import Alpine from "alpinejs";
import { initDialogs } from "./dialogs.js";
import connectionStatus from "./connection-status.js";
import { initRupiahInputs } from "./currency-input.js";
import { onDomReady } from "./dom-ready.js";
import { initPageMotion } from "./motion.js";
import toastStack from "./notifications.js";

window.Alpine = Alpine;
Alpine.data("connectionStatus", connectionStatus);
Alpine.data("toastStack", toastStack);
initPageMotion(document);

async function startAlpine() {
    if (document.querySelector('[x-data="growPos"]')) {
        const { default: growPos } = await import("./grow-pos.js");
        Alpine.data("growPos", growPos);
    }

    Alpine.start();
}

startAlpine();

// Safari/Chrome may restore a page from the back-forward cache. Reloading a
// restored page forces Laravel auth/tenant middleware to validate the session.
window.addEventListener("pageshow", (event) => {
    if (event.persisted) {
        window.location.reload();
    }
});

onDomReady(() => {
    initRupiahInputs(document);
    initDialogs(document);
    for (const error of document.querySelectorAll("[data-field-error]")) {
        const field = error.dataset.fieldError;
        const control = [
            ...document.querySelectorAll("input,select,textarea"),
        ].find((element) => element.name === field);
        if (control) {
            control.setAttribute("aria-invalid", "true");
            control.setAttribute("aria-describedby", error.id);
        }
    }
    if (document.getElementById("trendChart")) {
        import("chart.js/auto")
            .then(({ default: Chart }) => {
                window.Chart = Chart;
                window.dispatchEvent(new Event("growpos:charts-ready"));
            })
            .catch(() => {
                // The numeric report remains available when the optional chart fails.
                document.querySelectorAll("canvas").forEach((canvas) => {
                    canvas.setAttribute(
                        "aria-label",
                        "Grafik belum dapat dimuat. Ringkasan angka tetap tersedia.",
                    );
                });
                window.dispatchEvent(new Event("growpos:charts-error"));
            });
    }
});
