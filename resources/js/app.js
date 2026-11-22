import Alpine from "alpinejs";
import { initDialogs } from "./dialogs.js";
import connectionStatus from "./connection-status.js";
import { initRupiahInputs } from "./currency-input.js";
import { onDomReady } from "./dom-ready.js";
import { initPageMotion } from "./motion.js";
import toastStack from "./notifications.js";
import "./navigation.js";

window.Alpine = Alpine;
Alpine.data("connectionStatus", connectionStatus);
Alpine.data("toastStack", toastStack);
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

const pageCleanups = (window.growposPageCleanups ??= []);
let chartLoadVersion = 0;

window.registerGrowposPageCleanup = (cleanup) => {
    if (typeof cleanup === "function") pageCleanups.push(cleanup);
};

window.growposPageCleanup = () => {
    for (const cleanup of pageCleanups.splice(0).reverse()) {
        try {
            cleanup();
        } catch (error) {
            console.error("GrowPOS page cleanup failed:", error);
        }
    }
};

function initializePage(root) {
    const dialogCleanup = initDialogs(root);
    const motionCleanup = initPageMotion(root);
    const currencyCleanup = initRupiahInputs(root);
    for (const error of root.querySelectorAll("[data-field-error]")) {
        const field = error.dataset.fieldError;
        const control = [
            ...root.querySelectorAll("input,select,textarea"),
        ].find((element) => element.name === field);
        if (control) {
            control.setAttribute("aria-invalid", "true");
            control.setAttribute("aria-describedby", error.id);
        }
    }
    pageCleanups.push(dialogCleanup, motionCleanup, currencyCleanup);

    const trendChart = root.querySelector("#trendChart");
    if (trendChart) {
        const version = ++chartLoadVersion;
        import("chart.js/auto")
            .then(({ default: Chart }) => {
                if (version !== chartLoadVersion || !trendChart.isConnected)
                    return;
                window.Chart = Chart;
                window.dispatchEvent(new Event("growpos:charts-ready"));
            })
            .catch(() => {
                if (version !== chartLoadVersion || !trendChart.isConnected)
                    return;
                // The numeric report remains available when the optional chart fails.
                root.querySelectorAll("canvas").forEach((canvas) => {
                    canvas.setAttribute(
                        "aria-label",
                        "Grafik belum dapat dimuat. Ringkasan angka tetap tersedia.",
                    );
                });
                window.dispatchEvent(new Event("growpos:charts-error"));
            });
    }
}

onDomReady(() =>
    initializePage(document.getElementById("app-content") || document),
);

document.addEventListener("turbo:frame-load", (event) => {
    if (event.target.id === "app-content") initializePage(event.target);
});
