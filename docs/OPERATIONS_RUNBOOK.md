# Operasional release, backup dan recovery GrowPOS

Runbook untuk operator staging/production setelah kode perbaikan direview dan CI hijau. Tidak ada instruksi di sini yang telah men-deploy working tree ini.

## Prasyarat deployment

- Commit dan push perubahan yang telah direview ke `main`; `deploy.sh` menjalankan `git pull --ff-only origin main` pada `/var/www/pos-saas`.
- Host Linux dengan PHP >=8.3 dan ekstensi Composer, Node >=22, Composer, Git, web server dan PHP-FPM. Worker dan scheduler dikelola service manager.
- Aplikasi berjalan langsung dari `/var/www/pos-saas`; `.env`, storage, dan document root `/var/www/pos-saas/public` tetap digunakan. Ownership storage/bootstrap/cache harus cocok dengan user web/worker.
- Environment: APP_ENV=production, APP_DEBUG=false, APP_URL HTTPS, SESSION_SECURE_COOKIE=true, queue persisten, email delivery, Midtrans sesuai environment, support contact. Jangan menaruh credentials dalam command log/Git.
- Siapkan scheduler setiap menit menjalankan `php /var/www/pos-saas/artisan schedule:run` serta worker persisten.

## Menindaklanjuti `growpos:launch-check`

Untuk hasil yang dikirim operator pada 2026-11-03, nilai-nilai ini perlu disetel pada `/var/www/pos-saas/.env` melalui editor aman (jangan tempel secret ke chat/terminal yang direkam):

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://trialpos.fdevsite.cloud
SESSION_SECURE_COOKIE=true
MIDTRANS_IS_PRODUCTION=true
GROWPOS_BACKUP_DISK=s3
GROWPOS_SUPPORT_EMAIL=alamat-dukungan-bisnis@domain-anda
```

`MIDTRANS_IS_PRODUCTION=true` hanya setelah key produksi sudah dimasukkan ke `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY`; jangan aktifkan dengan key sandbox. Untuk disk S3, lengkapi `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, dan `AWS_BUCKET` dengan bucket privat yang benar-benar terpisah dari host aplikasi serta izin minimum yang diperlukan. Jangan menaruh kredensial di repository.

Setelah mengubah `.env`, dari `/var/www/pos-saas`:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan queue:restart
```

### Scheduler heartbeat

Pastikan cron milik user aplikasi (`fdev`) memanggil scheduler setiap menit. Periksa entry yang ada terlebih dahulu agar tidak menambahkan duplikat:

```cron
* * * * * cd /var/www/pos-saas && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Sesuaikan `/usr/bin/php` dengan `command -v php` pada server. Scheduler mengantrikan job heartbeat worker setiap menit dan menjalankan backup harian pukul 02:00.

### Worker heartbeat

Pastikan ada tepat worker persisten yang dijaga Supervisor/systemd dan berjalan sebagai user aplikasi dari `/var/www/pos-saas`. Untuk queue database, proses intinya:

```bash
php artisan queue:work database --sleep=3 --tries=3 --timeout=60 --max-time=3600
```

Jika Supervisor sudah terpasang, template tersedia di `ops/supervisor/growpos-worker.conf.example`. Periksa konfigurasi service yang ada dulu agar tidak menjalankan worker duplikat, lalu salin sebagai `/etc/supervisor/conf.d/growpos-worker.conf`, jalankan `sudo supervisorctl reread`, `sudo supervisorctl update`, dan periksa `sudo supervisorctl status growpos-worker:*`. Sesuaikan path PHP jika `command -v php` bukan `/usr/bin/php`. Bila menggunakan systemd, buat unit setara dengan `WorkingDirectory=/var/www/pos-saas`, user `fdev`, dan perintah queue di atas.

Sesudah start/restart worker, tunggu scheduler mengantrekan job heartbeat lalu jalankan ulang launch check. Jika heartbeat tetap gagal, lihat log worker dan `failed_jobs`; jangan menandai heartbeat secara manual.

### Backup heartbeat

Setelah bucket dan credential offsite siap, jalankan satu backup terkontrol:

```bash
php artisan growpos:backup
```

Command harus selesai sukses, mengunggah backup dan checksum, lalu membaca ulang object untuk membandingkan checksum. Jika gagal, periksa log tanpa menyalin credential atau isi dump. `Backup completed within 26 hours` baru lulus setelah backup tercatat. Heartbeat ini belum membuktikan restore berhasil; lakukan restore drill ke database/host terisolasi dan dokumentasikan hasilnya sebelum launch.

### Pemeriksaan akhir

```bash
php artisan schedule:list
php artisan growpos:launch-check
```

`APP_ENV`, secure cookie, kontak dukungan, worker, scheduler, dan offsite backup adalah kegagalan konfigurasi/operasi yang dapat ditangani dengan langkah di atas. Uji Midtrans produksi tetap perlu transaksi terkontrol yang direkonsiliasi; flag konfigurasi saja tidak membuktikan provider siap.

## Backup offsite

Konfigurasi disk privat terpisah, misalnya disk `s3` yang sudah didukung `config/filesystems.php`, dengan credential dari secret store/env operator. Tetapkan `GROWPOS_BACKUP_DISK=s3`, prefix khusus aplikasi dan `GROWPOS_BACKUP_RETENTION_DAYS=14` (minimum 2). Sesuaikan retention dan jadwal dengan RPO/RTO yang disepakati; default harian 02:00 berarti sasaran RPO maksimal 24 jam, bukan jaminan.

- Bucket harus privat dan terpisah dari volume/host aplikasi. Batasi IAM ke prefix backup; butuh read/write/list/delete untuk verifikasi dan retention.
- Adapter S3 mengirim ServerSideEncryption=AES256. Pastikan provider kompatibel mendukungnya; jika tidak, konfigurasi driver/enkripsi yang benar sebelum launch. Jangan mengabaikan kegagalan upload/verifikasi.
- `php artisan growpos:backup` membuat backup privat lokal, checksum, upload dan membaca ulang seluruh object untuk membandingkan SHA-256. Retention dijalankan hanya setelah salinan baru lolos verifikasi; backup lokal yang selesai tetap dipertahankan ketika offsite gagal.
- Pantau exit code, backup heartbeat, failed_jobs, worker/scheduler heartbeat, kapasitas disk dan lifecycle bucket. Production command gagal bila offsite belum dikonfigurasi.
- Backup mencakup database. Lampiran produk/logo dalam shared/storage memerlukan backup volume/object-storage sendiri; credential/env harus dipulihkan dari secret store secara terpisah.

## Deployment

1. CI wajib lulus: suite PHP, JS, build, dependency audit, MySQL concurrency/restore dan browser regression. Pada staging, uji webhook/status provider, email dan hardware yang digunakan.
2. Jalankan `./deploy.sh` dari server. Script berpindah ke `/var/www/pos-saas`, menarik `main`, memasang dependency, membangun aset, membersihkan cache, menjalankan migrasi, mengoptimalkan Laravel, lalu merestart queue.
3. Periksa landing page, login, kasir, dan log aplikasi setelah deployment. Jangan melakukan pembelian live sekadar smoke test tanpa prosedur merchant.

Dua migration perbaikan menambah activation marker/duration invoice, movement snapshot stocktake, FK users.tenant_id dan memperlebar due_date ke datetime. Lakukan backup dan cek orphan users sebelum rollout. Ukur durasi ALTER pada salinan volume production; MySQL dapat mengambil metadata lock. Tidak ada klaim online DDL tanpa pengukuran.

## Pemulihan deployment

Deployment langsung memperbarui checkout aktif, jadi tidak ada perpindahan symlink atau rollback otomatis. Jika deployment gagal, hentikan pengulangan script dan periksa pesan error serta log. Pulihkan kode melalui prosedur Git yang telah direview; migration database tidak di-rollback otomatis. Jangan menjalankan `migrate:rollback` production tanpa menilai dampak data.

## Restore drill

1. Unduh object backup dan checksum ke host recovery privat. Bandingkan SHA-256 dengan file checksum sebelum restore. Jangan tampilkan isi dump di log.
2. Buat database staging baru dengan credential dan host yang jelas terisolasi. **Jangan restore ke production yang sedang berjalan.**
3. Untuk MySQL, tempatkan dump tepercaya dan file `.sha256` di `storage/app/private/backups` pada environment recovery. `php artisan growpos:verify-mysql-backup /absolute/path/storage/app/private/backups/backup.sql` hanya menerima lokasi privat tersebut dan memverifikasi dump dengan database temporary terpisah; review guard/privilege command dahulu. CI juga menjalankan command ini terhadap database disposable.
4. Lakukan drill aplikasi penuh: restore database dan media ke environment recovery terisolasi, set URL/credential sandbox dan nonaktifkan integrasi live. Uji login, tenant, order/item/payment/cash/stock totals, export, struk dan file media.
5. Catat waktu backup, recovery point, durasi restore, hasil checksum, jumlah/total invariant dan operator. Tetapkan target RTO setelah hasil drill aktual; belum ada angka RTO terukur dalam pekerjaan ini.
6. Promosi recovery ke produksi harus dikelola operator dengan pencegahan double webhook/queue, rekonsiliasi provider dan rencana DNS/traffic. Drill sukses tidak otomatis mengotorisasi failover live.

## Billing recovery

`growpos:reconcile-billing` dijadwalkan setiap menit untuk pending invoice serta paid tanpa subscription. Command hanya memeriksa referensi pembayaran yang sudah ada; tidak membuat charge baru. Pantau error provider dan exit code. Jangan mengubah status invoice secara manual menjadi paid untuk memberi akses: aktivasi harus mengikuti hasil provider yang cocok nominal/identitas. Job reconciliation POS terpisah tetap berjalan.

## Pengukuran lanjutan

Gunakan `APP_ENV=testing php artisan growpos:benchmark-pos` hanya dengan konfigurasi testing yang tidak cached production. Command membuat database temporary sendiri. Untuk keputusan index/kapasitas, gunakan salinan MySQL staging, EXPLAIN query laporan, p95/p99 di bawah concurrency representatif dan pengukuran CWV pada perangkat sasaran. Jangan menghapus tenant lock untuk mengejar angka throughput tanpa pengujian invariant saldo/stok/billing.
