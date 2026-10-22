# Pengujian Fase 2

## Environment dan proteksi data

Database pengujian wajib PostgreSQL dengan nama tepat `progress_new_model_test`. PHPUnit memaksa DB_CONNECTION=pgsql, DB_DATABASE tersebut, dan mengosongkan DB_URL. `tests/TestCase::createApplication` memeriksa config setelah bootstrap, sebelum trait RefreshDatabase menjalankan migration. Tetap gunakan role terbatas yang tidak mempunyai akses produksi; nama database saja bukan pengganti isolasi role/server.

Gunakan `.env.testing.example` untuk membuat `.env.testing`; isi koneksi dan generate APP_KEY. File actual `.env`/`.env.testing` tidak disertakan source. Database development terpisah: progress_new_model_dev. Seeder development menolak overwrite, tidak membuat user, dan tidak menyentuh database lain.

## Perintah quality gate

```bash
composer install --prefer-dist
composer validate --strict
composer check-platform-reqs
cp .env.testing.example .env.testing
# Sesuaikan host/port/user/password test.
php artisan key:generate --env=testing
php artisan test
npm ci
npm run build
php artisan view:cache
```

Untuk diagnosis constraint dan auth saja:

```bash
php artisan test --filter='SchemaTest|PayloadTest|ServiceContractTest|AdminAccessTest'
php artisan test --filter='UiRecheckRegressionTest|PrototypeUiParityRegressionTest'
```

Migration up/down dapat diperiksa dengan `php artisan migrate`, `php artisan migrate:rollback`, lalu `php artisan migrate` pada **database uji kosong** menggunakan `--env=testing`. Jangan menjalankan rollback/fresh pada development yang berisi data atau pada produksi. Suite RefreshDatabase hanya diperuntukkan database testing terisolasi.

## Cakupan nyata

- Unik JO/Part, nol depan, raw `1,5` versus `1.5`, DATE null legacy, Activity/status/revision CHECK, FK dan RESTRICT.
- Dua problem dengan dua corrective action masing-masing; Next Action mandiri dan feedback berulang; master terkini lewat relasi.
- Satu admin, satu active edit draft, applied target, owner attachment tunggal, metadata-only tanpa download.
- Draft parsial versus final tiga field wajib; tipe/tanggal/field asing; ID anak silang; client ID stabil; referensi attachment lintas draft/baris ditolak.
- Login gagal/sukses/logout/throttle; command admin hash dan penolakan akun kedua; URL anonim dan boot Livewire; policy draft/file/Closed parent dan anak.
- Rollback graph contoh di PostgreSQL; Close/Reopen revision, pelestarian anak/draft; replay applied draft dan skeleton yang tidak mengembalikan sukses palsu.
- Test Fase 1 tetap dipertahankan dan memakai session identity admin fiktif tanpa memasukkan dummy ke database operasional.

Coverage tersebut **bukan** bukti implementasi Fase 3–5 sudah selesai: write graph final, sequencing autosave, konflik editor dua tab, staging cleanup dan upload operasional masih kontrak/skeleton.

## Browser terfokus

Playwright 1.62.1 menjadi satu tool browser proyek (`npm run test:browser`). Siapkan browser dan admin pada development terisolasi; tidak menggunakan akun produksi.

```bash
npx playwright install chromium
# Jalankan aplikasi development; PROTOTYPE_ENABLED=true, PROTOTYPE_STORAGE=session.
# Isi BROWSER_TEST_EMAIL dan BROWSER_TEST_PASSWORD sebagai environment lokal, jangan commit.
npm run test:browser
```

`BROWSER_BASE_URL` default http://127.0.0.1:8000. `CHROMIUM_PATH` opsional untuk browser lokal yang kompatibel. File `playwright.config.js` memakai satu worker dan menghasilkan test-results yang tidak masuk source ZIP. Test tidak membuat akun; pakai admin dari command lokal.

Delapan skenario disediakan: auth, lima viewport (1366, 1024, 911, 768, 390), navigasi tanpa ghost draft + pemulihan draft edit setelah reload, serta pengosongan tanggal dan Close/Reopen. Dataset preview tetap 3 project/8 progress/2 draft awal, 5 Open/3 Closed; jangan mengganti count agar menyerupai screenshot referensi lama.

Pada sesi ini Chrome 155.0.8059.79 tersedia tetapi gagal diluncurkan karena environment menolak Unix socket (`socket() failed: Operation not permitted`). Delapan test berhenti **sebelum interaksi halaman**, sehingga tidak ada klaim kelulusan browser/visual. Unduhan Chromium bawaan juga tidak menghasilkan archive valid. Jalankan ulang pada environment dengan browser yang dapat berjalan; tidak melonggarkan kontrol environment.

R03/R04 (offline, promise lampiran gagal, dua navigasi cepat, acknowledgement input baru, sesi berakhir) masih membutuhkan QA browser di environment tersebut. Source guard dari upload dipertahankan. Draft baru belum menambahkan UUID ke URL setelah acknowledgement; pemulihan melalui daftar draft tersedia pada adapter, sedangkan reload langsung URL form baru perlu dirapikan pada integrasi Fase 5. Ini dicatat sebagai sisa integrasi, bukan dianggap lulus oleh test server.

## Reproduksi instalasi dependency

Instalasi ke direktori vendor kosong dengan `--prefer-dist --no-scripts` mengalami HTTP 404 (contoh symfony/console v8.1.8). Fallback `--prefer-source --no-scripts` berhasil memasang 110 paket pada versi/revisi lockfile yang sama. Dependency segar ditempatkan pada path vendor standar, autoload/bootstrap dibuat ulang, platform requirements diperiksa, dan 116 test (708 assertions) lulus kembali. Source checkout memuat class development bawaan paket; `exclude-from-classmap` menghindari duplikasi namespace App dari Pint dan Local adapter Flysystem. Jangan mengganti versi dependency untuk menutupi masalah jaringan. CI ditambahkan di `.github/workflows/quality.yml` tetapi belum dijalankan pada GitHub.

Runtime pengujian sesi ini: PHP 8.4.26, Composer 2.10.3, PostgreSQL 18.6, Node 24.19.0, npm 11.9.0. Container hanya menyediakan satu UID root; server PostgreSQL uji dijalankan pada loopback dengan adapter identitas proses lokal, tanpa perubahan kode engine/SQL, dan socket Unix dimatikan. Adapter, cluster, kredensial dan binary runtime tidak masuk source. Pada mesin pengembang normal gunakan PostgreSQL sebagai user sistem postgres dan role database terbatas; tidak perlu adapter ini.
