# GrowPOS — Asset Manifest

## Paket visual

| Jalur | Isi | Sumber/status |
|---|---|---|
| `keyframes/01-opening.png` … `06-final-hero.png` | Enam komposisi portrait, masing-masing 1080 × 1920 | Still pra-produksi; bukan render video |
| `keyframe-contact-sheet.png` | Contact sheet 3 × 2 | Montage QA, 1164 × 1360 |
| `assets/generated-backgrounds/01-opening.png` … `06-hero.png` | Pelat fotografi konsep kedai/perangkat dengan layar kosong | Gambar konsep; bukan foto produk GrowPOS di lokasi |
| `assets/ui-captures/pos-catalog.png` | Katalog POS desktop | Capture asli dari `public/video/growpos-demo.mp4` |
| `assets/ui-captures/product-selection.png` | Dialog opsi Kopi Susu Gula Aren | Frame rekaman sekitar 7,5 detik |
| `assets/ui-captures/order-cart.png` | Pesanan, subtotal, diskon, total | Capture transaksi asli |
| `assets/ui-captures/dashboard.png` | Ringkasan usaha | Capture asli |
| `assets/ui-captures/stock.png` | Tampilan stok | Capture asli |
| `assets/ui-captures/operational-report.png` | Laporan operasional | Capture asli |
| `assets/ui-captures/mobile-transaction-panel-crop.png` | Panel transaksi portrait | Crop dari panel desktop `order-cart.png`, ditata ulang sebagai konsep ponsel; **bukan capture responsif mobile** |
| `assets/ui-captures/interface-reference-contact-sheet.png` | Ringkasan capture sumber | Contact sheet audit UI |
| `assets/branding/growpos-mark-green.png` | Mark GrowPOS hijau transparan, 512 × 512 | `public/img/growpos_logo.png`, diperkecil |
| `assets/branding/growpos-mark-white.png` | Mark GrowPOS putih transparan, 512 × 512 | `public/img/growpos-white.png`, diperkecil |
| `assets/fonts/*.woff2` dan `LICENSE-*.txt` | Plus Jakarta Sans, Inter, IBM Plex Mono | Font latin yang digunakan layout aplikasi; lisensi disertakan |

## Nilai transaksi yang dapat digunakan

- Kopi Susu Gula Aren — `Rp 19.800 / gelas`.
- Subtotal — `Rp 22.000`.
- Diskon menu — `-Rp 2.200`.
- Total — `Rp 19.800`.
- Extra shot — `+Rp 5.000`; susu oat — `+Rp 6.000`.

Semua angka berasal dari capture aplikasi yang sama. Jangan menambahkan KPI/nominal lain tanpa verifikasi.

## Hal yang belum tersedia sebelum produksi

1. Foto produk nyata (kopi, makanan, barang) yang digunakan pelanggan. Kartu saat ini memakai fallback inisial, bukan fotografi produk.
2. Capture responsif katalog dan cart GrowPOS dari route/session ponsel yang benar. Panel portrait saat ini adalah crop adaptasi desktop.
3. Packshot atau foto perangkat aktual, finish perangkat yang disetujui, dan clean plate layar.
4. Capture UI resolusi tinggi tanpa toast/tutorial/kursor demonstrasi jika elemen itu tak muncul dalam film final.
5. Wordmark/logo horizontal resmi dan panduan safe area. Aset saat ini hanya mark ikon; “GrowPOS” di frame ditulis sebagai teks.
6. Font overlay raster final yang disetujui. Keyframe memakai Avenir Next sistem sebagai proxy; UI screenshot mempertahankan font aplikasi.
7. Footage/model release kasir dan lokasi bila gambar konsep akan diganti pengambilan nyata.

## Riwayat build yang dapat diaudit

Folder `growpos-motion/dist` dan render/dokumen V1–V3 telah dibersihkan sebelum audit ini; file lama tidak tersedia untuk pemeriksaan ulang. Prototipe Art of Selling saat ini berada pada `growpos-motion/remotion/src/scene.jsx`, dengan contact sheet render di `growpos-motion/remotion/out/rendered-video-contact-sheet.png`. Paket ini tidak menghasilkan atau mengekspor MP4.

## Catatan komposit

UI tidak digambar generator gambar. Capture aplikasi diproyeksikan secara perspektif ke layar perangkat pada pelat visual konsep. Label layar berasal dari capture asli. Tepi perangkat/pantulan pada still konsep perlu disempurnakan dengan clean plate dan matte produksi.
