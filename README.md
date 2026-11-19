# EcoDrop — Bank Sampah Digital & Poin Sosial

Aplikasi web layanan publik berbasis PHP Native dan MySQL untuk mendigitalisasi penyetoran sampah terpilah masyarakat serta mengonversinya secara transparan menjadi poin bantuan sosial.

## Fitur Utama
- **Autentikasi & Multi-Role Session:** Registrasi warga, login, logout, serta pemisahan hak akses antara **Admin/Petugas** dan **Warga**.
- **Manajemen Setoran (CRUD):** Pengajuan setor sampah, riwayat transaksi, koreksi timbangan & verifikasi status oleh petugas, serta penghapusan data.
- **Kalkulator Poin Real-Time:** Estimasi perolehan poin dan nilai konversi rupiah otomatis berdasarkan jenis dan berat sampah.
- **Leaderboard Kontributor:** Peringkat warga dengan kontribusi pengelolaan sampah tertinggi.
- **Pencarian & Multi-Filter:** Filter transaksi berdasarkan kata kunci, kategori sampah, dan status verifikasi.
- **Cetak Struk Digital:** Bukti transaksi resmi siap cetak maupun simpan ke PDF.

## Akun Pengujian (Demo)
| Role | Email | Kata Sandi |
| :--- | :--- | :--- |
| **Admin / Petugas** | `admin@ecodrop.id` | `password` |
| **Warga** | `raka@gmail.com` | `password` |

## Cara Menjalankan di Localhost
1. Salin folder proyek ke dalam direktori `htdocs` (XAMPP) atau `www` (Laragon).
2. Buka `http://localhost/phpmyadmin` dan import file `database.sql`.
3. Sesuaikan konfigurasi koneksi database pada file `config.php` jika diperlukan.
4. Akses aplikasi melalui browser di `http://localhost/ecodrop`.