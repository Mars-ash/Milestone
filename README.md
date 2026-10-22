# Progress New Model

Fondasi Fase 2 untuk Laravel, PostgreSQL, Blade, Livewire, dan Tailwind. UI yang diterima tetap berada di `app/Livewire/Prototype` dan `resources/views/livewire/prototype`; folder itu dipertahankan untuk mencegah perubahan desain. Adapter simulasi hanya dapat dibuka admin pada environment local/testing dan harus diaktifkan eksplisit. Jalur `/app` tidak membaca fixture.

Fase 2 menyediakan schema, relasi, session auth satu admin, DTO/validasi, policy, kontrak service, serta test. Integrasi UI Project/Progress ke PostgreSQL dimulai Fase 3; upload Fase 4; autosave dan Simpan graph final Fase 5. Skeleton tersebut sengaja menolak dengan HTTP 501, bukan mengembalikan sukses.

## Kebutuhan

- PHP **8.4.1 atau lebih baru dalam major 8**, ekstensi `pdo_pgsql`, `pgsql`, `mbstring`, `dom`, `xml`, `xmlwriter`, `tokenizer`, `fileinfo`, `curl`, `intl`, `zip`.
- Composer 2; Node 24 dan npm; PostgreSQL 18.
- Lock aktual: Laravel 13.35.0, Livewire 4.4.7, Tailwind 4.3.3, Vite 8.3.4, PHPUnit 12.5.39, Playwright 1.62.1. Jangan hapus lockfile untuk mengatasi kegagalan unduhan.

## Setup development

Buat role PostgreSQL `progress_dev` yang hanya memiliki database kosong `progress_new_model_dev`. Buat role `progress_test` yang hanya memiliki database `progress_new_model_test`. Role development tidak perlu mempunyai akses ke database testing, dan keduanya tidak diberi akses produksi. Gunakan administrator PostgreSQL lokal untuk membuat role/database; tetapkan password sendiri. Contoh dari terminal dengan tool PostgreSQL di PATH:

```bash
createuser -h 127.0.0.1 -U postgres --login --pwprompt progress_dev
createdb -h 127.0.0.1 -U postgres --owner=progress_dev progress_new_model_dev
createuser -h 127.0.0.1 -U postgres --login --pwprompt progress_test
createdb -h 127.0.0.1 -U postgres --owner=progress_test progress_new_model_test
```

```bash
composer install --prefer-dist
# Jika unduhan dist HTTP 404: composer install --prefer-source
cp .env.example .env
# Isi DB_PASSWORD dan sesuaikan DB_HOST/DB_PORT/DB_USERNAME development.
php artisan key:generate
php artisan migrate
php artisan admin:create admin@example.test --name="Admin"
npm ci
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Password admin diminta dua kali melalui prompt tersembunyi, minimal 12 karakter. Tidak ada password admin default, registrasi publik, atau admin yang dibuat seeder. Akun kedua ditolak oleh command dan constraint database. Jika database lama sudah mempunyai lebih dari satu user, migration singleton gagal dengan aman; periksa akun secara manual sebelum melanjutkan.

Buka `/login`. `/app` menampilkan kesiapan fondasi dan Keluar. Untuk meninjau UI pengembangan yang sama, set `PROTOTYPE_ENABLED=true` di `.env`, jalankan `php artisan config:clear`, lalu buka `/prototype` setelah login. Semua perubahan pada jalur ini masih simulasi session, bukan data PostgreSQL. Produksi selalu menolak jalur simulasi, sekalipun flag true.

Fixture PostgreSQL fiktif opsional pada database development **kosong**:

```bash
php artisan db:seed --class=DevelopmentSeeder
```

Seeder menolak environment/database selain local/testing yang diizinkan dan menolak overwrite. Isinya tiga project, delapan progress dari fixture UI, satu kasus legacy null, graph anak, dan dokumen metadata-only. Tidak membuat akun atau file palsu yang dianggap tersedia.

## Pengujian

```bash
cp .env.testing.example .env.testing
# Isi koneksi role progress_test, terpisah dari role development.
php artisan key:generate --env=testing
composer validate --strict
composer check-platform-reqs
php artisan test
npm ci
npm run build
php artisan view:cache
```

Test database memakai PostgreSQL; `RefreshDatabase` dapat menghapus isi database uji. Guard menolak database selain `progress_new_model_test` sebelum migration test. Jangan arahkan database ini ke produksi. Browser dan detail bukti tersedia di [docs/testing.md](docs/testing.md) dan [docs/progress/P02.md](docs/progress/P02.md).

Instalasi Composer bersih melalui dist mengalami HTTP 404 pada environment pengerjaan. Fallback `composer install --prefer-source` berhasil memasang 110 paket pada versi lockfile yang sama. Bootstrap dan 116 test dijalankan lagi menggunakan dependency segar tersebut. Batasan yang masih terbuka adalah QA browser; lihat P02. Tidak ada versi paket yang diganti.

Sebelum go live: OP01 identitas admin/pemulihan akun, OP02 server/domain, OP03 batas dan format upload operasional, OP04 backup/retensi, OP05 cutover perlu ditetapkan. Pakai `APP_DEBUG=false`, HTTPS, dan cookie secure pada deployment sesuai Fase 7.
