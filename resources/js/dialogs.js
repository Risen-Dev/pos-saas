const focusableSelector =
    'button:not([disabled]),a[href],input:not([type="hidden"]):not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex="0"]';

export function initDialogs(root = document) {
    const dialogs = [...root.querySelectorAll("[data-dialog]")];
    let active = null;
    let previousFocus = null;
    let previousOverflow = "";
    let background = [];
    const visible = (element) =>
        element.getClientRects().length > 0 &&
        getComputedStyle(element).display !== "none";
    const focusables = (dialog) =>
        [...dialog.querySelectorAll(focusableSelector)].filter(visible);
    const restore = () => {
        for (const [element, inert] of background) element.inert = inert;
        background = [];
        document.body.style.overflow = previousOverflow;
    };
    for (const [index, dialog] of dialogs.entries()) {
        dialog.setAttribute("role", "dialog");
        dialog.setAttribute("aria-modal", "true");
        dialog.tabIndex = -1;
        const heading = dialog.querySelector("h1,h2,h3,h4");
        if (heading) {
            heading.id ||= `growpos-dialog-heading-${index}`;
            dialog.setAttribute("aria-labelledby", heading.id);
        }
        for (const button of dialog.querySelectorAll("button")) {
            if (
                !button.textContent.trim() &&
                !button.getAttribute("aria-label")
            ) {
                button.setAttribute(
                    "aria-label",
                    button.title || "Tutup dialog",
                );
            }
        }
    }
    const update = () => {
        const next = dialogs.find(visible) || null;
        if (next === active) return;
        const wasActive = active;
        if (active) restore();
        active = next;
        if (!active) {
            if (previousFocus?.isConnected) previousFocus.focus();
            return;
        }
        if (!wasActive) previousFocus = document.activeElement;
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = "hidden";
        // Inert siblings along the ancestor path, never the dialog's ancestor.
        for (
            let node = active;
            node.parentElement && node !== document.body;
            node = node.parentElement
        ) {
            for (const sibling of node.parentElement.children) {
                if (sibling !== node && sibling instanceof HTMLElement) {
                    background.push([sibling, sibling.inert]);
                    sibling.inert = true;
                }
            }
        }
        (focusables(active)[0] || active).focus();
    };
    const observer = new MutationObserver(update);
    for (const dialog of dialogs) {
        observer.observe(dialog, {
            attributes: true,
            attributeFilter: ["class", "style", "hidden"],
        });
    }
    document.addEventListener("keydown", (event) => {
        if (!active) return;
        if (event.key === "Escape") {
            const close = [...active.querySelectorAll("button")].find(
                (button) =>
                    button.hasAttribute("data-dialog-close") ||
                    /(?:Tutup|Batal|×)/i.test(
                        button.textContent +
                            " " +
                            button.getAttribute("aria-label"),
                    ),
            );
            if (close && !close.disabled) {
                event.preventDefault();
                close.click();
            }
        }
        if (event.key === "Tab") {
            const nodes = focusables(active);
            if (!nodes.length) {
                event.preventDefault();
                active.focus();
                return;
            }
            const first = nodes[0];
            const last = nodes.at(-1);
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        }
    });
    update();
}
