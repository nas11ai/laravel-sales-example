# Laravel Sales Example App

Aplikasi web untuk pencatatan penjualan berbasis Laravel + Vue 3 + Inertia.js. Dilengkapi dengan manajemen pembayaran bertahap, dashboard analitik, dan sistem role & permission.

---

## Fitur Utama

### Dashboard

- Widget ringkasan: total transaksi, total penjualan (Rp), total qty terjual
- Filter berdasarkan rentang tanggal yang mempengaruhi semua widget dan chart
- Chart penjualan per bulan (12 bulan terakhir)
- Chart qty terjual per item (top 10 dalam periode yang dipilih)

### Penjualan

- List penjualan dengan filter tanggal
- Tambah penjualan — generate kode otomatis, multi-item, snapshot harga saat transaksi
- Detail penjualan — lihat item, status pembayaran, riwayat pembayaran
- Edit penjualan — tidak bisa diedit jika sudah lunas
- Hapus penjualan — tidak bisa dihapus jika sudah lunas
- Status penjualan: **Belum Dibayar** → **Belum Dibayar Sepenuhnya** → **Sudah Dibayar**

### Pembayaran

- List pembayaran dengan filter tanggal
- Tambah pembayaran — generate kode otomatis, mendukung pembayaran bertahap
- Validasi tidak bisa membayar melebihi sisa tagihan
- Edit pembayaran — tidak bisa mengubah penjualan yang dirujuk
- Hapus pembayaran — status penjualan terkait otomatis diperbarui

### Master Data

- **User** — CRUD user dengan assignment role
- **Item** — CRUD item dengan upload gambar, kode unik, harga
- **Role & Permission** — kelola permission per role (via Spatie Laravel Permission)

---

## Tech Stack

**Backend**

- PHP 8.3
- Laravel 13
- Spatie Laravel Permission 7
- Laravel Wayfinder (type-safe routes)
- Inertia.js (server-side adapter)

**Frontend**

- Vue 3 + TypeScript
- Inertia.js (client-side adapter)
- Tailwind CSS v4
- shadcn-vue + Reka UI
- Pinia (state management)
- TanStack Table (DataTable)
- Unovis (chart)
- Vue Sonner (toast notification)
- Lucide Vue Next (icon)

**Testing**

- Pest PHP 4.7
- SQLite in-memory

---

## Instalasi

### Prasyarat

- PHP >= 8.3
- Composer
- Node.js >= 18
- MySQL / SQLite

### Langkah Instalasi

**1. Clone repository**

```bash
git clone https://github.com/nas11ai/laravel-sales-example
cd laravel-sales-example
```

**2. Install dependensi**

```bash
composer setup
```

**3. Konfigurasi database di `.env`**

Untuk MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_sales
DB_USERNAME=root
DB_PASSWORD=
```

Untuk SQLite (default):

```env
DB_CONNECTION=sqlite
```

**4. Jalankan migrasi dan seeder**

```bash
php artisan migrate:fresh --seed
```

**5. Build assets frontend**

```bash
npm run build
```

**6. Jalankan aplikasi**

```bash
composer dev
```

Akses aplikasi di: `http://localhost:8000`

---

## Akun Default

Setelah menjalankan seeder, tersedia dua akun bawaan:

| Role  | Email           | Password |
| ----- | --------------- | -------- |
| Admin | admin@sales.com | password |
| Staff | staff@sales.com | password |

### Perbedaan Hak Akses

| Fitur                 | Admin | Staff |
| --------------------- | ----- | ----- |
| Dashboard             | ✅    | ✅    |
| Lihat Item            | ✅    | ✅    |
| Kelola Item           | ✅    | ❌    |
| Lihat & Kelola User   | ✅    | ❌    |
| Kelola Role           | ✅    | ❌    |
| Lihat Penjualan       | ✅    | ✅    |
| Tambah/Edit Penjualan | ✅    | ✅    |
| Hapus Penjualan       | ✅    | ✅    |
| Lihat Pembayaran      | ✅    | ✅    |
| Tambah Pembayaran     | ✅    | ✅    |
| Edit/Hapus Pembayaran | ✅    | ❌    |

---

## Pengembangan

Jalankan server development dengan hot-reload:

```bash
# Terminal 1 — Laravel
php artisan serve

# Terminal 2 — Vite
npm run dev
```

Atau pakai script bawaan yang menjalankan keduanya sekaligus:

```bash
composer dev
```

Setelah mengubah route Laravel, regenerate Wayfinder type-safe routes:

```bash
php artisan wayfinder:generate
```

---

## Testing

Jalankan semua test:

```bash
php artisan test
```

Jalankan test spesifik:

```bash
php artisan test tests/Feature/SaleTest.php
php artisan test tests/Feature/PartialPaymentTest.php
```

Jalankan dengan coverage (butuh Xdebug atau PCOV):

```bash
php artisan test --coverage
```

### Daftar Test

| File                             | Deskripsi                                      |
| -------------------------------- | ---------------------------------------------- |
| `Unit/SaleServiceTest.php`       | Generate kode, kalkulasi total, snapshot harga |
| `Unit/PaymentServiceTest.php`    | Generate kode, create, update payment          |
| `Unit/PaymentObserverTest.php`   | Auto-update status sale saat payment berubah   |
| `Feature/SaleTest.php`           | CRUD penjualan + authorization                 |
| `Feature/PaymentTest.php`        | CRUD pembayaran + validasi bisnis              |
| `Feature/PartialPaymentTest.php` | Alur pembayaran bertahap                       |
| `Feature/ItemTest.php`           | CRUD item + authorization                      |
| `Feature/UserTest.php`           | CRUD user + role assignment                    |
| `Feature/RoleTest.php`           | Kelola permission per role                     |
| `Feature/DashboardTest.php`      | Widget, chart, filter tanggal                  |
| `Feature/FilterTest.php`         | Filter tanggal di list penjualan & pembayaran  |

---

## Struktur Direktori

```
app/
├── Enums/
│   └── SaleStatus.php          # Status penjualan (UNPAID, PARTIAL, PAID)
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   ├── Master/             # UserController, ItemController, RoleController
│   │   ├── DashboardController.php
│   │   ├── SaleController.php
│   │   └── PaymentController.php
│   ├── Requests/               # Form request + validasi
│   └── Resources/              # API Resource
├── Models/
│   ├── Item.php
│   ├── Sale.php
│   ├── SaleItem.php
│   ├── Payment.php
│   └── User.php
├── Observers/
│   └── PaymentObserver.php     # Auto-update status sale
├── Policies/                   # Authorization per model
└── Services/
    ├── SaleService.php         # Business logic penjualan
    └── PaymentService.php      # Business logic pembayaran

resources/js/
├── components/                 # Shared components
├── composables/                # useRupiah, useBreadcrumbs
├── layouts/                    # AppLayout, AppSidebarLayout
├── pages/                      # Halaman Inertia (Vue SFC)
│   ├── sales/
│   ├── payments/
│   ├── master/
│   └── Dashboard.vue
├── routes/                     # Wayfinder generated routes
└── stores/                     # Pinia stores
```

---

## Lisensi

MIT
