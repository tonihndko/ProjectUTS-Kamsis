# ProjectUTS-Kamsis

Web sederhana menggunakan HTML + PHP + SQL untuk input:
- Username
- Password

## Struktur File
- `index.php`: Form input dan proses simpan data
- `config.php`: Konfigurasi koneksi MySQL
- `database.sql`: Script membuat database dan tabel `users`

## Cara Menjalankan
1. Buat database dan tabel dengan menjalankan `database.sql` di MySQL.
2. Sesuaikan koneksi database pada `config.php` jika perlu.
3. Jalankan server PHP dari folder project:

```bash
php -S localhost:8000
```

4. Buka browser ke `http://localhost:8000`.