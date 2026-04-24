# 🔒 Dokumentasi Keamanan Aplikasi

Aplikasi ini telah diamankan dari berbagai jenis serangan keamanan. Berikut adalah penjelasan lengkap:

---

## 1. SQL Injection Protection

### ✅ Implementasi:

- **Prepared Statements (Parameterized Queries)**: Semua query menggunakan PDO prepared statements
- **Parameter Binding**: Data user tidak pernah langsung digabungkan dengan SQL query
- **PDO Exception Handling**: Error database tidak di-expose ke user

### 📝 Contoh Kode:

```php
// ✅ BENAR - Menggunakan prepared statement
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
$stmt->execute([$username]);

// ❌ SALAH - Rentan SQL Injection
$query = "SELECT id FROM users WHERE username = '$username'";
```

### 🧪 Cara Test SQL Injection:

```
Username: ' OR '1'='1
Password: anything
```

Aplikasi akan reject input ini karena:

- Validasi format username (alphanumeric, underscore, hyphen saja)
- Username `' OR '1'='1` tidak sesuai format

---

## 2. Buffer Overflow & Input Validation

### ✅ Implementasi:

- **Length Validation**:
  - Username: Max 50 karakter
  - Password: Max 255 karakter, Min 6 karakter
- **Character Whitelist**: Username hanya accept a-z, 0-9, \_, -
- **HTML maxlength Attribute**: Pada form input untuk client-side protection

### 📋 Validasi Rules:

```
Username:
  - Min: 1 karakter
  - Max: 50 karakter
  - Format: [a-zA-Z0-9_-]

Password:
  - Min: 6 karakter
  - Max: 255 karakter
  - Bisa semua karakter (termasuk spesial)
```

---

## 3. XSS (Cross-Site Scripting) Protection

### ✅ Implementasi:

- **Output Encoding**: Semua output di-encode dengan `htmlspecialchars()`
- **Content-Security-Policy Header**: Mencegah inline script execution
- **X-XSS-Protection Header**: Browser-level XSS protection

### 🔧 Security Headers:

```php
header('Content-Security-Policy: default-src \'self\'');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
```

### 🧪 Cara Test XSS:

```
Username: <script>alert('XSS')</script>
Password: test123
```

Output akan di-encode menjadi: `&lt;script&gt;alert('XSS')&lt;/script&gt;`

---

## 4. CSRF (Cross-Site Request Forgery) Protection

### ✅ Implementasi:

- **CSRF Token**: Setiap form memiliki unique token
- **Token Validation**: Server memvalidasi token sebelum memproses request
- **Session-Based Token**: Token di-generate per session

### 📝 Cara Kerja:

1. User membuka login form → Server generate unique CSRF token
2. Token disimpan di session dan form hidden input
3. User submit form → Token dikirim bersama data
4. Server validasi token sebelum proses

---

## 5. Brute Force Protection

### ✅ Implementasi:

- **Failed Attempt Tracking**: Mencatat setiap login gagal per username
- **Attempt Limit**: Max 5 percobaan dalam 15 menit
- **Lockout**: User di-lock selama 15 menit setelah exceed limit
- **Temporary Files**: Data attempt disimpan di temp folder dengan MD5 hash

### 📊 Proses:

1. Login gagal → Increment counter
2. Counter >= 5 → Lock account selama 15 menit
3. Timeout 15 menit → Reset counter

---

## 6. Password Security

### ✅ Implementasi:

- **Password Hashing**: Menggunakan `password_hash()` dengan BCRYPT
- **Cost Factor**: 12 (secure dan slow untuk prevent brute force)
- **Password Verification**: Menggunakan `password_verify()` untuk safe comparison
- **Tidak Reversible**: Password tidak bisa di-decrypt

### 📝 Contoh:

```php
// Hash password saat registrasi
$hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Verify saat login
if (password_verify($password, $hashed)) {
    // Password benar
}
```

---

## 7. Session Security

### ✅ Implementasi:

- **Session Timeout**: 30 menit inaktif
- **Session Regeneration**: ID di-regenerate setelah login (prevent session fixation)
- **HTTPOnly Cookie**: Session cookie tidak bisa diakses JavaScript
- **Secure Cookie**: Cookie hanya dikirim via HTTPS
- **SameSite Policy**: Cookie tidak dikirim cross-origin

### 🔒 Session Config (config.php):

```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);      // HTTPS only
ini_set('session.cookie_samesite', 'Strict');
```

---

## 8. Error Handling & Logging

### ✅ Implementasi:

- **Generic Error Messages**: User tidak lihat detail error
- **Detailed Logging**: Error detail di-log di server
- **No SQL Errors to User**: PDO exception tidak di-expose

### 📋 Log Locations:

- Apache Error Log: `C:\xampp\apache\logs\error.log`
- Failed Attempts: `C:\Windows\Temp\login_attempts_*.txt`

### 📝 Logged Events:

- Successful login
- Failed login attempts
- Registration
- SQL errors (di server saja)
- CSRF token mismatch
- Logout

---

## 9. Data Protection

### ✅ Implementasi:

- **No Plain Text Passwords**: Passwords selalu di-hash
- **No Sensitive Info in Cookies**: Session ID saja yang di-store
- **Prepared Statements**: Data protection di query level
- **Database Sanitization**: Input di-sanitize sebelum database

---

## 10. HTTPS Configuration

### ✅ Implementasi:

- **SSL/TLS Encryption**: Semua data dienkripsi in-transit
- **Self-Signed Certificate** (Development): Untuk testing
- **Production**: Gunakan certificate resmi (Let's Encrypt, Comodo, etc.)

### 📝 Konfigurasi Apache:

- Virtual Host HTTPS: Port 443
- Document Root: Tepat di folder aplikasi
- SSL Certificate: `localhost+2.pem`
- Private Key: `localhost+2-key.pem`

---

## 🧪 Testing Security

### Test SQL Injection:

```
Username: admin' OR '1'='1' --
Username: ' UNION SELECT * FROM users --
```

✅ Semua akan di-reject karena validasi username

### Test XSS:

```
Username: <img src=x onerror=alert('XSS')>
Username: <script>alert(1)</script>
```

✅ Akan di-sanitize dan ditampilkan sebagai text

### Test CSRF:

- Copse form HTML dari aplikasi
- Buka di tab lain dengan form yang sudah di-modify
- Submit form
  ❌ Akan gagal karena CSRF token tidak match

### Test Brute Force:

- Try login 5 kali dengan password salah
- Login keenam akan di-block selama 15 menit

---

## 🔐 Best Practices untuk Production

1. **Update Dependencies**: Selalu update PHP dan libraries terbaru
2. **Use HTTPS**: Jangan pernah kirim password via HTTP
3. **Real SSL Certificate**: Gunakan certificate resmi bukan self-signed
4. **Database Password**: Jangan hardcode, gunakan environment variables
5. **Regular Backups**: Backup database secara berkala
6. **Audit Logs**: Keep detailed logs untuk audit trail
7. **Rate Limiting**: Implement global rate limiting untuk API
8. **Security Headers**: Jangan disable security headers
9. **Keep Logs**: Archive logs untuk compliance dan forensics
10. **Monitor**: Gunakan monitoring tools untuk detect anomalies

---

## 📞 Security Checklist

- [x] SQL Injection Prevention
- [x] XSS Protection
- [x] CSRF Protection
- [x] Buffer Overflow Prevention
- [x] Brute Force Protection
- [x] Password Hashing
- [x] Session Security
- [x] Error Handling
- [x] Logging
- [x] HTTPS Configuration
- [x] Security Headers
- [x] Input Validation
- [x] Output Encoding

---

## 📚 Referensi

- OWASP Top 10: https://owasp.org/www-project-top-ten/
- PHP Security: https://www.php.net/manual/en/security.php
- PDO Prepared Statements: https://www.php.net/manual/en/pdo.prepared-statements.php
- Password Hashing: https://www.php.net/manual/en/function.password-hash.php

---

**Aplikasi ini dilindungi dengan multiple layers of security untuk mencegah serangan umum.**
