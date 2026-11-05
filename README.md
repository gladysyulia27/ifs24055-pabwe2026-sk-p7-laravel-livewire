# ifs24055-pabwe2026-sk-p7-laravel-livewire

Aplikasi Stock Barang — Laravel 13, Livewire 4, MySQL, PhpSpreadsheet, OpenAI API.

## Fitur
- Guest: login dan registrasi.
- Member: CRUD stok, pencarian, filter status, pagination, adjust quantity, detail, cover image, deskripsi Markdown, import/export Excel, AI generator dengan preview, profil.
- Admin: daftar member dan stok masing-masing secara read-only.
- Feature-First: Auth, Stock, AI, Profile, Admin; repository/service/policy/middleware.

## Prasyarat lokal
PHP >= 8.3 dengan ekstensi `pdo_mysql`, `mbstring`, `gd`, `zip`, `fileinfo`, Composer 2, Node.js, npm, MySQL/MariaDB.

## Instalasi Windows (PowerShell)
```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
# Buat database pabwe_p7_stock di MySQL, lalu atur DB_* di .env
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
php artisan serve
```
Buka http://127.0.0.1:8000

## Akun demo
- Admin: `admin` / `password`
- Member: `member` / `password`
Ganti password demo sebelum aplikasi dipublikasikan.

## AI Generator
Isi `OPENAI_API_KEY` pada `.env`. Tanpa API key, fitur AI menampilkan pesan konfigurasi dan fitur lain tetap berjalan.

## Pengujian
```bash
composer test
composer audit
```
Catatan: target coverage 100% pada rubrik adalah quality gate yang harus dibuktikan dengan menjalankan suite di environment PHP/Composer yang sesuai. Paket ini menyediakan struktur test dan pipeline; jangan menganggap target 100% sudah tercapai sebelum hasil coverage diverifikasi.

## Deployment
### Vercel (eksperimental)
Vercel bukan platform Laravel native. File `vercel.json` dan `api/index.php` disertakan sebagai titik awal menggunakan runtime komunitas `vercel-php@0.9.0` (PHP 8.5). Runtime ini bukan runtime resmi yang didukung Vercel. Dokumentasi runtime komunitas menyarankan container runtime untuk proyek PHP baru; konfigurasi serverless ini tetap eksperimental. Sebelum production, verifikasi dukungan runtime PHP 8.3, Laravel 13, Livewire request lifecycle, writable cache/session/temp, dan ukuran deployment. Gunakan database MySQL eksternal (misalnya layanan managed), bukan SQLite lokal/serverless. Simpan cover/profile upload di object storage persisten; filesystem lokal Vercel bersifat ephemeral. Set environment variables `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `DB_*`, `SESSION_DRIVER=database` atau layanan session yang persisten, `CACHE_STORE`, `FILESYSTEM_DISK`, `OPENAI_API_KEY`, `FORCE_HTTPS=true`. Jalankan migration melalui proses deploy terpisah.

**Rekomendasi untuk deployment Laravel yang stabil:** gunakan Railway, Render, atau VPS dengan PHP 8.3 + MySQL. Dockerfile tersedia. Untuk Vercel, konfigurasi ini bersifat best-effort dan tidak menjamin deployment berhasil pada runtime pihak ketiga.
