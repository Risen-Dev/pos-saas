import * as Turbo from "@hotwired/turbo";

window.Turbo = Turbo;
Turbo.setProgressBarDelay(180);

const activeClasses = (element) =>
    (element.dataset.navActiveClass || "bg-primary-50 text-primary-700")
        .split(/\s+/)
        .filter(Boolean);
const idleClasses = (element) =>
    (element.dataset.navIdleClass || "text-ink-700 hover:bg-surface-100")
        .split(/\s+/)
        .filter(Boolean);

function matchesRoute(route, patterns = "") {
    return patterns.split(",").some((pattern) => {
        const value = pattern.trim();
        return value.endsWith("*")
            ? route.startsWith(value.slice(0, -1))
            : route === value;
    });
}

function syncNavigation(route) {
    for (const element of document.querySelectorAll("[data-nav-patterns]")) {
        const active = matchesRoute(route, element.dataset.navPatterns);
        element.classList.remove(
            ...(active ? idleClasses(element) : activeClasses(element)),
        );
        element.classList.add(
            ...(active ? activeClasses(element) : idleClasses(element)),
        );
        if (element instanceof HTMLAnchorElement) {
            if (active) element.setAttribute("aria-current", "page");
            else element.removeAttribute("aria-current");
        }
    }

    for (const group of document.querySelectorAll(
        "[data-sidebar-group-patterns]",
    )) {
        const state = window.Alpine?.$data(group);
        if (state)
            state.open = matchesRoute(
                route,
                group.dataset.sidebarGroupPatterns,
            );
    }
}

document.addEventListener("turbo:before-frame-render", (event) => {
    if (event.target.id !== "app-content") return;
    window.growposPageCleanup?.();
    window.dispatchEvent(new Event("growpos:before-content-change"));
});

document.addEventListener("turbo:frame-load", (event) => {
    const frame = event.target;
    if (!(frame instanceof HTMLElement) || frame.id !== "app-content") return;

    const context = frame.querySelector("[data-page-context]");
    if (context) {
        document.title = context.dataset.pageTitle || document.title;
        const shellTitle = document.querySelector("[data-shell-page-title]");
        if (shellTitle)
            shellTitle.textContent =
                context.dataset.pageTitle?.replace(/^GrowPOS - /, "") ||
                shellTitle.textContent;
        syncNavigation(context.dataset.routeName || "");
    }

    const main = document.getElementById("main-content");
    if (main) main.scrollTop = 0;

    const shell = document.getElementById("app-shell");
    if (shell && window.Alpine) window.Alpine.$data(shell).sidebarOpen = false;
});

document.addEventListener("turbo:frame-missing", (event) => {
    if (event.target.id !== "app-content") return;
    event.preventDefault();
    event.detail.visit(event.detail.response.url);
});

document.addEventListener("turbo:before-visit", () => {
    window.growposPageCleanup?.();
});

export { Turbo };
