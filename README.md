# Sistem Pelacakan Alumni (Alumni Tracking System)

Sistem Pelacakan Alumni adalah sebuah aplikasi berbasis web yang dibangun menggunakan **Laravel 12** untuk mengelola, melacak, dan memverifikasi data lulusan (alumni) perguruan tinggi. Aplikasi ini didesain secara khusus untuk menangani data dalam jumlah besar (ratusan ribu baris) serta mampu memperkaya data kontak alumni secara otomatis melalui sistem *Scraping* dan integrasi API PDDIKTI.

---

## ✨ Fitur Utama

- **Manajemen Data Skala Besar:** Dioptimalkan untuk mengelola lebih dari 140.000+ data alumni dengan cepat.
- **Integrasi PDDIKTI API:** Memvalidasi dan melengkapi status akademik alumni secara langsung dari database nasional PDDIKTI.
- **Auto-Enrichment (Pencarian Sosmed Otomatis):** Fitur scraping cerdas untuk menemukan profil sosial media alumni (LinkedIn, Instagram, Facebook, TikTok) berdasarkan nama dan program studi.
- **Dashboard Analitik & Tracking:** Memantau persebaran alumni, status pekerjaan, dan history pelacakan.
- **Dukungan Multi-Database:** Bisa dijalankan secara lokal (SQLite) maupun Production (MySQL/MariaDB).

---

## 🛠 Teknologi yang Digunakan

- **Backend:** Laravel 12.x, PHP 8.2+
- **Database:** MySQL / MariaDB (Disarankan untuk Production) & SQLite
- **Frontend:** Blade Templating, Vanilla CSS / Tailwind (opsional)
- **Paket Tambahan Utama:** `ilhamrisky/pddiktiapi`

---

## ⚙️ Prasyarat (Prerequisites)

Sebelum menginstal aplikasi ini, pastikan komputer/server kamu sudah terpasang:

- **PHP** minimal versi 8.2
- **Composer** versi terbaru
- **MySQL / MariaDB** (Disarankan menggunakan Laragon atau XAMPP)
- **Node.js & NPM** (untuk build assets)

---

## 🚀 Panduan Instalasi (Local Development)

1. **Clone repositori ini:**

   ```bash
   git clone <url-repo-kamu>
   cd SistemPelacakanAlumni
   ```
2. **Install dependensi PHP via Composer:**

   ```bash
   composer install
   ```
3. **Install dependensi Frontend via NPM:**

   ```bash
   npm install
   npm run build
   ```
4. **Konfigurasi Environment:**
   Salin file `.env.example` menjadi `.env`:

   ```bash
   copy .env.example .env  # Windows
   cp .env.example .env    # Linux/Mac
   ```
5. **Generate Application Key:**

   ```bash
   php artisan key:generate
   ```
6. **Konfigurasi Database:**
   Buka file `.env` dan atur koneksi ke database kamu. Disarankan menggunakan MySQL:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=nama_database_kamu
   DB_USERNAME=root
   DB_PASSWORD=
   ```
7. **Migrasi Database:**
   Jalankan perintah ini untuk membuat struktur tabel di database kamu.

   ```bash
   php artisan migrate
   ```
8. **(Opsional) Import Data Skala Besar:**
   Jika kamu memiliki file `.sql` yang dipisah-pisah (misal per 50.000 data), lakukan import secara manual lewat phpMyAdmin ke database yang sudah kamu atur di atas.
9. **Jalankan Aplikasi:**

   ```bash
   php artisan serve
   ```

   Aplikasi bisa diakses di `http://127.0.0.1:8000`

---

## 🖥 Menjalankan Proses Background (Queue)

Karena aplikasi ini melakukan scraping data (Auto-Enrichment) yang memakan waktu lama, proses tersebut dijalankan di belakang layar menggunakan sistem antrean (*Queue*).

Pastikan kamu membuka terminal baru dan menjalankan perintah ini agar proses pencarian sosial media berjalan:

```bash
php artisan queue:work
```

---

## 📝 Catatan Khusus

- Jika mengalami error terkait kolom `data_source` yang tidak ditemukan, pastikan kamu sudah menjalankan migrasi terbaru atau sudah melakukan import *file struktur tabel yang paling baru*.
- Konfigurasi API Premium untuk keperluan *scraping* (seperti Google Search API) dapat dimasukkan pada file `.env`.

---

*Dibuat untuk memudahkan pelacakan rekam jejak lulusan demi masa depan pendidikan yang lebih baik.*
