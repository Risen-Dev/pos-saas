const defaultDuration = {
    success: 5000,
    info: 5000,
    warning: 7000,
    error: 8000,
};

export default (initialNotifications = []) => ({
    notifications: [],
    timers: {},
    nextId: 1,
    eventHandler: null,

    init() {
        this.notifications = initialNotifications.map((notice) => ({
            ...notice,
            id: this.nextId++,
            visible: true,
        }));
        this.notifications.forEach((notice) => this.schedule(notice));

        try {
            const pending = sessionStorage.getItem("growpos:pending-toast");
            if (pending) {
                sessionStorage.removeItem("growpos:pending-toast");
                this.push(JSON.parse(pending));
            }
        } catch {
            // Storage may be disabled; regular in-page notifications still work.
        }

        this.eventHandler = (event) => this.push(event.detail);
        window.addEventListener("growpos:toast", this.eventHandler);
    },

    destroy() {
        window.removeEventListener("growpos:toast", this.eventHandler);
        Object.values(this.timers).forEach((timer) =>
            clearTimeout(timer.handle),
        );
    },

    push(detail = {}) {
        if (typeof detail.message !== "string" || !detail.message.trim())
            return;

        const type = Object.hasOwn(defaultDuration, detail.type)
            ? detail.type
            : "success";
        const notice = {
            id: this.nextId++,
            type,
            message: detail.message.trim(),
            duration: Number(detail.duration) || defaultDuration[type],
            visible: true,
        };

        while (this.notifications.length >= 4) {
            const oldest = this.notifications.shift();
            clearTimeout(this.timers[oldest.id]?.handle);
            delete this.timers[oldest.id];
        }

        this.notifications.push(notice);
        this.schedule(notice);
    },

    schedule(
        notice,
        duration = notice.duration || defaultDuration[notice.type],
    ) {
        if (!duration || !notice.visible) return;

        clearTimeout(this.timers[notice.id]?.handle);
        this.timers[notice.id] = {
            remaining: duration,
            startedAt: Date.now(),
            handle: window.setTimeout(() => this.dismiss(notice.id), duration),
        };
    },

    pause(id) {
        const timer = this.timers[id];
        if (!timer?.handle) return;

        clearTimeout(timer.handle);
        timer.remaining = Math.max(
            0,
            timer.remaining - (Date.now() - timer.startedAt),
        );
        timer.handle = null;
    },

    resume(id) {
        const timer = this.timers[id];
        const notice = this.notifications.find((item) => item.id === id);
        if (!timer || timer.handle || !notice?.visible) return;

        this.schedule(notice, timer.remaining);
    },

    dismiss(id) {
        const notice = this.notifications.find((item) => item.id === id);
        if (!notice) return;

        clearTimeout(this.timers[id]?.handle);
        notice.visible = false;
        window.setTimeout(() => {
            this.notifications = this.notifications.filter(
                (item) => item.id !== id,
            );
            delete this.timers[id];
        }, 220);
    },
});

window.growposToast = (message, type = "success", options = {}) => {
    const detail = { message, type, duration: options.duration };

    if (options.persistOnReload) {
        try {
            sessionStorage.setItem(
                "growpos:pending-toast",
                JSON.stringify(detail),
            );
        } catch {
            // The current page can still display the notice without session storage.
        }
    }

    if (options.defer) return;

    window.dispatchEvent(new CustomEvent("growpos:toast", { detail }));
};
