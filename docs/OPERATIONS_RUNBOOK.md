# Operasional release, backup dan recovery GrowPOS

Runbook untuk operator staging/production setelah kode perbaikan direview dan CI hijau. Tidak ada instruksi di sini yang telah men-deploy working tree ini.

## Prasyarat release

- Commit dan push perubahan yang telah direview. `deploy.sh` membangun dari `git archive DEPLOY_REF` (default origin/main), sehingga perubahan belum di-commit tidak ikut release.
- Host Linux dengan PHP >=8.3 dan ekstensi Composer, Node >=22, Composer, Git, flock, GNU mv, curl, mysqldump/mysql untuk MySQL, web server dan PHP-FPM. Worker dan scheduler dikelola service manager.
- `DEPLOY_ROOT` default `/var/www/pos-saas`. Siapkan `shared/.env` privat dan `shared/storage`. Web root harus mengarah ke `current/public`; worker menggunakan `current/artisan`. Ownership storage/bootstrap/cache harus cocok dengan user web/worker.
- Environment: APP_ENV=production, APP_DEBUG=false, APP_URL HTTPS, SESSION_SECURE_COOKIE=true, queue persisten, email delivery, Midtrans sesuai environment, support contact. Jangan menaruh credentials dalam command log/Git.
- Siapkan scheduler setiap menit menjalankan `php /var/www/pos-saas/current/artisan schedule:run` serta worker persisten. Pada setup pertama, bootstrap release staging/provisioned harus sudah memiliki schema/worker sehingga heartbeat dapat dinilai; script sengaja menolak launch check yang belum sehat.

## Backup offsite

Konfigurasi disk privat terpisah, misalnya disk `s3` yang sudah didukung `config/filesystems.php`, dengan credential dari secret store/env operator. Tetapkan `GROWPOS_BACKUP_DISK=s3`, prefix khusus aplikasi dan `GROWPOS_BACKUP_RETENTION_DAYS=14` (minimum 2). Sesuaikan retention dan jadwal dengan RPO/RTO yang disepakati; default harian 02:00 berarti sasaran RPO maksimal 24 jam, bukan jaminan.

- Bucket harus privat dan terpisah dari volume/host aplikasi. Batasi IAM ke prefix backup; butuh read/write/list/delete untuk verifikasi dan retention.
- Adapter S3 mengirim ServerSideEncryption=AES256. Pastikan provider kompatibel mendukungnya; jika tidak, konfigurasi driver/enkripsi yang benar sebelum launch. Jangan mengabaikan kegagalan upload/verifikasi.
- `php artisan growpos:backup` membuat backup privat lokal, checksum, upload dan membaca ulang seluruh object untuk membandingkan SHA-256. Retention dijalankan hanya setelah salinan baru lolos verifikasi; backup lokal yang selesai tetap dipertahankan ketika offsite gagal.
- Pantau exit code, backup heartbeat, failed_jobs, worker/scheduler heartbeat, kapasitas disk dan lifecycle bucket. Production command gagal bila offsite belum dikonfigurasi.
- Backup mencakup database. Lampiran produk/logo dalam shared/storage memerlukan backup volume/object-storage sendiri; credential/env harus dipulihkan dari secret store secara terpisah.

## Deployment

1. CI wajib lulus: suite PHP, JS, build, dependency audit, MySQL concurrency/restore dan browser regression. Pada staging, uji webhook/status provider, email dan hardware yang digunakan.
2. Jalankan `DEPLOY_ROOT=/var/www/pos-saas DEPLOY_REF=origin/main PHP_FPM_SERVICE=php8.3-fpm ./deploy.sh` dari checkout sumber operator. Sesuaikan nama service aktual; nilai contoh bukan auto-detection.
3. Script mengunci deployment, membuat release baru, install/build/audit, backup, migrate, optimize dan launch-check sebelum switch symlink. Backup harus benar-benar berhasil; jangan bypass exit code.
4. Switch atomik diikuti reload FPM opsional, health URL `/up` dan queue restart. Health URL harus menuju vhost release yang diuji (set DEPLOY_HEALTH_URL bila localhost default bukan vhost aplikasi).
5. Periksa launch-check, login, checkout fixture staging, billing, queue/export dan log setelah release. Jangan melakukan pembelian live sekadar smoke test tanpa prosedur merchant.

### Peralihan pertama dari document root lama

Server lama dapat tetap melayani `/var/www/pos-saas/public` selama release pertama disiapkan. Sebelum menjalankan script, salin konfigurasi dan storage aktif ke lokasi shared (perintah ini hanya untuk setup pertama; pastikan backup tersedia dan jangan menampilkan isi `.env`):

```bash
sudo install -d -o fdev -g www-data -m 2775 /var/www/pos-saas/shared/storage
sudo install -o fdev -g www-data -m 640 /var/www/pos-saas/.env /var/www/pos-saas/shared/.env
sudo rsync -a /var/www/pos-saas/storage/ /var/www/pos-saas/shared/storage/
sudo chown -R fdev:www-data /var/www/pos-saas/shared/storage
```

Commit dan push kode ke `main`, lalu jalankan release pertama. Setelah `current` berhasil dibuat, ubah `root` pada vhost `trialpos.fdevsite.cloud` dari `/var/www/pos-saas/public` menjadi `/var/www/pos-saas/current/public`, jalankan `sudo nginx -t`, lalu reload Nginx. Verifikasi `/up` dan aset hero melalui domain. Perbarui juga path worker dan scheduler agar menjalankan `/var/www/pos-saas/current/artisan`.

Dua migration perbaikan menambah activation marker/duration invoice, movement snapshot stocktake, FK users.tenant_id dan memperlebar due_date ke datetime. Lakukan backup dan cek orphan users sebelum rollout. Ukur durasi ALTER pada salinan volume production; MySQL dapat mengambil metadata lock. Tidak ada klaim online DDL tanpa pengukuran.

## Rollback

Script mengembalikan symlink sebelumnya jika gagal setelah switch, lalu mencoba reload FPM dan restart worker. Schema tidak di-rollback otomatis. Gunakan migration kompatibel dengan release sebelumnya; jangan menjalankan migrate:rollback production tanpa review dampak data.

Untuk rollback manual, operator mengarahkan symlink temporary ke direktori release sehat lalu melakukan GNU `mv -Tf` ke `current`, reload service FPM yang benar dan restart worker. Verifikasi `/up`, launch-check dan alur bisnis. Simpan release gagal untuk diagnosis; jangan menghapus shared/storage atau .env. Retention release lama belum diotomatisasi, sehingga operator harus memantau kapasitas dan mempertahankan release rollback yang diperlukan.

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
