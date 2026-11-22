export function initPageMotion(root = document) {
    if (
        window.matchMedia?.("(prefers-reduced-motion: reduce)").matches ||
        typeof window.IntersectionObserver !== "function"
    ) {
        return;
    }

    const groups = root.querySelectorAll("[data-motion-stagger]");
    if (!groups.length) return;

    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (!entry.isIntersecting) continue;

                entry.target.dataset.motionState = "visible";
                observer.unobserve(entry.target);
            }
        },
        { threshold: 0.08, rootMargin: "0px 0px -24px 0px" },
    );

    for (const group of groups) {
        const items = [...group.children].filter(
            (item) => !item.hasAttribute("data-motion-ignore"),
        );

        items.forEach((item, index) => {
            item.style.setProperty(
                "--motion-delay",
                `${Math.min(index * 42, 210)}ms`,
            );
            item.dataset.motionItem = "";
            item.dataset.motionState = "pending";
            observer.observe(item);
        });
    }
}
