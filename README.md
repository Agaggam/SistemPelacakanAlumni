<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/API-PDDIKTI-0ea5e9?style=for-the-badge&logo=google-cloud&logoColor=white" alt="PDDIKTI API">
  <img src="https://img.shields.io/badge/Status-Development-orange?style=for-the-badge" alt="Status">
</p>

<h1 align="center">🎓 Sistem Pelacakan Alumni (Real-Time Edition)</h1>
<p align="center">
  Platform pelacakan alumni cerdas yang mengintegrasikan data lokal kampus dengan data <strong>PDDIKTI secara Live</strong> melalui API resmi.
</p>

---

## Tentang Proyek

Sistem ini dikembangkan untuk memudahkan pengelola perguruan tinggi dalam memverifikasi status kelulusan alumni secara akurat. Berbeda dengan sistem pencatatan biasa, aplikasi ini menggunakan **Integrasi API Langsung** ke server PDDIKTI Kemendikbudristek untuk menyajikan data yang selalu mutakhir.

### Teknologi Integrasi Data
Proyek ini **bukan** menggunakan library pihak ketiga (wrapper), melainkan menggunakan **API PDDIKTI secara langsung** (`api-pddikti.kemdiktisaintek.go.id`) melalui:
- **Laravel HTTP Client (Guzzle)** untuk request server-side.
- **AJAX (Fetching API)** untuk pemuatan data detail secara asinkron agar performa website tetap ringan dan cepat.

---

## Fitur Unggulan

-  **Real-Time API Search** — Mencari data mahasiswa langsung ke database nasional Kemdikbud.
-  **Async Detail Loader** — Memuat detail "Angkatan" dan "Status Lulus" secara otomatis di latar belakang.
-  **Smart Filtering** — Filter dinamis yang hanya muncul saat hasil pencarian tersedia.
-  **Dual Source Integration** — Menggabungkan hasil pencarian dari database lokal (Manual/CSV) dan PDDIKTI secara transparan.

---

## Hasil Pengujian (Aspek Kualitas & QA)

Laporan pengujian ini disusun berdasarkan kriteria kualitas yang ditetapkan pada perancangan sistem (Daily Project 2).

### 1. Pengujian Fungsional (Functionality)
| No | Fitur | Skenario | Hasil Diharapkan | Status |
|----|-------|----------|------------------|--------|
| 1 | Pencarian Global | Input nama/NIM di halaman publik | Muncul hasil dari DB Lokal & PDDIKTI | ✅ OK |
| 2 | Async Detail | Hover/Load tabel pencarian | Placeholders "Memuat..." berubah jadi data asli | ✅ OK |
| 3 | Filter Dinamis | Mengubah dropdown Angkatan/Prodi | Hasil pencarian ter-filter secara instant | ✅ OK |
| 4 | Admin Dashboard | Pantau data tersimpan | Grafik Chart.js tampil akurat sesuai data lokal | ✅ OK |
| 5 | Demo Modal | Masuk halaman utama pertama kali | Muncul bubble peringatan demo & tombol Oke | ✅ OK |

### 2. Pengujian Kinerja & Efisiensi (Performance)
| No | Parameter | Metode | Hasil | Status |
|----|-----------|--------|-------|--------|
| 1 | Kecepatan Muat | Initial Search | < 2 detik (Karena detail dimuat asinkron) | ✅ OK |
| 2 | Responsivitas UI | AJAX Fetching | Row diupdate satu persatu tanpa freezing | ✅ OK |
| 3 | Penggunaan API | GET Requests | Menggunakan timeout 10s & bypass SSL local | ✅ OK |

### 3. Pengujian UI/UX (Usability)
| No | Elemen | Kriteria Kualitas | Hasil | Status |
|----|--------|-------------------|-------|--------|
| 1 | Demo Modal | Glassmorphism & Animasi | Tampilan premium, blur background, animasi pop | ✅ OK |
| 2 | Status Badges | Color Coding | Lulus (Hijau), Belum Lulus (Abu), Error (Merah) | ✅ OK |
| 3 | Navigation | Sidebar Admin | Mudah berpindah antara Dashboard & Publik | ✅ OK |

### 4. Integritas & Keamanan Data (Security)
| No | Aspek | Skenario | Hasil | Status |
|----|-------|----------|-------|--------|
| 1 | Auth Access | Akses /dashboard tanpa login | Redirect otomatis ke halaman /login | ✅ OK |
| 2 | CSRF Protection | Submit form pencarian/CRUD | Token divalidasi oleh Middleware Laravel | ✅ OK |
| 3 | Data Consistency| Simpan dari PDDIKTI | Data tersimpan permanen di SQLite lokal | ✅ OK |

---

## Cara Menjalankan

1.  **Clone & Install**
    ```bash
    git clone https://github.com/username/SistemPelacakanAlumni.git
    composer install && npm install
    ```
2.  **Environment Setup**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
3.  **Database Strategy** (SQLite)
    ```bash
    # Buat file database kosong
    touch database/database.sqlite
    # Jalankan migrasi
    php artisan migrate --seed
    ```
4.  **Running**
    ```bash
    php artisan serve
    # Dan di terminal lain:
    npm run dev
    ```

---

## 📑 Lisensi & Kredit
- **Dibuat Oleh:** Muhammad Fadhil YZ & Asisten 
- **Tujuan:** Projek Pengembangan Sistem Informasi
- **Status:** Open Source 

---

*Catatan Penting:* Website ini berstatus **Demo**. Data Mahasiswa disimpan menggunakan **Database Lokal (SQLite)** dan belum terintegrasi dengan Database Cloud. Data PDDIKTI yang tampil berasal dari API publik Kemdikbud.
-

## 👨‍💻 Teknologi

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Database | SQLite 3 |
| Frontend | Blade Template, Vanilla CSS, Chart.js |
| Auth | Laravel Session Auth |
| Algorithm | PHP similar_text(), CRC32 seed, weighted scoring |

---

