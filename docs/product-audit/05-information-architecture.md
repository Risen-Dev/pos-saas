# 05 — Information architecture yang diusulkan

## Diagnosis

GrowPOS memiliki kebutuhan role yang berbeda, namun owner navigation menyimpan terlalu banyak domain di parent generik dan menambahkan tab kontekstual di sebagian halaman. “Unified” sebaiknya berarti satu bahasa visual dan pola interaksi dasar, bukan daftar menu dan hierarki sama untuk semua orang.

## Alternatif yang dibandingkan

| Opsi | Bentuk | Kelebihan | Trade-off |
|---|---|---|---|
| A. Shared universal nav (sekarang, dirapikan) | Semua owner melihat top-level kategori identik; tab tetap | Biaya perubahan terendah; mudah dipelajari antar role admin | Kategori generik dan menu tak relevan tetap muncul; tidak menyelesaikan concern “terlalu unified”. |
| B. Nav sepenuhnya terpisah per role | Shell dan menu kasir/owner/admin berbeda tajam | Sangat fokus; terminal kasir bisa minim | Menduplikasi struktur visual; pengguna multi-role kehilangan orientasi konsisten; pemeliharaan dan transisi role rumit. |
| **C. Shared shell, workspace per role/task (direkomendasikan)** | Identitas visual/core controls bersama; nav, landing, quick actions dan subnav berubah sesuai role dan modul | Fokus tiap pekerjaan, menghindari satu taxonomy untuk semua, tetap mempertahankan komponen bersama | Perlu aturan permission yang eksplisit, desain transisi role/context, dan usability validation. |

Rekomendasi: **C**. Ini mempertahankan konsistensi interaksi tanpa menyatukan struktur informasi secara paksa.

## Sitemap usulan

```text
Workspace selector (hanya jika akun memiliki >1 konteks)
├── Kasir workspace (role: kasir; hanya izin operasional)
│   ├── Jual (default; POS)
│   ├── Transaksi saya (riwayat, detail, cetak/kirim, retur bila diizinkan)
│   └── Shift (buka / aktivitas kas / tutup / ringkasan shift)
│
├── Usaha (owner/admin tenant)
│   ├── Ringkasan (hari ini; alert dan tindakan)
│   ├── Penjualan
│   │   ├── Kasir / terminal
│   │   ├── Transaksi & retur
│   │   ├── Promo
│   │   └── Shift & rekonsiliasi
│   ├── Katalog
│   │   ├── Produk / menu / jasa (label sesuai tipe usaha)
│   │   ├── Kategori
│   │   └── Varian, tambahan & resep (modul kondisional)
│   ├── Persediaan (modul kondisional)
│   │   ├── Stok & penyesuaian
│   │   ├── Pembelian & penerimaan
│   │   ├── Pemasok
│   │   ├── Stocktake & riwayat
│   │   └── Bahan baku (food)
│   ├── Pelanggan (jika paket/permission aktif)
│   │   ├── Daftar & detail/riwayat
│   │   └── Loyalty
│   ├── Operasi (modul kondisional)
│   │   ├── Antrean dapur (food)
│   │   └── Pengerjaan jasa (services)
│   └── Laporan
│       ├── Penjualan
│       ├── Kas & pengeluaran
│       ├── Stok
│       ├── Pegawai/shift
│       ├── Ekspor
│       └── Analisis AI (opt-in/plan)
│
├── Pengaturan usaha (owner/admin tenant)
│   ├── Profil & jenis usaha
│   ├── Pembayaran & pajak
│   ├── Pegawai & akses
│   ├── Bisnis/outlet (sesuai definisi product)
│   ├── Paket & langganan
│   └── Aktivitas
├── Akun & keamanan (semua role; self-service terbatas)
└── Bantuan (contextual; tidak menjadi submenu operasional)

Platform console (platform-admin; app boundary terpisah)
├── Pembayaran/pencairan yang perlu review
├── Audit platform
└── Health/support
```

## Aturan konteks dan disclosure

- Kasir masuk langsung ke layar jual dengan shift status yang jelas; hindari menampilkan laporan/konfigurasi yang tak diperlukan.
- Owner masuk ke Ringkasan bila butuh monitor bisnis, atau ke workspace terakhir jika tugas harian berulang; jangan selalu memaksa dashboard.
- Tampilkan business context yang sedang aktif secara jelas. Jika produk tetap memakai tenant sebagai “outlet”, label harus menyatakan data tidak digabung. Jika bisnis multi-outlet parent disetujui, tampilkan filter parent vs outlet dan scope angka.
- Subnav hanya untuk bagian yang memang berada pada satu job domain. Mobile gunakan picker/segmented navigation terukur, bukan daftar tab yang ber-wrap tak terkendali.
- Search menu opsional hanya setelah analytics menunjukkan menu dalam cukup banyak; command palette bukan solusi untuk kategori yang tak jelas.
- Quick actions didasari frekuensi dan role, maksimal 1 primary action di satu viewport. Platform actions tidak muncul di pengaturan usaha.

## Keselarasan izin

Kartu/nav disembunyikan berdasarkan ability server-side; menyembunyikan link bukan kontrol akses. Semua route tetap menegakkan policy/gate. Saat permission berubah, halaman harus memberi 403 jelas dan menyediakan jalan kembali. `aria-current=page` hanya menandai link yang benar-benar merupakan halaman aktif.

## Validasi IA sebelum implementasi

Uji card sort ringan dengan owner/kasir yang nyata (tanpa menganggap asumsi tim sebagai bukti). Minta peserta menemukan: transaksi kemarin, pelanggan dengan bon, tutup shift, stok opname, terima pembelian parsial, ganti pajak, tambah pegawai, lihat margin, ganti bisnis aktif. Ukur first click, keberhasilan tanpa bantuan, waktu, dan label salah tafsir. Jika belum ada peserta, tandai hasil sebagai hipotesis dan lakukan moderated usability sesudah prototipe IA.

