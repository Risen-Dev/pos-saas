# 07 — Spesifikasi interaksi yang direkomendasikan

## Pola lintas aplikasi

- Setiap halaman punya judul yang menyatakan objek/pekerjaan, satu primary action, filter yang terlihat aktif, dan feedback dekat dengan objek yang berubah.
- Gunakan route page untuk pekerjaan kompleks atau data penuh; modal untuk konfirmasi singkat; drawer untuk membaca/mengedit konteks yang harus tetap terlihat. Hindari modal bersarang.
- Form mempertahankan nilai valid saat error; error spesifik dekat field dan ringkasan fokus di atas; sukses menyebut hasil dan next action.
- Aksi destruktif/irreversibel menampilkan objek, scope, jumlah dan konsekuensi; jangan memakai dialog generik “Yakin?”.
- Loading untuk aksi submit memberi progress yang bisa dipahami dan mencegah duplikasi; aksi idempoten dapat dicoba ulang dengan aman.
- Status kerja harus memakai teks + ikon/warna, tidak warna saja.
- Semua aksi bisa keyboard; fokus tetap dan kembali sesuai konteks setelah modal/drawer; input label selalu terhubung dengan control.

## POS — prioritas throughput dan ketepatan

**Layout:** area item dan cart selalu terlihat pada layar besar; mode mobile menampilkan cart count/total yang sticky, cart drawer/page dengan tombol checkout jelas. Pencarian tetap fokus setelah scan kecuali pengguna berpindah task. Scanner input tidak boleh disalahartikan sebagai shortcut keyboard biasa.

**Tambah produk:** search menerima nama, SKU/barcode; hasil menampilkan nama, opsi, harga, dan stok relevan. Item dengan required variant/add-on membuka pemilih inline/drawer dengan pilihan wajib dan harga akhir. Item tanpa opsi dapat ditambah sekali dengan feedback singkat; repeated tap tetap menghormati qty/stock policy.

**Cart:** qty edit langsung, tombol plus/minus touch target besar, remove undo sementara; catatan per item dilabeli. Perubahan cart menghitung ulang harga, promo, pajak, dan total dalam urutan yang sama dengan server. Tampilkan diskon/pajak sebagai baris terpisah, bukan angka kejutan saat bayar.

**Bayar:** review total plus metode; cash menampilkan kembalian; transfer/manual QRIS menjelaskan status bukti dan langkah verifikasi; provider otomatis membedakan menunggu/sukses/gagal. Setelah submit, lock button untuk mencegah accidental duplicate, tetap cari idempotency di server. Jangan hapus cart sebelum server mengonfirmasi commit. Receipt tersedia setelah sukses dengan print/share dan “Transaksi baru”. Bon/partial pay menjelaskan saldo tertunggak dan cara pelunasan.

**Keyboard:** scanner/search → pilih → qty/opsi → bayar → metode → amount/confirm → receipt; shortcut terlihat dan tidak bertabrakan dengan input numeric, browser shortcut, atau assistive tech. Tetap tersedia touch path.

## Tabel dan daftar operasional

- Sediakan pencarian/filter yang dipakai pekerjaan; filter aktif terlihat, dapat dihapus satu per satu, dan tersimpan saat balik dari detail.
- Kolom utama: nama/nomor, status, waktu/nominal; tindakan sekunder diletakkan pada detail/menu row yang berlabel.
- Mobile: jangan mempertahankan tabel 6–8 kolom dengan scroll horizontal tanpa indikator. Pakai compact row/card dengan nilai kunci; detail sekunder disclose, bukan menghilang.
- Sort/filter/pagination harus mempertahankan query saat navigasi kembali. Empty state membedakan belum ada data dari filter tanpa hasil.
- Bulk action menampilkan jumlah item yang terpilih dan efek; batasi risiko select-all lintas halaman.

## Mutasi stok dan uang

- Adjustment/return/cash-out membutuhkan alasan dan menampilkan nilai before/after bila relevan.
- Preview sebelum post: item/qty/nominal/tenant/dampak ke stok atau kas.
- Status pending tidak sama dengan failure. Berikan nomor referensi dan instruksi retry/reconcile.
- Aksi ganda menerima hasil yang sama atau mengembalikan hasil pertama (idempotent); UI memperlihatkan satu transaksi.
- Close shift dan return/refund meminta reconfirm hanya bila data review benar-benar berubah sejak dibuka.

## Form pengaturan

- Kelompokkan field menurut task, jangan satu form panjang tanpa heading; tandai required/optional dan format contoh.
- Auto-save hanya untuk preference sederhana; perubahan pajak, akses, paket atau payment harus eksplisit disimpan dan dikonfirmasi.
- Import memvalidasi file lalu menampilkan pratinjau row valid/invalid; user dapat mengunduh error dan mengulang tanpa duplikasi.

## State contract minimum per halaman

Default, loading, empty, no results, field validation error, server error, permission denied, network interruption, success, dataset besar, dan role dengan akses terbatas harus mempunyai copy/visual/action. State-state ini belum dipastikan semua ada dalam render live; acceptance test ada di dokumen 11.

