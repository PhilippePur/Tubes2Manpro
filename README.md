# Tubes2Manpro
Tugas Besar Manajemen Proyek 2 
Michael Philippe Purnama - 6182301019
Gregorius Jason Maresi - 6182301055
Michael Gunawan - 6182301099
# MIBD Tubes PHP

Proyek ini adalah aplikasi Youtube sederhana yang dibangun dengan PHP dan Microsoft SQL Server. Proyek ini merupakan bagian dari Tugas Besar Mata Kuliah Manajemen Informasi dan Basis Data.

---

## 📦 Tools yang Harus Diinstall

Sebelum menjalankan project ini, pastikan kamu sudah menginstal tools berikut:

1. [XAMPP](https://www.apachefriends.org/index.html)  
   Untuk menjalankan server PHP dan Apache (versi PHP 8.2 direkomendasikan).

2. [Microsoft SQL Server (Developer Edition)](https://www.microsoft.com/en-us/sql-server/sql-server-downloads)  
   Untuk menyimpan dan mengelola database.

3. [SQL Server Management Studio (SSMS)](https://learn.microsoft.com/en-us/sql/ssms/download-sql-server-management-studio-ssms)  
   Untuk mengakses, membuat, dan mengelola database secara visual.

4. [Microsoft Drivers for PHP for SQL Server](https://learn.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server)
   Dibutuhkan untuk menghubungkan PHP ke SQL Server.  Simpan pada C:\xampp\php\ext

---

## Catatan 
Aktifkan ekstensi SQLSRV di PHP:
Buka file php.ini di direktori XAMPP, misalnya C:\xampp\php\php.ini.
Tambahkan baris ini 
extension=php_sqlsrv_82_ts_x64.dll //sesuaikan dengan versi XAMPP dan perangkat anda
extension=php_pdo_sqlsrv_82_ts_x64.dll //sesuaikan dengan versi XAMPP dan perangkat anda
Simpan dan restart Apache di XAMPP.

## Cara Menjalankan Project
1. Download & Extract
Download repository ini dan extract folder-nya ke direktori:
"C:\xampp\htdocs\MIBDTubesPHP"
2. Connect ke SQL Server
Pastikan anda memiliki SQL Server yang aktif di perangkat anda.
3. Import Database
Jalankan query dari file `LoginDatabaseSQL.sql` menggunakan SQL Server Management Studio (SSMS) atau tools lain untuk membuat database dan tabel yang dibutuhkan.
4. Ubah Koneksi di `testsql.php`
Buka file `testsql.php`, lalu sesuaikan bagian berikut dengan server SQL anda:
```php
$serverName = "localhost\\SQLEXPRESS"; // Ganti sesuai server pada device anda
$connectionOptions = [
    "Database" => "Tubes", // Nama database
    "TrustServerCertificate" => true
];
```
5. Jalankan Apache
Buka XAMPP dan aktifkan service Apache.
6. Akses Aplikasi
Buka browser dan kunjungi:
http://localhost/MIBDTubesPHP/Register.php



