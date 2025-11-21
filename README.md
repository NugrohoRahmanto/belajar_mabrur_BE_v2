# 🎓 Belajar Mabrur BE v2 – Backend Platform & Admin Dashboard

Aplikasi backend modern menggunakan **Laravel 10**, **Filament 3**, dan **API Token**, dirancang untuk:

-   Manajemen konten (Ihram, Sa’i, Tawaf, Tahallul)
-   Sistem autentikasi aman (API Key + Token)
-   Dashboard analitik real-time (User Growth, User Active)
-   Daily Activity Log (DAU/WAU/MAU)
-   Manajemen pengguna (admin, host, user)

---

# 🔧 Requirements

````python
# Minimum system requirements
PHP >= 8.1
Composer
Node.js + NPM / Yarn
MySQL / MariaDB
Git
Web Server (Apache / Nginx / Laravel Sail / Valet)



---

```markdown
# 🚀 Local Installation (Notebook Style)

Ikuti perintah berikut seperti menjalankan "cell" python:

```python
# Clone repository
! git clone https://github.com/AbiyaMakruf/belajar_mabrur_BE_v2.git

# Masuk ke folder project
! cd belajar_mabrur_BE_v2

# Install dependencies backend
! composer install

# Install dependencies frontend (Filament assets)
! npm install
! npm run dev

# Copy environment file
! cp .env.example .env

# Generate key aplikasi
! php artisan key:generate

# Edit file .env
DB_DATABASE=belajar_mabrur
DB_USERNAME=root
DB_PASSWORD=

APP_TIMEZONE=Asia/Jakarta
SESSION_LIFETIME=30
API_KEY="your_api_key"

# Jalankan migrasi dan seeder
! php artisan migrate --seed

# Link storage untuk file media
! php artisan storage:link

# Jalankan server lokal
! php artisan serve




---

```markdown
# 🖥️ Filament Admin Dashboard

```python
# Main dashboard URL
http://localhost:8000/admin


username = "admin"
password = "12345678"

Dashboard menyediakan:
📈 User Growth (7 / 30 / 365 hari)
🔥 User Active (DAU / WAU / MAU)
👥 Total Registered Users
📊 Activity Log
📘 Content Overview


---

```markdown
# 🔑 API Authentication

Semua request API menggunakan header berikut:

```python
headers = {
    "X-API-KEY": "your_api_key",
    "Accept": "application/json"
}

POST /api/login

payload = {
    "username": "admin",
    "password": "12345678"
}

Authorization: Bearer {token}



---

```markdown
# 📂 Project Structure

```python
belajar_mabrur_BE_v2/
│
├── app/
│   ├── Filament/
│   │   ├── Widgets/       # Dashboard widgets (growth, active)
│   │   ├── Pages/         # Dashboard page
│   ├── Http/
│   │   ├── Controllers/API
│   ├── Models/
│   │   ├── UserDailyActivity.py
│
├── database/
│   ├── migrations/
│   ├── seeders/
│   ├── data/              # JSON content (Ihram, Sai, Tawaf, Tahallul)
│
├── routes/
│   ├── api.php
│   ├── web.php

````
