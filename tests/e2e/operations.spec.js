import { test, expect } from "@playwright/test";

async function signIn(page, email = "qa.owner@growpos.test") {
    if (!process.env.GROWPOS_QA_PASSWORD)
        throw new Error("Fixture password required");
    await page.goto("/login");
    await page
        .getByRole("textbox", { name: "Alamat email", exact: true })
        .fill(email);
    await page
        .getByLabel("Kata sandi", { exact: true })
        .fill(process.env.GROWPOS_QA_PASSWORD);
    await page
        .getByRole("button", { name: "Masuk ke GrowPOS", exact: true })
        .click();
}

test("catalog search, pagination and customer detail stay usable", async ({
    page,
}) => {
    await signIn(page);
    await page.goto("/products");
    await expect(
        page.getByRole("navigation", { name: /Pagination/ }),
    ).toBeVisible();
    await page
        .getByRole("searchbox", { name: "Cari produk", exact: true })
        .fill("Beras Premium QA");
    await page.getByRole("button", { name: "Cari", exact: true }).click();
    await expect(
        page.getByText("Beras Premium QA", { exact: true }),
    ).toBeVisible();
    await expect(
        page.getByText("Air Mineral QA", { exact: true }),
    ).not.toBeVisible();
    await page.goto("/customers");
    await page
        .getByRole("link", { name: "Customer Testing QA", exact: true })
        .click();
    await expect(
        page.getByRole("heading", { name: "Customer Testing QA", exact: true }),
    ).toBeVisible();
});

test("cash checkout records fractional quantity and a receipt", async ({
    page,
}) => {
    await signIn(page, "qa.cashier@growpos.test");
    await expect(
        page.getByRole("heading", { name: "Ada pesanan apa?", exact: true }),
    ).toBeVisible();
    await page
        .getByRole("button", { name: "Mulai jualan", exact: true })
        .click();
    let dialog = page.getByRole("dialog", { name: "Detail kasir" });
    await dialog
        .getByRole("textbox", { name: "Modal awal (Rp)", exact: true })
        .fill("100000");
    await dialog
        .getByRole("button", { name: "Mulai jualan", exact: true })
        .click();
    await expect(dialog).not.toBeVisible();
    await page.getByRole("button", { name: /BE Beras Premium QA/ }).click();
    await dialog
        .getByRole("spinbutton", { name: "Jumlah", exact: true })
        .fill("1.5");
    await dialog.getByRole("button", { name: /^Tambahkan/ }).click();
    await page
        .getByRole("button", { name: "Lanjut pembayaran →", exact: true })
        .click();
    await dialog.getByRole("button", { name: "Uang pas", exact: true }).click();
    await dialog
        .getByRole("button", { name: "Konfirmasi pembayaran", exact: true })
        .click();
    await expect(
        dialog.getByRole("heading", {
            name: "Transaksi berhasil.",
            exact: true,
        }),
    ).toBeVisible();
    await expect(
        dialog.getByRole("link", { name: "Cetak struk ↗", exact: true }),
    ).toBeVisible();
});

test("member checkout redeems points and exposes the updated loyalty card", async ({
    page,
}) => {
    await signIn(page);
    await page.goto("/pos");
    const startShift = page.getByRole("button", { name: "Mulai jualan", exact: true });
    if (await startShift.count()) {
        await startShift.click();
        const shiftDialog = page.getByRole("dialog", { name: "Detail kasir" });
        await shiftDialog
            .getByRole("textbox", { name: "Modal awal (Rp)", exact: true })
            .fill("0");
        await shiftDialog
            .getByRole("button", { name: "Mulai jualan", exact: true })
            .click();
        await expect(shiftDialog).not.toBeVisible();
    }

    await page.getByRole("button", { name: /BE Beras Premium QA/ }).click();
    const productDialog = page.getByRole("dialog", { name: "Detail kasir" });
    await productDialog.getByRole("spinbutton", { name: "Jumlah", exact: true }).fill("2");
    await productDialog.getByRole("button", { name: /^Tambahkan/ }).click();
    await page
        .getByLabel("Pelanggan / member")
        .selectOption({ label: "Customer Testing QA · Member · 100 poin" });
    await page.getByRole("button", { name: "Lanjut pembayaran →", exact: true }).click();
    const paymentDialog = page.getByRole("dialog", { name: "Detail kasir" });
    await expect(paymentDialog.getByText("Poin member:")).toBeVisible();
    await paymentDialog.getByLabel("Gunakan poin").fill("100");
    await expect(paymentDialog.getByText("Rp 20.000").first()).toBeVisible();
    await paymentDialog.getByRole("button", { name: "Uang pas", exact: true }).click();
    await paymentDialog
        .getByRole("button", { name: "Konfirmasi pembayaran", exact: true })
        .click();

    await expect(
        paymentDialog.getByRole("heading", { name: "Transaksi berhasil.", exact: true }),
    ).toBeVisible();
    await expect(paymentDialog.getByText(/poin digunakan/)).toBeVisible();
    await expect(paymentDialog.getByText(/Saldo/)).toContainText("2");
    const cardLink = paymentDialog.getByRole("link", { name: "Lihat kartu", exact: true });
    const href = await cardLink.getAttribute("href");
    expect(href).toMatch(/\/loyalty\/card\/[A-Za-z0-9]{64}$/);
    await page.goto(href);
    await expect(page.getByRole("heading", { name: "Toko Maju Jaya QA" })).toBeVisible();
    await expect(page.getByText("Customer Testing QA", { exact: false })).toBeVisible();
    await expect(page.getByText("2", { exact: true })).toBeVisible();
});

test("first business setup stays separate from adding another business", async ({
    page,
}, testInfo) => {
    const errors = [];
    page.on("pageerror", (error) => errors.push(error.message));
    await signIn(page, "qa.new@growpos.test");
    await expect(
        page.getByRole("heading", { name: "Siapkan bisnis pertama Anda" }),
    ).toBeVisible();
    await expect(
        page.getByText("LANGKAH 2 DARI 2 · BISNIS PERTAMA", { exact: true }),
    ).toBeVisible();
    await page.screenshot({
        path: testInfo.outputPath("setup-first-390.png"),
        fullPage: true,
        animations: "disabled",
    });
    await page.getByRole("button", { name: "Keluar", exact: true }).click();
    await signIn(page);
    await page.goto("/setup-business");
    await expect(page).toHaveURL(/\/tenants\/create$/);
    await expect(
        page.getByRole("heading", { name: "Tambah bisnis", exact: true }),
    ).toBeVisible();
    await expect(page.getByText(/LANGKAH 2/)).toHaveCount(0);
    await expect(page.getByText("Sudah punya akun")).toHaveCount(0);
    await page.screenshot({
        path: testInfo.outputPath("add-business-390.png"),
        fullPage: true,
        animations: "disabled",
    });
    await page.getByRole("link", { name: "Batal", exact: true }).click();
    await expect(page).toHaveURL(/\/tenants$/);
    expect(errors).toEqual([]);
});

test("minimal catalog and verified manual payment work in the browser", async ({
    page,
}, testInfo) => {
    const errors = [];
    page.on("pageerror", (error) => errors.push(error.message));
    await signIn(page);
    await page.goto("/products/create");
    await page.locator("#product-name").fill("Teh sederhana QA");
    await page.getByLabel("Harga jual (Rp)", { exact: true }).fill("5000");
    await page.getByLabel("Gunakan stok", { exact: true }).uncheck();
    await page.screenshot({
        path: testInfo.outputPath("catalog-create-390.png"),
        fullPage: true,
        animations: "disabled",
    });
    await page
        .getByRole("button", { name: "Simpan katalog", exact: true })
        .click();
    await expect(page).toHaveURL(/\/products$/);
    await page.goto("/pos");
    const dialog = page.getByRole("dialog", { name: "Detail kasir" });
    const startShift = page.getByRole("button", { name: "Mulai jualan", exact: true });
    if (await startShift.count()) {
        await startShift.click();
        await dialog
            .getByRole("textbox", { name: "Modal awal (Rp)", exact: true })
            .fill("0");
        await dialog
            .getByRole("button", { name: "Mulai jualan", exact: true })
            .click();
        await expect(dialog).not.toBeVisible();
    }
    await page
        .getByRole("searchbox", {
            name: "Cari produk, layanan, atau barcode",
            exact: true,
        })
        .fill("Teh sederhana QA");
    await page.getByRole("button", { name: /^TE Teh sederhana QA/ }).click();
    await page
        .getByRole("button", { name: "Lanjut pembayaran →", exact: true })
        .click();
    await dialog
        .getByRole("button", { name: "Transfer bank", exact: true })
        .click();
    await dialog
        .getByRole("button", { name: "Konfirmasi pembayaran", exact: true })
        .click();
    await expect(
        dialog
            .getByRole("alert")
            .filter({ hasText: /Periksa dana masuk pada rekening/ }),
    ).toBeVisible();
    await dialog
        .getByRole("checkbox", { name: /Saya sudah memeriksa dana masuk/ })
        .check();
    await dialog
        .getByLabel("Referensi pembayaran (opsional)")
        .fill("BANK-QA-123");
    await page.screenshot({
        path: testInfo.outputPath("manual-payment-390.png"),
        fullPage: true,
        animations: "disabled",
    });
    await dialog
        .getByRole("button", { name: "Konfirmasi pembayaran", exact: true })
        .click();
    await expect(
        dialog.getByRole("heading", {
            name: "Transaksi berhasil.",
            exact: true,
        }),
    ).toBeVisible();
    expect(errors).toEqual([]);
});

test("simplified workspaces render at phone and desktop widths without script errors or document overflow", async ({
    page,
}, testInfo) => {
    const errors = [];
    page.on("pageerror", (error) => errors.push(error.message));
    await signIn(page);
    for (const width of [320, 390, 1440]) {
        await page.setViewportSize({ width, height: 960 });
        for (const [path, name] of [
            ["/dashboard", "summary"],
            ["/inventory", "stock"],
            ["/reports", "reports"],
            ["/workspace/settings", "settings"],
            ["/tenants/create", "add"],
            ["/products/create", "catalog"],
        ]) {
            const response = await page.goto(path);
            expect(response.status()).toBe(200);
            await expect(page.locator("h1").first()).toBeVisible();
            await page.screenshot({
                path: testInfo.outputPath(`${name}-${width}.png`),
                fullPage: true,
                animations: "disabled",
            });
            const dimensions = await page.evaluate(() => ({
                viewport: window.innerWidth,
                document: document.documentElement.scrollWidth,
            }));
            expect(
                dimensions.document,
                `${path} at ${width}`,
            ).toBeLessThanOrEqual(dimensions.viewport);
        }
    }
    expect(errors).toEqual([]);
});

test("workspace submenus stay below the header and out of floating notifications", async ({
    page,
}, testInfo) => {
    await signIn(page);
    for (const width of [390, 1440]) {
        await page.setViewportSize({ width, height: 960 });
        for (const [path, section] of [
            ["/orders", "transactions"],
            ["/customers", "transactions"],
            ["/products", "catalog"],
            ["/inventory", "stock"],
            ["/reports", "reports"],
        ]) {
            await page.goto(path);
            const submenu = page.getByRole("navigation", {
                name: `Menu ${section}`,
                exact: true,
            });
            await expect(submenu).toHaveCount(1);
            await expect(submenu).toBeVisible();
            await expect(page.locator("[data-toast-region] nav")).toHaveCount(
                0,
            );
            const navBox = await submenu.boundingBox();
            const headerBox = await page
                .locator("header")
                .first()
                .boundingBox();
            expect(navBox.y).toBeGreaterThanOrEqual(
                headerBox.y + headerBox.height - 1,
            );
            if (path === "/orders")
                await page.screenshot({
                    path: testInfo.outputPath(`orders-navigation-${width}.png`),
                    fullPage: true,
                    animations: "disabled",
                });
        }
    }
});

test("a checkout appears in history, updates stock, and rolls into reports", async ({
    page,
}, testInfo) => {
    await signIn(page);

    await page.goto("/inventory?search=Beras%20Premium%20QA");
    const stockRow = page.getByRole("row").filter({
        hasText: "Beras Premium QA",
    });
    const stockCell = stockRow.getByRole("cell").nth(1);
    const stockBefore = Number(
        (await stockCell.innerText())
            .trim()
            .replace(/\./g, "")
            .replace(",", "."),
    );
    expect(stockBefore).toBeGreaterThan(0);

    await page.goto("/reports");
    const salesSummary = page.getByRole("region", { name: "Ringkasan penjualan" });
    const revenueCard = salesSummary.locator("div").nth(0);
    const transactionCard = salesSummary.locator("div").nth(1);
    const readAmount = async (locator) =>
        Number((await locator.innerText()).replace(/\D/g, ""));
    const revenueBefore = await readAmount(revenueCard.locator("p").nth(0));
    const transactionCountBefore = await readAmount(
        transactionCard.locator("p").nth(0),
    );

    await page.goto("/pos");
    const shiftDialog = page.getByRole("dialog", { name: "Detail kasir" });
    const startShift = shiftDialog.getByRole("button", {
        name: "Mulai jualan",
        exact: true,
    });
    if (await startShift.isVisible()) {
        await shiftDialog
            .getByRole("textbox", { name: "Modal awal (Rp)", exact: true })
            .fill("0");
        await startShift.click();
        await expect(shiftDialog).not.toBeVisible();
    }

    await page.getByRole("button", { name: /BE Beras Premium QA/ }).click();
    await shiftDialog
        .getByRole("button", { name: /^Tambahkan/ })
        .click();
    await page
        .getByRole("button", { name: "Lanjut pembayaran →", exact: true })
        .click();
    const checkoutDialog = page.getByRole("dialog", { name: "Detail kasir" });
    await checkoutDialog
        .getByRole("button", { name: "Uang pas", exact: true })
        .click();
    await checkoutDialog
        .getByRole("button", { name: "Konfirmasi pembayaran", exact: true })
        .click();
    await expect(
        checkoutDialog.getByRole("heading", {
            name: "Transaksi berhasil.",
            exact: true,
        }),
    ).toBeVisible();
    const invoice = await checkoutDialog.getByText(/INV-/).last().innerText();

    await page.goto("/orders");
    await expect(page.getByText(invoice, { exact: true })).toBeVisible();
    await expect(
        page.getByRole("row").filter({ hasText: invoice }),
    ).toContainText("Lunas");

    await page.goto("/inventory?search=Beras%20Premium%20QA");
    await expect(stockCell).toHaveText(
        new Intl.NumberFormat("id-ID", { maximumFractionDigits: 3 }).format(
            stockBefore - 1,
        ),
    );

    await page.goto("/reports");
    await expect(revenueCard.locator("p").nth(0)).toHaveText(
        `Rp ${new Intl.NumberFormat("id-ID").format(revenueBefore + 15000)}`,
    );
    await expect(transactionCard.locator("p").nth(0)).toHaveText(
        String(transactionCountBefore + 1),
    );
    await page.screenshot({
        path: testInfo.outputPath("integrated-sales-report.png"),
        fullPage: true,
        animations: "disabled",
    });
});
