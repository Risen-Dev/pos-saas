# GrowPOS design foundation — proposal audit

> **Status:** proposal per 22 November 2026; belum menjadi single source of truth aktif. Token dirangkum dari tailwind.config.js dan aturan pemakaiannya direkomendasikan; validasi browser diperlukan sebelum adopsi.

## Prinsip desain

1. Cepat untuk pekerjaan berulang: layar kasir mengutamakan scan/search, cart, total, pembayaran dan recovery.
2. Konteks terlihat: bisnis/outlet, shift, periode dan status transaksi dekat dengan hasil.
3. Satu visual language, shell sesuai pekerjaan: komponen dasar bersama; kasir, owner dan console platform tidak harus memakai nav/layout yang sama.
4. Hierarki sebelum dekorasi: judul, angka, status, primary action dan data utama membentuk kontras.
5. Uang dan stok eksplisit: nominal, status pending, retur dan variance dapat ditelusuri.

## Token yang saat ini terlihat

| Token | Nilai Tailwind | Catatan |
|---|---|---|
| primary.900 / .700 / .600 / .500 | #0B3D2E / #12664B / #16805F / #1FA179 | Brand dan aksi; putih di atas primary 600 perlu dicek contrast. |
| primary.100 / .50 | #DCF3E9 / #F1FAF6 | Background terpilih/soft. |
| accent.700 / .500 / .100 | #B45309 / #F0932B / #FDEBD3 | Promo/secondary; warning butuh semantics jelas. |
| ink.900 / .700 / .400 | #1A2421 / #465550 / #8B9994 | Main, secondary, placeholder. Ink 400 jangan untuk teks kecil penting tanpa uji kontras. |
| surface.0 / .100 | #FFFFFF / #F7F9F8 | Card dan page. |
| border.200 | #E3E9E6 | Divider/border. |
| semantic | success #1FA179; warning #F0932B; danger #E24C4B; info #3B82C4 | Tambahkan pasangan foreground/background/border dan focus/disabled state bila diadopsi. |
| font family | Plus Jakarta Sans (heading), Inter (body), JetBrains Mono/IBM Plex Mono (angka) | Fallback harus tetap readable. |
| spacing | unit 4 px; 4/8/12/16/20/24/32/40/48 px umum | Target sentuh bukan sekadar spacing token. |
| radius/shadow | 6/10/16 px dan pill; tiga level elevation | Gunakan untuk hierarchy/layer, bukan setiap komponen. |
| breakpoints | 360/768/1024/1440 px | QA tambahan 320 px karena screenshot dan kebutuhan mobile. |

## Semantik dan aturan komponen yang disarankan

- action.primary: satu aksi utama per area; foreground/background teruji.
- status.success/warning/danger/info: icon dan teks ditambah surface/border; tidak hanya hue.
- focus.ring dan disabled state harus terlihat pada background terpilih maupun default.
- Cashier: touch-safe density; scan/search dan item entry berdekatan; cart/total mudah ditemukan; payment confirmation menyatakan jumlah dan status.
- Owner: list-first, action-driven; metrik berdefinisi; filter/periode/scope terlihat.
- Tables: angka tabular rata kanan; kolom inti pertama; mobile menjadi record row dengan nilai utama, bukan mengecilkan seluruh tabel.
- Buttons: verb + object seperti “Terima stok”; destructive action memiliki copy jelas dan bukan primary color.
- Forms: label terlihat, required state eksplisit, error dekat field, feedback submit.
- Navigation: role-specific IA dalam shared component/token system; selected item tepat; tab kontekstual dibatasi dan tidak wrap tanpa intent.
- Cards mengelompokkan ringkasan/status/aksi; link sederhana sebaiknya section/list.
- Motion singkat dan fungsional; hormati prefers-reduced-motion.

## Tipografi, angka, dan responsif

Gunakan H1 untuk pekerjaan, H2 untuk bagian, body untuk petunjuk, metadata untuk scope/status. Format Rupiah dan tanggal harus memakai locale/timezone yang konsisten di backend, UI dan export. Jangan mencampur periode ambigu atau membuat teks kecil/uppercase panjang pada layar kasir.

Pastikan 320/360/390 px tidak menghabiskan ruang dengan nav ber-wrap; 768 px menentukan layout dari task bukan hanya breakpoint; 1024/1440 px dapat menampilkan daftar/detail bila mempercepat kerja. Uji zoom 200%, landscape tablet, keyboard virtual, label panjang dan empty/error data.

## Adoption checklist

Sebelum file ini menjadi SSOT: setujui semantic token, ukur kontras, audit komponen nyata, sepakati batas shell kasir/owner/platform, render viewport, validasi bersama cashier dan owner, sinkronkan Tailwind dan dokumentasi.

Skill design-md mengharuskan sumber Stitch yang dihubungkan. Sumber itu tidak tersedia, sehingga file ini adalah proposal yang berlandaskan repo, bukan ekstraksi Stitch.

