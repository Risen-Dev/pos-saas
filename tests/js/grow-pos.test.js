import { test } from "node:test";
import assert from "node:assert/strict";
import growPos from "../../resources/js/grow-pos.js";

function terminal() {
    Object.defineProperty(globalThis, "navigator", {
        value: { onLine: true },
        configurable: true,
    });
    const pos = growPos();
    pos.config = { checkout: "/pos" };
    pos.persist = () => true;
    return pos;
}
test("network failure preserves the original payload and stops automatic sync", async () => {
    const pos = terminal();
    const payload = { checkout_key: "stable", payment_method: "cash" };
    pos.queue = [
        { payload },
        { payload: { checkout_key: "next", payment_method: "cash" } },
    ];
    let calls = 0;
    pos.request = async () => {
        calls++;
        throw new Error("Connection lost");
    };
    await pos.syncQueue();
    assert.equal(calls, 1);
    assert.equal(pos.queue.length, 2);
    assert.equal(pos.queue[0].payload, payload);
    assert.equal(pos.syncing, false);
    pos.request = async () => ({});
    await pos.syncQueue();
    assert.equal(pos.queue.length, 0);
});
test("stock conflict stays visible while other cash orders sync; QR is never charged in background", async () => {
    const pos = terminal();
    pos.queue = ["conflict", "good", "qr"].map((key) => ({
        payload: {
            checkout_key: key,
            payment_method: key === "qr" ? "midtrans" : "cash",
        },
    }));
    const sent = [];
    pos.request = async (url, method, payload) => {
        sent.push(payload.checkout_key);
        if (payload.checkout_key === "conflict")
            throw Object.assign(new Error("Stok kurang"), { status: 422 });
        return {};
    };
    await pos.syncQueue();
    assert.deepEqual(sent, ["conflict", "good"]);
    assert.deepEqual(
        pos.queue.map((e) => e.payload.checkout_key),
        ["conflict", "qr"],
    );
    assert.equal(pos.queue[0].error, "Stok kurang");
});
test("paid QR replay opens receipt, pending QR remains recoverable without duplicates", () => {
    const pos = terminal();
    const pending = { order_id: 3, qr_url: "/qr", payment_status: "unpaid" };
    pos.showResult(pending);
    pos.showResult(pending);
    assert.equal(pos.pendingPayments.length, 1);
    assert.equal(pos.modal, "qris");
    pos.showResult({ ...pending, payment_status: "paid" });
    assert.equal(pos.modal, "receipt");
});
test("payment confirmation removes recovered QR from waiting list", async () => {
    const pos = terminal();
    pos.payment = { order_id: 3 };
    pos.pendingPayments = [{ order_id: 3 }, { order_id: 4 }];
    pos.request = async () => ({ status: "paid" });
    await pos.checkPayment();
    assert.equal(pos.modal, "receipt");
    assert.deepEqual(pos.pendingPayments, [{ order_id: 4 }]);
});

test("loyalty redemption is unavailable while the POS is offline", () => {
    const pos = terminal();
    pos.config = {
        loyaltyProgramEnabled: true,
        pointRedemptionPoints: 100,
        pointRedemptionValue: 10000,
    };
    pos.customer = "1";
    pos.customerOptions = [{ id: 1, is_member: true, points: 100 }];
    pos.cart = [{ quantity: 1, price: 30000, discount: 0 }];
    pos.pointsToRedeem = 100;
    assert.equal(pos.canUsePoints, true);
    assert.equal(pos.amounts.total, 20000);
    pos.online = false;
    assert.equal(pos.canUsePoints, false);
    assert.equal(pos.amounts.total, 30000);
});

test("barcode scan selects an exact item and refuses ambiguous codes", () => {
    const pos = terminal();
    pos.products = [
        {
            id: 1,
            sku: "A",
            barcode: "899001",
            name: "Buku",
            type: "product",
            variants: [],
            addons: [],
        },
    ];
    let added = 0;
    pos.addItem = () => added++;
    pos.scan("899001");
    assert.equal(pos.selected.id, 1);
    assert.equal(added, 1);
    pos.products.push({ id: 2, sku: "899001", barcode: null });
    pos.scan("899001");
    assert.equal(added, 1);
    assert.match(pos.error, /beberapa item/);
});

test("cashier filters goods and services without changing the catalog", () => {
    const pos = terminal();
    pos.products = [
        { id: 1, name: "Buku", sku: "A", type: "product" },
        { id: 2, name: "Pasang", sku: "B", type: "service" },
    ];
    pos.itemType = "service";
    assert.deepEqual(
        pos.filtered.map((p) => p.id),
        [2],
    );
    pos.itemType = "all";
    assert.equal(pos.filtered.length, 2);
});

test("unconfirmed QR without an image never opens the paid receipt", () => {
    const pos = terminal();
    pos.showResult({
        order_id: 19,
        payment_method: "midtrans",
        payment_status: "unpaid",
        order_status: "pending",
        qr_url: null,
    });
    assert.equal(pos.modal, "qris");
    assert.equal(pos.pendingPayments.length, 1);
    assert.equal(pos.payment.order_id, 19);
});

test("wholesale savings compare the same addons on normal and bulk prices", () => {
    const pos = terminal();
    pos.selected = {
        price: 10000,
        discount: 0,
        variants: [],
        addons: [{ id: 1, price: 3000 }],
        price_tiers: [{ min_quantity: 10, price: 8000, discount: 0 }],
    };
    pos.addonIds = [1];
    pos.itemQuantity = 10;
    assert.equal(pos.wholesaleSaving, 20000);
    pos.addonIds = [];
    assert.equal(pos.wholesaleSaving, 20000);
    pos.itemQuantity = 1;
    assert.equal(pos.wholesaleSaving, 0);
});

test("credit sale records debt without collecting cash and retains its checkout reference", async () => {
    const pos = terminal();
    pos.config.canCredit = true;
    pos.shift = { id: 7 };
    pos.cart = [
        { id: 1, quantity: 1, price: 10000, discount: 0, addon_ids: [] },
    ];
    pos.customer = "9";
    pos.method = "credit";
    let payload;
    pos.request = async (url, method, body) => {
        payload = body;
        return {
            payment_method: "cash",
            payment_status: "unpaid",
            order_status: "pending",
            order_id: 1,
        };
    };
    await pos.submit();
    assert.equal(payload.payment_method, "cash");
    assert.equal(payload.payment_status, "unpaid");
    assert.equal(payload.paid_amount, 0);
    assert.equal(payload.customer_id, 9);
    assert.match(payload.checkout_key, /^[0-9a-f-]{36}$/);
    assert.equal(pos.queue.length, 0);
    assert.equal(pos.modal, "receipt");
});

test("credit sale requires a customer and CRM entitlement before any request", async () => {
    const pos = terminal();
    pos.shift = { id: 1 };
    pos.cart = [{ id: 1, quantity: 1, price: 10000, discount: 0 }];
    pos.method = "credit";
    pos.config.canCredit = true;
    pos.request = () =>
        assert.fail("A credit sale without a customer must not be sent");
    await pos.submit();
    assert.match(pos.error, /Pilih pelanggan/);
    pos.customer = "9";
    pos.config.canCredit = false;
    await pos.submit();
    assert.match(pos.error, /Pilih pelanggan/);
});

test("customer lookup ignores stale responses and keeps current selection in query", async () => {
    const pos = terminal();
    pos.config.customerLookup = "/pos/customers/lookup";
    pos.customer = "55";
    const pending = [];
    const urls = [];
    pos.request = (url) => {
        urls.push(url);
        return new Promise((resolve) => pending.push(resolve));
    };
    pos.customerSearch = "Old";
    const oldRequest = pos.lookupCustomers();
    pos.customerSearch = "New";
    const newRequest = pos.lookupCustomers();
    pending[1]({ customers: [{ id: 55, name: "New" }] });
    await newRequest;
    pending[0]({ customers: [{ id: 1, name: "Old" }] });
    await oldRequest;
    assert.equal(pos.customerOptions[0].name, "New");
    assert.ok(urls.every((url) => url.includes("selected=55")));
});

test("manual payment requires online confirmation and records the exact amount without cash", async () => {
    const pos = terminal();
    pos.method = "transfer";
    pos.shift = { id: 1 };
    pos.cart = [
        { id: 1, quantity: 1, price: 10000, discount: 0, addon_ids: [] },
    ];
    let payload;
    pos.request = async (url, method, data) => {
        payload = data;
        return {
            order_id: 1,
            payment_method: "transfer",
            payment_status: "paid",
        };
    };
    await pos.submit();
    assert.equal(payload, undefined);
    assert.match(pos.error, /Periksa dana/);
    pos.manualConfirmed = true;
    navigator.onLine = false;
    await pos.submit();
    assert.equal(payload, undefined);
    navigator.onLine = true;
    pos.paymentReference = "BANK-123";
    await pos.submit();
    assert.equal(payload.paid_amount, 10000);
    assert.equal(payload.manual_confirmed, true);
    assert.equal(payload.payment_reference, "BANK-123");
    assert.equal(pos.modal, "receipt");
    assert.equal(pos.manualConfirmed, false);
    assert.equal(pos.queue.length, 0);
});

test("manual payment network failure keeps verified payload for explicit retry only", async () => {
    const pos = terminal();
    pos.method = "qris_manual";
    pos.manualConfirmed = true;
    pos.shift = { id: 1 };
    pos.cart = [
        { id: 1, quantity: 1, price: 10000, discount: 0, addon_ids: [] },
    ];
    let attempts = 0;
    pos.request = async () => {
        attempts++;
        throw new Error("Network failed");
    };
    await pos.submit();
    assert.equal(pos.queue.length, 1);
    const payload = pos.queue[0].payload;
    assert.equal(payload.manual_confirmed, true);
    await pos.syncQueue();
    assert.equal(attempts, 1);
    assert.equal(pos.queue[0].payload, payload);
    pos.request = async (url, method, data) => {
        assert.equal(data, payload);
        return {
            order_id: 2,
            payment_method: "qris_manual",
            payment_status: "paid",
        };
    };
    await pos.retry(pos.queue[0]);
    assert.equal(pos.queue.length, 0);
    assert.equal(pos.modal, "receipt");
});
