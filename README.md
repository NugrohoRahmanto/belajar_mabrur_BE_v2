
#           BELAJAR MABRUR BE v2          

Belajar Mabrur BE v2 is a modern backend platform built with **Laravel 10**,  
**Filament 3**, and **Token-Based API Authentication**, designed to support  
the Belajar Mabrur mobile & web ecosystem.

It provides:
- Structured Islamic learning content (Ihram, Sa’i, Tawaf, Tahallul)
- A secure authentication system (API Key + Token)
- Real-time analytics dashboard (User Growth & User Active)
- Daily Activity Logging (DAU/WAU/MAU)
- Multi-role user management: admin, host, standard user
- Clean, modern admin UI powered by Filament 3

---

# 🧩 Requirements

## Minimum system requirements
- `` PHP >= 8.1 ``
- `` Composer ``
- `` Node.js + NPM / Yarn ``
- `` MySQL / MariaDB ``
- `` Git ``
- `` Web Server (Apache / Nginx / Laravel Sail / Valet) ``

---

# 🚀 Local Installation

- run `` git clone https://github.com/NugrohoRahmanto/belajar_mabrur_BE_v2.git ``
- run `` cd belajar_mabrur_BE_v2 ``
- run `` composer install ``  
- run `` npm install ``  
- run `` npm run dev ``  
- copy `` .env.example `` to `` .env ``  
- run `` php artisan key:generate ``  
- configure your database in `` .env ``  
- set `` API_KEY="your_api_key" `` inside `` .env ``  
- run `` php artisan migrate --seed ``  
- run `` php artisan storage:link ``  
- run `` php artisan serve ``  
- then visit `` http://localhost:8000 `` or `` http://127.0.0.1:8000 ``  

---

# 🖥️ Admin Panel (Filament Dashboard)

Access the admin panel at:  
`` http://localhost:8000/admin ``

### Default Admin Credentials (from Seeder)
- username: `` admin ``
- password: `` 12345678 ``

Admin dashboard includes:
- 📈 User Growth (7/30/365 days)
- 🔥 Daily Active Users (DAU)
- 📊 Weekly/Monthly Active Users (WAU/MAU)
- 👥 Total Registered Users
- 🕌 Islamic content overview
- 🔧 User management tools

---

# 👥 Dump Users (Seeder)

The system includes default seeded users:

| Role  | Username | Password  |
|-------|----------|-----------|
| Admin | `` admin `` | `` 12345678 `` |
| Host  | `` host ``  | `` 12345678 `` |
| User  | `` user ``  | `` 12345678 `` |

These accounts are created automatically using database seeders.

---

# ©️ License & Copyright

Belajar Mabrur BE v2 is an internal project developed for  
**Belajar Mabrur Educational Platform**.

All rights reserved.  
Unauthorized copying, modification, or distribution of this software  
is strictly prohibited without written permission.

© 2025 Belajar Mabrur — All Rights Reserved.

