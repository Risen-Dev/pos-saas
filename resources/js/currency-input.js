export function rupiahDigits(value) {
    return String(value ?? "").replace(/\D/g, "");
}

export function rupiahInput(value) {
    const digits = rupiahDigits(value);
    if (!digits) return "";
    return `Rp ${new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(Number(digits))}`;
}

export function initRupiahInputs(root) {
    root.querySelectorAll("[data-rupiah-input]").forEach((input) => {
        if (input.dataset.rupiahReady === "1") return;
        input.dataset.rupiahReady = "1";
        input.type = "text";
        input.inputMode = "numeric";
        input.autocomplete = "off";
        input.value = rupiahInput(input.value);
        input.addEventListener("input", () => {
            const digits = rupiahDigits(input.value);
            input.value = rupiahInput(digits);
            try {
                input.setSelectionRange(input.value.length, input.value.length);
            } catch (_) {}
        });
        input.form?.addEventListener("submit", () => {
            input.value = rupiahDigits(input.value);
        });
    });
    return () => {};
}
