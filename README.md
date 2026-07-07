# 🔥 BurnoutShield — AI-Powered Employee Burnout Risk Prediction System

> Laravel 10 + Python ML Engine + Filament v3 Admin + Gemini AI + Google Calendar

---

## 📋 Daftar Isi

1. [Persyaratan Sistem](#persyaratan-sistem)
2. [Instalasi Cepat (setup.py)](#instalasi-cepat)
3. [Instalasi Manual Step-by-Step](#instalasi-manual)
4. [Menjalankan Aplikasi](#menjalankan-aplikasi)
5. [Akun Default](#akun-default)
6. [Konfigurasi API Eksternal](#konfigurasi-api-eksternal)
7. [Struktur Proyek](#struktur-proyek)
8. [ML Engine Documentation](#ml-engine)
9. [Troubleshooting](#troubleshooting)

---

## 🖥️ Persyaratan Sistem

| Komponen | Versi Minimum |
|----------|--------------|
| PHP | 8.1+ |
| Composer | 2.x |
| MySQL | 8.0+ |
| Python | 3.10+ |
| Node.js | 18+ (opsional) |
| Laravel | 10.x |

**Rekomendasi:** Gunakan [Laragon](https://laragon.org/) untuk Windows — sudah include PHP 8.1, MySQL, dan Apache.

---

## ⚡ Instalasi Cepat

```bash
# 1. Ekstrak ZIP ke folder pilihan Anda
# 2. Buka terminal di folder burnoutshield/
# 3. Jalankan setup script:
python setup.py
```

Script ini akan otomatis:
- Copy `.env.example` → `.env`
- Install Composer packages
- Generate APP_KEY
- Install Python ML packages
- Jalankan database migrations & seeding
- Build frontend assets

---

## 🛠️ Instalasi Manual

### Step 1 — Setup Database MySQL

Buka **Laragon > Database (phpMyAdmin atau HeidiSQL)** dan buat database baru:

```sql
CREATE DATABASE burnoutshield CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

### Step 2 — Konfigurasi Environment

```bash
# Copy file environment
cp .env.example .env
```

Edit file `.env`, sesuaikan bagian ini:

```env
APP_NAME="BurnoutShield"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=burnoutshield
DB_USERNAME=root
DB_PASSWORD=          # kosong jika pakai Laragon default

ML_ENGINE_URL=http://127.0.0.1:8001
```

---

### Step 3 — Install PHP Dependencies

```bash
composer install
```

---

### Step 4 — Generate App Key

```bash
php artisan key:generate
```

---

### Step 5 — Jalankan Migration & Seeder

```bash
php artisan migrate --seed
```

Output yang diharapkan:
```
✅ Database seeded successfully!
   Admin:    admin@burnoutshield.id / password
   Employee: employee@burnoutshield.id / password
```

---

### Step 6 — Install Python ML Dependencies

```bash
cd ml-engine
pip install -r requirements.txt
```

> **Catatan:** Jika ada error install, model sudah pre-trained dan tersimpan di `ml-engine/models/`. API tetap berjalan dengan scikit-learn saja.

---

### Step 7 — Storage Link

```bash
php artisan storage:link
```

---

### Step 8 — Build Frontend (Opsional)

> Jika tidak install Node.js, app tetap berjalan menggunakan CDN Tailwind.

```bash
npm install
npm run build
```

---

## 🚀 Menjalankan Aplikasi

Anda butuh **2 terminal** yang berjalan bersamaan:

### Terminal 1 — ML Engine (Python)

```bash
cd ml-engine
python api.py
```

Output:
```
🚀 BurnoutShield ML API running on http://0.0.0.0:8001
   GET  /health              — Health check
   GET  /model/info          — Model info
   GET  /model/performance   — All model metrics
   POST /predict             — Predict burnout risk
   POST /retrain             — Trigger retraining
```

### Terminal 2 — Laravel

**Opsi A: php artisan serve**
```bash
php artisan serve
# Buka: http://localhost:8000
```

**Opsi B: Laragon (Recommended)**
1. Buka Laragon
2. Klik kanan icon Laragon → Sites → Add
3. Arahkan Document Root ke folder `burnoutshield/public/`
4. Domain: `burnoutshield.test`
5. Buka: `http://burnoutshield.test`

---

## 🔑 Akun Default

| Role | Email | Password | URL |
|------|-------|----------|-----|
| Admin (Filament) | `admin@burnoutshield.id` | `password` | `/admin` |
| Employee | `employee@burnoutshield.id` | `password` | `/login` |

> **Penting:** Ganti password default setelah setup pertama!

---

## 🔌 Konfigurasi API Eksternal

### Gemini AI (Rekomendasi AI)

1. Buka [Google AI Studio](https://aistudio.google.com/)
2. Buat API Key baru
3. Tambahkan ke `.env`:

```env
GEMINI_API_KEY=AIzaSy...your-key-here
GEMINI_MODEL=gemini-1.5-flash
```

> **Tanpa Gemini API:** Sistem otomatis menggunakan rekomendasi fallback berbasis rule yang sudah built-in.

---

### Google Calendar OAuth2

1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru atau pilih existing
3. Enable **Google Calendar API**
4. Buat **OAuth 2.0 Client ID** (tipe: Web application)
5. Tambahkan Authorized redirect URI:
   ```
   http://localhost:8000/calendar/callback
   ```
   atau (jika pakai Laragon):
   ```
   http://burnoutshield.test/calendar/callback
   ```
6. Tambahkan ke `.env`:

```env
GOOGLE_CLIENT_ID=123456789-xxx.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=GOCSPX-xxx
GOOGLE_REDIRECT_URI=http://localhost:8000/calendar/callback
```

> **Tanpa Google Calendar:** Fitur sync tetap tampil di UI tapi tombol connect tidak aktif.

---

## 📁 Struktur Proyek

```
burnoutshield/
├── app/
│   ├── Filament/              # Filament v3 Admin Panel
│   │   ├── Pages/             # Dashboard admin
│   │   ├── Resources/         # CRUD resources (User, Assessment, dll)
│   │   └── Widgets/           # Stats, Charts, ML Performance
│   ├── Http/Controllers/      # Employee web controllers
│   ├── Models/                # Eloquent models
│   └── Services/              # ML, Gemini, Google Calendar services
├── database/
│   ├── migrations/            # 4 migration files
│   └── seeders/               # Admin + demo employee seeder
├── ml-engine/                 # Python ML Engine
│   ├── models/                # Pre-trained model files (.pkl)
│   ├── api.py                 # FastAPI/HTTP server (port 8001)
│   ├── retrain.py             # Re-training script
│   └── requirements.txt
├── resources/views/
│   ├── auth/                  # Login & Register
│   ├── dashboard/             # Employee dashboard
│   ├── assessments/           # Assessment form (25 questions)
│   ├── predictions/           # Prediction result & recommendations
│   ├── profile/               # Profile & demographic data
│   ├── history/               # Assessment history table
│   ├── calendar/              # Google Calendar integration
│   ├── layouts/               # App layout + auth layout
│   └── components/            # Blade components (slider)
├── routes/
│   └── web.php                # All employee routes
├── .env.example               # Environment template
├── setup.py                   # Quick setup script
└── README.md                  # This file
```

---

## 🤖 ML Engine

### Model yang Digunakan

| Model | Accuracy | F1 Score | ROC AUC |
|-------|----------|----------|---------|
| **Random Forest** ⭐ | ~98.8% | ~98.8% | ~99.8% |
| Gradient Boosting | ~98.5% | ~98.5% | ~99.7% |
| Logistic Regression | ~87.7% | ~87.7% | ~99.2% |
| KNN | ~97.2% | ~97.2% | ~99.5% |

> Model terbaik (Random Forest) dipilih otomatis berdasarkan F1 Score.

### Dataset

- **File:** `tech_mental_health_burnout.csv`
- **Total rows:** 150,000 records
- **Features:** 23 fitur (demografis, work data, wellness, psikologis)
- **Target:** `burnout_level` (Low / Moderate / High)
- **Class distribution:** Low 87.7%, Moderate 12.2%, High 0.04%
- **Balancing method:** Random Oversampling (dipilih karena dataset sangat imbalanced)

### Re-training Model

Jika ingin melatih ulang dengan dataset baru:

```bash
cd ml-engine
python retrain.py path/to/new_dataset.csv
```

Model baru akan otomatis disimpan ke `ml-engine/models/` dan digunakan oleh API.

### API Endpoints (Python, port 8001)

```
GET  /health               — Cek status ML engine
GET  /model/info           — Info model aktif
GET  /model/performance    — Perbandingan semua model
POST /predict              — Prediksi burnout risk
POST /retrain              — Trigger re-training (admin only)
```

---

## 🔧 Troubleshooting

### ❌ "419 Page Expired" / CSRF Error

```bash
php artisan config:clear
php artisan cache:clear
php artisan session:table  # jika pakai session database
```

Pastikan di `.env`:
```env
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=false
```

---

### ❌ "Class not found" / Autoload Error

```bash
composer dump-autoload
php artisan optimize:clear
```

---

### ❌ ML Engine tidak bisa terkoneksi

1. Pastikan terminal ML Engine sudah berjalan: `cd ml-engine && python api.py`
2. Test: `curl http://localhost:8001/health`
3. Jika ML Engine offline, prediksi otomatis menggunakan **fallback rule-based** (tetap bisa digunakan)

---

### ❌ Filament Admin tidak bisa login

Pastikan user memiliki `role = 'admin'`. Cek di database:

```sql
UPDATE users SET role = 'admin' WHERE email = 'admin@burnoutshield.id';
```

---

### ❌ Migration failed

```bash
# Cek koneksi database
php artisan db:show

# Reset migration (WARNING: hapus semua data)
php artisan migrate:fresh --seed
```

---

### ❌ Google Calendar callback error

Pastikan:
1. OAuth redirect URI di Google Console **persis sama** dengan `GOOGLE_REDIRECT_URI` di `.env`
2. Google Calendar API sudah di-enable di Google Cloud Console

---

## 📊 Fitur Lengkap

### Employee
- ✅ Register / Login / Logout
- ✅ Dashboard (trend chart, risk distribution, recommendations)
- ✅ Profile & Demographic Data (satu sumber data)
- ✅ Assessment Form (25 pertanyaan, 4 seksi)
- ✅ AI Prediction Result (gauge, bar chart, feature importance)
- ✅ Assessment History (tabel dengan filter)
- ✅ Google Calendar sync untuk rekomendasi

### Admin (Filament v3)
- ✅ Dashboard dengan stats overview widget
- ✅ Risk distribution chart (doughnut)
- ✅ ML Model performance comparison chart
- ✅ CRUD: Users, Demographic Data, Assessments
- ✅ View: Prediction Results, Recommendations
- ✅ Filter dan search di semua resource

---

## 🔐 Keamanan

- CSRF protection aktif di semua form
- Session-based authentication (file driver)
- Role-based authorization (admin vs employee)
- Password hashing (bcrypt)
- Filament admin di path `/admin` dengan role guard
- ML Engine API key protection

---

*BurnoutShield v1.0 — Developed with Laravel 10, Filament v3, Python scikit-learn*
