export default () => ({
    online: navigator.onLine,
    init() {
        window.addEventListener("online", () => {
            this.online = true;
        });
        window.addEventListener("offline", () => {
            this.online = false;
        });
    },
    get label() {
        return this.online ? "Jaringan tersedia" : "Koneksi terputus";
    },
});
