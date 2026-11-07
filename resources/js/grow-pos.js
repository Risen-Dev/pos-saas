import { money, unitFor, totals, readState, queueOnce } from "./pos-state.js";
import { rupiahDigits, rupiahInput } from "./currency-input.js";

export default () => ({
    pendingPayments: [],
    config: null,
    products: [],
    visibleLimit: 60,
    cart: [],
    favorites: [],
    drafts: [],
    queue: [],
    search: "",
    category: "all",
    itemType: "all",
    orderType: "takeaway",
    table: "",
    customer: "",
    customerOptions: [],
    customerSearch: "",
    customerSearchVersion: 0,
    customerSearchError: "",
    newCustomerName: "",
    newCustomerPhone: "",
    newCustomerMember: false,
    customerCreateError: "",
    pointsToRedeem: 0,
    useStampReward: false,
    orderNote: "",
    modal: "",
    selected: null,
    variant: "",
    saleUnit: "",
    itemQuantity: 1,
    addonIds: [],
    itemNote: "",
    cash: "",
    method: "cash",
    manualConfirmed: false,
    paymentReference: "",
    busy: false,
    syncing: false,
    online: navigator.onLine,
    notice: "",
    error: "",
    receipt: null,
    payment: null,
    shift: null,
    shiftCash: "",
    shiftSummary: null,
    key: "",
    storageReady: true,
    cartTier(line) {
        if (line.unit_id || line.variant_id) {
            return null;
        }

        const product = this.products.find((p) => p.id === line.id);

        if (!product) {
            return null;
        }

        return (
            [...(product.price_tiers || [])]
                .filter(
                    (tier) =>
                        Number(tier.min_quantity) <= Number(line.quantity),
                )
                .sort(
                    (a, b) => Number(b.min_quantity) - Number(a.min_quantity),
                )[0] || null
        );
    },
    currencyInput(value) {
        return rupiahInput(value);
    },
    setCurrencyField(field, value) {
        this[field] = rupiahDigits(value);
    },
    init() {
        this.config = JSON.parse(
            document.getElementById("grow-pos-data").textContent,
        );
        this.products = this.config.products;
        this.customerOptions = this.config.customers || [];
        this.pendingPayments = this.config.pendingPayments || [];
        this.shift = this.config.shift;
        this.key = `growpos:${this.config.tenant}:${this.config.user}:v1`;
        try {
            const state = readState(localStorage, this.key, {});
            this.cart = state.cart || [];
            this.favorites = state.favorites || [];
            this.drafts = state.drafts || [];
            this.queue = state.queue || [];
            this.orderType = state.orderType || "takeaway";
            this.table = state.table || "";
            this.customer = state.customer || "";
            this.orderNote = state.orderNote || "";
            this.persist();
        } catch {
            this.storageReady = false;
            this.error =
                "Penyimpanan perangkat tidak tersedia. Aktifkan penyimpanan browser sebelum menerima transaksi.";
        }
        if (this.customer && this.config.canCredit) this.lookupCustomers();
        this.$watch("customer", (value) => {
            this.pointsToRedeem = 0;
            this.useStampReward = false;
            if (
                value &&
                !this.customerOptions.some(
                    (person) => Number(person.id) === Number(value),
                )
            )
                this.lookupCustomers();
        });
        this.$watch("method", (value) => {
            if (value === "credit") this.useStampReward = false;
        });
        this.$watch("cart", () => this.persist());
        for (const field of ["search", "category", "itemType"]) {
            this.$watch(field, () => {
                this.visibleLimit = 60;
            });
        }
        let modalTrigger = null;
        this.$watch("modal", (value, previous) => {
            if (value && !previous) modalTrigger = document.activeElement;
            if (!value && modalTrigger?.isConnected)
                this.$nextTick(() => modalTrigger.focus());
            document.body.classList.toggle("overflow-hidden", Boolean(value));
            if (value)
                this.$nextTick(() => {
                    const panel = document.querySelector("[data-dialog-panel]");
                    const focusable = [
                        ...panel.querySelectorAll(
                            "input,select,button,a[href]",
                        ),
                    ].filter((el) => el.offsetParent !== null && !el.disabled);
                    focusable[0]?.focus();
                });
        });
        ["orderType", "table", "customer", "orderNote"].forEach((field) =>
            this.$watch(field, () => this.persist()),
        );
        window.addEventListener("online", () => {
            this.online = true;
            this.syncQueue();
        });
        window.addEventListener("offline", () => {
            this.online = false;
            this.pointsToRedeem = 0;
            this.useStampReward = false;
        });
        this.syncQueue();
    },
    money,
    formatQuantity(value) {
        return new Intl.NumberFormat("id-ID", {
            minimumFractionDigits: 0,
            maximumFractionDigits: 3,
        }).format(Number(value) || 0);
    },

    get activeTier() {
        if (!this.selected || this.saleUnit || this.variant) {
            return null;
        }

        const qty = Number(this.itemQuantity) || 0;

        return (
            [...(this.selected.price_tiers || [])]
                .filter(
                    (tier) =>
                        Number(tier.min_quantity) > 0 &&
                        Number(tier.min_quantity) <= qty,
                )
                .sort(
                    (a, b) => Number(b.min_quantity) - Number(a.min_quantity),
                )[0] || null
        );
    },

    get nextTier() {
        if (!this.selected || this.saleUnit || this.variant) {
            return null;
        }

        const qty = Number(this.itemQuantity) || 0;

        return (
            [...(this.selected.price_tiers || [])]
                .filter((tier) => Number(tier.min_quantity) > qty)
                .sort(
                    (a, b) => Number(a.min_quantity) - Number(b.min_quantity),
                )[0] || null
        );
    },

    get wholesaleSaving() {
        if (!this.selected || !this.activeTier) {
            return 0;
        }

        const extras = this.selected.addons
            .filter((addon) => this.addonIds.includes(addon.id))
            .reduce((sum, addon) => sum + Number(addon.price), 0);
        const normalPrice =
            Number(this.selected.price || 0) -
            Number(this.selected.discount || 0) +
            extras;

        const tierPrice =
            Number(this.selectedUnit.price || 0) -
            Number(this.selectedUnit.discount || 0);

        return Math.max(
            0,
            Math.round(normalPrice * Number(this.itemQuantity || 0)) -
                Math.round(tierPrice * Number(this.itemQuantity || 0)),
        );
    },
    get visibleProducts() {
        return this.filtered.slice(0, this.visibleLimit);
    },
    get filtered() {
        const q = this.search.toLowerCase().trim();
        return this.products.filter(
            (p) =>
                (this.itemType === "all" || p.type === this.itemType) &&
                (this.category === "all" ||
                    (this.category === "favorites" &&
                        this.favorites.includes(p.id)) ||
                    Number(this.category) === p.category_id) &&
                (!q ||
                    `${p.name} ${p.sku} ${p.barcode || ""}`
                        .toLowerCase()
                        .includes(q)),
        );
    },
    scan(code) {
        const matches = this.products.filter(
            (p) => p.sku === code.trim() || p.barcode === code.trim(),
        );
        if (matches.length !== 1) {
            this.error = matches.length
                ? "Kode cocok dengan beberapa item. Pilih item dari katalog."
                : "Kode tidak ditemukan di katalog aktif.";
            return;
        }
        this.choose(matches[0]);
        if (!matches[0].variants.length && !matches[0].addons.length)
            this.addItem();
        this.search = "";
    },
    get amounts() {
        const amounts = totals(this.cart, this.config?.taxRate || 0);
        const points = Number(this.pointsToRedeem) || 0;
        const step = Number(this.config?.pointRedemptionPoints) || 1;
        const value = Number(this.config?.pointRedemptionValue) || 0;
        const pointsDiscount =
            this.canUsePoints &&
            points > 0 &&
            points % step === 0 &&
            points <= this.maxRedeemablePoints
                ? Math.floor(points / step) * value
                : 0;
        const stampDiscount =
            this.canUseStampReward && this.useStampReward
                ? Number(this.config?.stampRewardValue) || 0
                : 0;
        const discount = pointsDiscount || stampDiscount;

        return {
            ...amounts,
            loyaltyDiscount: discount,
            redeemStampReward: stampDiscount > 0,
            total: amounts.total - discount,
        };
    },
    get selectedCustomer() {
        return (
            this.customerOptions.find(
                (person) => Number(person.id) === Number(this.customer),
            ) || null
        );
    },
    get canUsePoints() {
        return Boolean(
            this.online &&
                this.config?.loyaltyProgramEnabled &&
                this.config?.loyaltyProgramType === "points" &&
                this.selectedCustomer?.is_member &&
                this.method !== "credit",
        );
    },
    get canUseStampReward() {
        const goal = Number(this.config?.stampRewardGoal) || 0;
        const reward = Number(this.config?.stampRewardValue) || 0;
        const payable = totals(this.cart, this.config?.taxRate || 0).total;

        return Boolean(
            this.online &&
                this.config?.loyaltyProgramEnabled &&
                this.config?.loyaltyProgramType === "stamps" &&
                this.selectedCustomer?.is_member &&
                this.method !== "credit" &&
                Number(this.selectedCustomer?.stamp_balance || 0) >= goal &&
                payable > reward,
        );
    },
    get maxRedeemablePoints() {
        if (!this.canUsePoints) return 0;
        const step = Number(this.config?.pointRedemptionPoints) || 1;
        const value = Number(this.config?.pointRedemptionValue) || 0;
        const available =
            Math.floor(Number(this.selectedCustomer.points || 0) / step) * step;
        const payable = totals(this.cart, this.config?.taxRate || 0).total;
        const affordable =
            value > 0 ? Math.floor(Math.max(0, payable - 1) / value) * step : 0;

        return Math.min(available, affordable);
    },
    get itemCount() {
        return this.cart.reduce((n, i) => n + i.quantity, 0);
    },
    get selectedUnit() {
        return this.selected
            ? unitFor(
                  this.selected,
                  this.variant,
                  this.addonIds,
                  this.saleUnit,
                  Number(this.itemQuantity),
              )
            : { price: 0, discount: 0 };
    },
    persist() {
        if (!this.key || !this.storageReady) return false;
        try {
            localStorage.setItem(
                this.key,
                JSON.stringify({
                    cart: this.cart,
                    favorites: this.favorites,
                    drafts: this.drafts,
                    queue: this.queue,
                    orderType: this.orderType,
                    table: this.table,
                    customer: this.customer,
                    orderNote: this.orderNote,
                }),
            );
            return true;
        } catch {
            this.storageReady = false;
            this.error =
                "Penyimpanan perangkat penuh. Antrean belum tersimpan; jangan tutup halaman.";
            return false;
        }
    },
    favorite(id) {
        this.favorites = this.favorites.includes(id)
            ? this.favorites.filter((v) => v !== id)
            : [...this.favorites, id];
        this.persist();
    },
    pick(product) {
        this.choose(product);
        if (
            !product.allow_fraction &&
            !product.variants.length &&
            !product.addons.length &&
            !(product.units || []).length
        ) {
            this.addItem();
        }
    },
    choose(product) {
        this.selected = product;
        this.variant = "";
        this.saleUnit = "";
        this.itemQuantity = 1;
        this.addonIds = [];
        this.itemNote = "";
        this.modal = "item";
        this.error = "";
    },
    addItem() {
        const p = this.selected,
            unit = this.selectedUnit;
        const variantId = this.variant ? Number(this.variant) : null;
        const quantity = Number(this.itemQuantity);
        if (
            !Number.isFinite(quantity) ||
            quantity <= 0 ||
            quantity > 100000 ||
            Math.abs(quantity * 1000 - Math.round(quantity * 1000)) > 0.00001 ||
            (!p.allow_fraction && !Number.isInteger(quantity))
        ) {
            this.error = "Jumlah tidak valid untuk satuan produk ini.";
            return;
        }
        const existingQty = this.cart
            .filter((i) => i.id === p.id && i.variant_id === variantId)
            .reduce(
                (n, i) => n + Math.round(i.quantity * (i.factor || 1) * 1000),
                0,
            );
        if (
            p.tracked &&
            existingQty + Math.round(quantity * unit.factor * 1000) >
                Math.round(unit.stock * 1000)
        ) {
            this.error = "Stok produk tidak mencukupi.";
            return;
        }
        const signature = JSON.stringify([
            p.id,
            variantId,
            this.saleUnit || null,
            [...this.addonIds].sort(),
            this.itemNote.trim(),
        ]);
        const existing = this.cart.find((i) => i.key === signature);
        if (existing) {
            existing.quantity =
                Math.round((existing.quantity + quantity) * 1000) / 1000;
            this.reprice(existing);
        } else
            this.cart.push({
                key: signature,
                id: p.id,
                variant_id: variantId,
                unit_id: this.saleUnit ? Number(this.saleUnit) : null,
                factor: unit.factor,
                unit_name: unit.unit_name,
                allow_fraction: p.allow_fraction || false,
                addon_ids: [...this.addonIds],
                note: this.itemNote.trim(),
                name: unit.name,
                price: unit.price,
                discount: unit.discount,
                stock: unit.stock,
                tracked: p.tracked,
                addons: p.addons
                    .filter((a) => this.addonIds.includes(a.id))
                    .map((a) => a.name),
                quantity,
            });
        this.modal = "";
        this.error = "";
        this.persist();
    },
    reprice(item) {
        const product = this.products.find((p) => p.id === item.id);
        if (!product) return;
        Object.assign(
            item,
            unitFor(
                product,
                item.variant_id,
                item.addon_ids || [],
                item.unit_id,
                item.quantity,
            ),
        );
    },
    quantity(key, delta) {
        const item = this.cart.find((i) => i.key === key);
        if (item)
            this.setQuantity(
                key,
                Math.max(0, Math.round((item.quantity + delta) * 1000) / 1000),
            );
    },
    setQuantity(key, value) {
        const item = this.cart.find((i) => i.key === key);
        if (!item) return;
        const next = Number(value);
        if (
            !Number.isFinite(next) ||
            next < 0 ||
            next > 100000 ||
            Math.abs(next * 1000 - Math.round(next * 1000)) > 0.00001 ||
            (!item.allow_fraction && !Number.isInteger(next))
        ) {
            this.error = "Jumlah tidak valid untuk satuan produk ini.";
            return;
        }
        const used = this.cart
            .filter(
                (i) =>
                    i.id === item.id &&
                    i.variant_id === item.variant_id &&
                    i.key !== key,
            )
            .reduce(
                (n, i) => n + Math.round(i.quantity * (i.factor || 1) * 1000),
                0,
            );
        if (
            item.tracked &&
            used + Math.round(next * (item.factor || 1) * 1000) >
                Math.round(item.stock * 1000)
        ) {
            this.error = "Stok produk tidak mencukupi.";
            return;
        }
        item.quantity = next;
        this.reprice(item);
        this.cart = this.cart.filter((i) => i.quantity > 0);
        this.error = "";
        this.persist();
    },
    hold() {
        if (!this.cart.length) return;
        const manual = ["transfer", "qris_manual"].includes(this.method);
        if (manual && (!navigator.onLine || !this.manualConfirmed)) {
            this.error =
                "Periksa dana masuk pada rekening/aplikasi toko dan centang konfirmasi. Pembayaran manual memerlukan koneksi.";
            return;
        }
        if (!this.storageReady) {
            this.error = "Pesanan belum bisa disimpan di perangkat.";
            return;
        }
        this.drafts.push({
            id: crypto.randomUUID(),
            name: this.table
                ? "Meja " + this.table
                : "Pesanan " +
                  new Date().toLocaleTimeString("id-ID", {
                      hour: "2-digit",
                      minute: "2-digit",
                  }),
            cart: JSON.parse(JSON.stringify(this.cart)),
            orderType: this.orderType,
            table: this.table,
            customer: this.customer,
            note: this.orderNote,
        });
        if (this.persist()) {
            this.clearCart();
            this.notice =
                "Pesanan tersimpan di perangkat. Stok belum dipotong.";
        }
    },
    resume(id) {
        if (this.cart.length) {
            this.error = "Simpan pesanan saat ini sebelum membuka draft lain.";
            return;
        }
        const d = this.drafts.find((v) => v.id === id);
        if (!d) return;
        this.cart = d.cart;
        this.orderType = d.orderType;
        this.table = d.table;
        this.customer = d.customer;
        this.orderNote = d.note;
        this.drafts = this.drafts.filter((v) => v.id !== id);
        this.modal = "";
        this.persist();
    },
    clearCart() {
        this.cart = [];
        this.table = "";
        this.customer = "";
        this.orderNote = "";
        this.cash = "";
        this.persist();
    },
    pay() {
        this.error = "";
        if (!this.shift) {
            this.modal = "openShift";
            return;
        }
        if (!this.cart.length) {
            this.error = "Pilih produk atau layanan terlebih dahulu.";
            return;
        }
        this.cash = "";
        this.method = "cash";
        this.modal = "pay";
    },
    async lookupCustomers() {
        const version = ++this.customerSearchVersion;
        this.customerSearchError = "";
        try {
            const params = new URLSearchParams({ q: this.customerSearch });
            if (this.customer) params.set("selected", this.customer);
            const result = await this.request(
                `${this.config.customerLookup}?${params}`,
            );
            if (version === this.customerSearchVersion)
                this.customerOptions = result.customers;
        } catch (error) {
            if (version === this.customerSearchVersion)
                this.customerSearchError = error.message;
        }
    },
    openCustomerForm() {
        this.newCustomerName = "";
        this.newCustomerPhone = "";
        this.newCustomerMember = false;
        this.customerCreateError = "";
        this.modal = "customer";
    },
    async saveCustomer() {
        if (this.busy) return;
        this.customerCreateError = "";
        this.busy = true;
        try {
            const response = await this.request(
                this.config.customerStore,
                "POST",
                {
                    name: this.newCustomerName,
                    phone: this.newCustomerPhone,
                    is_member: this.newCustomerMember,
                },
            );
            const person = response.data;
            this.customerOptions = [
                person,
                ...this.customerOptions.filter(
                    (existing) => Number(existing.id) !== Number(person.id),
                ),
            ];
            this.customer = String(person.id);
            this.customerSearch = person.name;
            this.modal = "";
            this.notice = person.is_member
                ? "Member disimpan. Tautan kartu akan tersedia setelah transaksi berhasil."
                : "Pelanggan disimpan dan dipilih untuk transaksi.";
        } catch (error) {
            this.customerCreateError = error.message;
        } finally {
            this.busy = false;
        }
    },
    async request(url, method = "GET", body = null) {
        const response = await fetch(url, {
            method,
            credentials: "same-origin",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": this.config.csrf,
            },
            body: body ? JSON.stringify(body) : undefined,
        });
        // Session/CSRF expiry must never leave a cashier on a stale privileged screen.
        if (response.status === 401 || response.status === 419) {
            window.location.assign(
                `/login?expired=1&next=${encodeURIComponent(window.location.pathname)}`,
            );
            throw new Error("Sesi berakhir. Silakan masuk kembali.");
        }

        let data;
        try {
            data = await response.json();
        } catch {
            throw new Error(
                "Sesi atau koneksi terputus. Muat ulang setelah menyimpan antrean.",
            );
        }
        if (!response.ok) {
            const message = data.errors
                ? Object.values(data.errors).flat().join(" ")
                : data.message || "Permintaan gagal.";
            const e = new Error(message);
            e.status = response.status;
            throw e;
        }
        return data;
    },
    async submit() {
        if (this.busy) return;
        this.error = "";
        const total = this.amounts.total;
        if (!this.shift || !this.cart.length) {
            this.error =
                "Mulai jualan dan pilih item sebelum mencatat transaksi.";
            return;
        }
        if (
            this.method === "credit" &&
            (!this.config.canCredit || !this.customer)
        ) {
            this.error = "Pilih pelanggan untuk mencatat bon.";
            return;
        }
        if (this.method === "cash" && Number(this.cash) < total) {
            this.error = "Uang tunai kurang.";
            return;
        }
        if (this.method === "midtrans" && !navigator.onLine) {
            this.error = "QRIS membutuhkan koneksi internet.";
            return;
        }
        const manual = ["transfer", "qris_manual"].includes(this.method);
        if (manual && (!navigator.onLine || !this.manualConfirmed)) {
            this.error =
                "Periksa dana masuk pada rekening/aplikasi toko dan centang konfirmasi. Pembayaran manual memerlukan koneksi.";
            return;
        }
        if (!this.storageReady) {
            this.error =
                "Aktifkan penyimpanan browser agar transaksi dapat disimpan sebelum dikirim.";
            return;
        }
        const payload = {
            checkout_key: crypto.randomUUID(),
            shift_id: this.shift.id,
            sold_at: new Date().toISOString(),
            payment_method: this.method === "credit" ? "cash" : this.method,
            payment_status: this.method === "credit" ? "unpaid" : "paid",
            points_to_redeem:
                this.method === "credit" ? 0 : Number(this.pointsToRedeem || 0),
            redeem_stamp_reward:
                this.method !== "credit" &&
                this.canUseStampReward &&
                this.useStampReward,
            paid_amount: manual
                ? total
                : this.method === "cash"
                  ? Number(this.cash)
                  : 0,
            ...(manual
                ? {
                      manual_confirmed: true,
                      payment_reference: this.paymentReference || null,
                  }
                : {}),
            grand_total: total,
            order_type: this.orderType,
            table_number: this.table || null,
            customer_id: this.customer ? Number(this.customer) : null,
            note: this.orderNote || null,
            items: this.cart.map((i) => ({
                id: i.id,
                variant_id: i.variant_id,
                unit_id: i.unit_id || null,
                addon_ids: i.addon_ids,
                note: i.note || null,
                quantity: i.quantity,
            })),
        };
        this.queue = queueOnce(this.queue, payload);
        if (!this.persist()) return;
        this.busy = true;
        if (!navigator.onLine) {
            this.clearCart();
            this.modal = "queue";
            this.notice =
                "Transaksi tunai tersimpan di perangkat, belum masuk laporan server.";
            this.busy = false;
            return;
        }
        try {
            const result = await this.request(
                this.config.checkout,
                "POST",
                payload,
            );
            this.queue = this.queue.filter(
                (i) => i.payload.checkout_key !== payload.checkout_key,
            );
            this.clearCart();
            this.persist();
            this.showResult(result);
        } catch (e) {
            const pending = this.queue.find(
                (i) => i.payload.checkout_key === payload.checkout_key,
            );
            if (pending) pending.error = e.message;
            this.clearCart();
            this.persist();
            this.modal = "queue";
            this.error =
                e.message +
                " Transaksi tetap disimpan dalam antrean. Jangan menerima pembayaran ulang.";
        } finally {
            this.busy = false;
        }
    },
    async syncQueue() {
        if (
            this.syncing ||
            this.busy ||
            !navigator.onLine ||
            !this.queue.length
        )
            return;
        this.syncing = true;
        try {
            for (const entry of [...this.queue]) {
                // Digital retries need explicit attention; do not initiate QR charges in the background.
                if (entry.payload.payment_method !== "cash") continue;
                try {
                    await this.request(
                        this.config.checkout,
                        "POST",
                        entry.payload,
                    );
                    this.queue = this.queue.filter(
                        (i) =>
                            i.payload.checkout_key !==
                            entry.payload.checkout_key,
                    );
                    this.persist();
                } catch (e) {
                    entry.error = e.message;
                    this.persist();
                    if (!e.status || e.status >= 500) break;
                }
            }
            if (!this.queue.length)
                this.notice = "Semua transaksi sudah tersinkron ke server.";
        } finally {
            this.syncing = false;
        }
    },
    async retry(entry) {
        if (this.busy || this.syncing) return;
        this.busy = true;
        this.error = "";
        try {
            const result = await this.request(
                this.config.checkout,
                "POST",
                entry.payload,
            );
            this.queue = this.queue.filter(
                (i) => i.payload.checkout_key !== entry.payload.checkout_key,
            );
            this.persist();
            this.showResult(result);
        } catch (e) {
            entry.error = e.message;
            this.persist();
        } finally {
            this.busy = false;
        }
    },
    showResult(result) {
        this.manualConfirmed = false;
        this.paymentReference = "";
        this.pointsToRedeem = 0;
        this.useStampReward = false;
        if (result.order_status === "cancelled") {
            this.modal = "";
            this.notice = "Pembayaran sudah dibatalkan.";
            return;
        }
        if (
            (result.qr_url || result.payment_method === "midtrans") &&
            result.payment_status !== "paid"
        ) {
            this.payment = result;
            if (
                !this.pendingPayments.some(
                    (p) => p.order_id === result.order_id,
                )
            )
                this.pendingPayments.push(result);
            this.modal = "qris";
        } else {
            this.receipt = result;
            this.modal = "receipt";
        }
    },
    async copyCardLink() {
        if (!this.receipt?.loyalty_card_url) return;
        try {
            await navigator.clipboard.writeText(this.receipt.loyalty_card_url);
            this.notice = "Tautan kartu member tersalin.";
        } catch {
            window.prompt(
                "Salin tautan kartu member",
                this.receipt.loyalty_card_url,
            );
        }
    },
    trapFocus(event) {
        const nodes = [
            ...document.querySelectorAll(
                "[data-dialog-panel] button,[data-dialog-panel] a[href],[data-dialog-panel] input,[data-dialog-panel] select",
            ),
        ].filter((el) => el.offsetParent !== null && !el.disabled);
        if (!nodes.length) return;
        const index = nodes.indexOf(document.activeElement);
        const next = event.shiftKey
            ? index <= 0
                ? nodes.length - 1
                : index - 1
            : (index + 1) % nodes.length;
        event.preventDefault();
        nodes[next].focus();
    },
    backupQueue() {
        const blob = new Blob([JSON.stringify(this.queue, null, 2)], {
            type: "application/json",
        });
        const a = document.createElement("a");
        a.href = URL.createObjectURL(blob);
        a.download = "growpos-antrean-" + this.config.tenant + ".json";
        a.click();
        URL.revokeObjectURL(a.href);
    },
    async checkPayment() {
        if (this.busy || !this.payment) return;
        this.error = "";
        this.busy = true;
        try {
            const result = await this.request(
                "/orders/" + this.payment.order_id + "/check-status",
            );
            const paid =
                result.status === "paid" || result.payment_status === "paid";
            if (paid || result.status === "cancelled")
                this.pendingPayments = this.pendingPayments.filter(
                    (p) => p.order_id !== this.payment.order_id,
                );
            if (paid) {
                this.receipt =
                    result.payment_status === "paid" ? result : this.payment;
                this.modal = "receipt";
                this.notice = "Pembayaran QRIS terkonfirmasi.";
            } else if (result.status === "cancelled") {
                this.modal = "";
                this.notice = "Pembayaran batal. Stok telah dipulihkan.";
            } else this.notice = "Pembayaran masih menunggu konfirmasi.";
        } catch (e) {
            this.error = e.message;
        } finally {
            this.busy = false;
        }
    },
    async openShift() {
        this.error = "";
        this.busy = true;
        try {
            await this.request("/shifts/open", "POST", {
                cash_start: Number(this.shiftCash),
            });
            this.shift = (await this.request("/shifts/current")).shift;
            this.modal = "";
            this.notice = "Shift dibuka. Selamat berjualan!";
        } catch (e) {
            this.error = e.message;
        } finally {
            this.busy = false;
        }
    },
    async prepareClose() {
        if (this.queue.length) {
            this.modal = "queue";
            this.error =
                "Sinkronkan antrean perangkat ini sebelum menutup penjualan.";
            return;
        }
        try {
            this.shiftSummary = await this.request("/shifts/summary");
            this.shiftCash = "";
            this.modal = "closeShift";
        } catch (e) {
            this.error = e.message;
        }
    },
    async closeShift() {
        if (this.queue.length) return;
        this.error = "";
        this.busy = true;
        try {
            await this.request("/shifts/close", "POST", {
                cash_actual: Number(this.shiftCash),
            });
            this.shift = null;
            this.modal = "";
            this.notice = "Shift ditutup. Terima kasih untuk hari ini.";
        } catch (e) {
            this.error = e.message;
        } finally {
            this.busy = false;
        }
    },
});
