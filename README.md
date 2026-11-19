# EcoDrop — Bank Sampah Digital & Poin Sosial
Friedzi Hafizh Al Afghani (123250004) 
Rafi Wiksa Abqary Fitra (123250230)

Aplikasi web layanan publik dan sosial untuk mengelola penyetoran sampah anorganik rumah tangga yang dikonversi menjadi poin bantuan sosial.

Live Demo Online: [http://ecodrop-app.infinityfreeapp.com](http://ecodrop-app.infinityfreeapp.com)

---

## Fitur Utama
- **Multi-Role Authentication:** Pemisahan hak akses dan sesi antara **Warga** dan **Admin / Petugas**.
- **CRUD Setoran Sampah:** Pengajuan jadwal setor (Antar ke Pos / Jemput Kurir), pencatatan kategori & berat sampah, verifikasi status oleh petugas, serta pembatalan transaksi.
- **Kalkulasi Poin Otomatis:** Konversi berat sampah (Kg) ke poin sosial berdasarkan tarif per kategori.
- **Dashboard Statistik & Grafik Interaktif:** Visualisasi komposisi sampah terkelola menggunakan **Chart.js** serta *Leaderboard Top Kontributor Warga*.
- **Pencarian, Filter & Cetak Struk:** Filter transaksi berdasarkan status dan pencetakan bukti setoran siap cetak.

---

## Akun Demo Pengujian
| Peran | Email | Password |
| :--- | :--- | :--- |
| **Admin / Petugas** | `admin@ecodrop.id` | `password` |
| **Warga** | `raka@gmail.com` | `password` |

---

## Teknologi yang Digunakan
- **Backend:** PHP Native
- **Database:** MySQL / MariaDB
- **Frontend:** HTML5, Bootstrap 5, Bootstrap Icons, Chart.js

---

## Cara Menjalankan di Lokal (XAMPP)
1. Salin folder proyek ke dalam direktori `C:\xampp\htdocs\EcoDrop`.
2. Aktifkan modul **Apache** dan **MySQL** di XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin`, buat database bernama `ecodrop`, lalu impor file `database.sql`.
4. Pastikan pengaturan di `config.php` menggunakan kredensial lokal (`host: localhost`, `user: root`, `pass: ''`, `db: ecodrop`).
5. Akses aplikasi melalui `http://localhost/EcoDrop`.