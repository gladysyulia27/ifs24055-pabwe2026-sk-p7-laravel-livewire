# IFS24055 - PABWE 2026 Praktikum 7 (Laravel 13 & Livewire)

Aplikasi Manajemen Stok Barang (Inventory) modern dengan arsitektur **Clean Architecture & Feature-First Approach** sesuai standar industri, dibangun menggunakan **Laravel 13**, **Livewire 4**, basis data **MySQL/MariaDB**, upload/export spreadsheet via **PhpOffice/PhpSpreadsheet**, integrasi kecerdasan buatan (**OpenAI AI Content Generation**), rate limiting (1000 per detik), proteksi kepemilikan middleware, force HTTPS, dan **100% Test Coverage**.

---

## 🚀 Fitur Utama

1. **Clean Architecture & Feature-First Approach**:
   - Modul diorganisir per feature/domain (`App\Features\Auth`, `App\Features\Stock`, `App\Features\Profile`, `App\Features\Admin`, `App\Features\Ai`).
   - Menerapkan Dependency Inversion Principle (DIP) dengan repository interface dan client contracts.
   - Pemisahan lapisan yang jelas antara Actions, Repositories, Services, DTOs, Policies, dan UI (Livewire).

2. **Autentikasi & Autorisasi Role-Based**:
   - **Login Fleksibel**: Pengguna dapat masuk menggunakan **Username** atau **Email**.
   - **Role `admin`**:
     - Mengakses halaman Manajemen Pengguna (`/admin/users`).
     - Melihat daftar seluruh pengguna beserta username, email, dan jumlah stok barang mereka.
     - Melihat detail stok barang pengguna lain dalam mode **Read-Only** (`/admin/users/{user}/stocks`).
     - Admin **tidak dapat** memodifikasi/menghapus stok barang pengguna lain.
   - **Role `member`**:
     - User yang mendaftar melalui form registrasi otomatis memiliki role `member`.
     - Hanya dapat mengelola (CRUD), mengubah jumlah stok (adjust qty), mengunggah cover image, menulis deskripsi Markdown, mengekspor/mengimpor Excel, serta generate AI untuk stok barang miliknya sendiri (`/stocks`).
   - **Middleware & Policy**:
     - `EnsureRole`: Membatasi rute khusus admin dan member.
     - `EnsureStockOwnership`: Memastikan member tidak dapat mengakses atau memodifikasi stok milik orang lain.
     - `StockPolicy`: Memvalidasi otorisasi di tingkat model.

3. **Spreadsheet Excel Import & Export**:
   - Menggunakan library aman dari kerentanan: **`phpoffice/phpspreadsheet`**.
   - **Export**: Menghasilkan file `.xlsx` daftar stok barang milik pengguna.
   - **Import**: Mengunggah file `.xlsx`, `.xls`, `.csv` untuk melakukan bulk insert barang secara aman dengan validasi format data dan status otomatis.

4. **AI Stock Content Generator (OpenAI)**:
   - Dikonfigurasi secara dinamis melalui `.env`:
     - `OPENAI_ENDPOINT` / `OPENAI_BASE_URL`
     - `OPENAI_KEY` / `OPENAI_API_KEY`
     - `OPENAI_MODEL`
   - Kemampuan generate multiple item inventaris secara otomatis berdasarkan prompt/permintaan pengguna.
   - Output diformat dan divalidasi ke dalam DTO `GeneratedStockItem`.

5. **Rate Limiting & Keamanan**:
   - Skrip pembatas akses: **1000 request per detik** via `RateLimiter::for('1000-per-second', ...)`.
   - **Force HTTPS**: Dikonfigurasi melalui `FORCE_HTTPS=true` di `.env` atau otomatis aktif pada environment `production`.

6. **100% Test Coverage**:
   - Seluruh classes, methods, dan baris kode pada direktori `app/` memiliki **100% coverage**.
   - Unit test dan feature test komprehensif menguji seluruh alur autentikasi, model, repository, policy, middleware, service AI (mocked), dan upload/download spreadsheet.

---

## 📁 Struktur Direktori Feature-First

```
app/
├── Features/
│   ├── Admin/
│   │   ├── Livewire/
│   │   │   ├── AdminUserStockList.php
│   │   │   └── UserList.php
│   │   └── Services/
│   │       └── AdminUserService.php
│   ├── Ai/
│   │   ├── Contracts/
│   │   │   └── AiClientInterface.php
│   │   ├── DTO/
│   │   │   └── GeneratedStockItem.php
│   │   └── Services/
│   │       └── OpenAiStockGeneratorService.php
│   ├── Auth/
│   │   ├── Actions/
│   │   │   ├── LoginUserAction.php
│   │   │   └── RegisterUserAction.php
│   │   └── Livewire/
│   │       ├── Login.php
│   │       └── Register.php
│   ├── Profile/
│   │   ├── Livewire/
│   │   │   └── Profile.php
│   │   └── Services/
│   │       └── ProfileService.php
│   └── Stock/
│       ├── Contracts/
│       │   └── StockRepositoryInterface.php
│       ├── Enums/
│       │   └── StockStatus.php
│       ├── Livewire/
│       │   ├── StockDetail.php
│       │   └── StockList.php
│       ├── Models/
│       │   └── Stock.php
│       ├── Policies/
│       │   └── StockPolicy.php
│       ├── Repositories/
│       │   └── StockRepository.php
│       └── Services/
│           ├── StockExcelService.php
│           └── StockService.php
├── Http/
│   ├── Controllers/
│   │   └── Controller.php
│   └── Middleware/
│       ├── EnsureRole.php
│       └── EnsureStockOwnership.php
├── Models/
│   └── User.php
└── Providers/
    └── AppServiceProvider.php
```

---

## 🧪 Pengujian & Verifikasi 100% Test Coverage

Jalankan perintah berikut untuk menjalankan seluruh pengujian dan melihat kalkulasi coverage:

```bash
composer test
```

Atau di terminal Windows:
```cmd
.\test.bat
```
