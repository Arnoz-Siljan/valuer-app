# Valuer.si

Enostavna spletna aplikacija za upravljanje z nepremičninskimi cenitvami, zgrajena z PHP, MySQL in vanilla JS.

## Zahteve

- PHP 8.x
- MySQL 5.7+ ali MariaDB 10.4+
- Spletni strežnik (XAMPP / Laragon / MAMP)

## Namestitev

### 1. Klonirajte/kopirajte projekt

Postavite mapo `valuer-app/` v vaš spletni koren (npr. `htdocs/` pri XAMPP-u ali `www/` pri Laragon-u).

### 2. Ustvarite bazo podatkov

Uvozite SQL skripto v MySQL:

```bash
mysql -u root -p < database.sql
```

Ali jo uvozite ročno prek **phpMyAdmin** → `Import` → izberite `database.sql`.

### 3. Nastavite dostop do baze

Kopirajte `config/db.php` in uredite poverilnice:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'valuer_db');
define('DB_USER', 'root');
define('DB_PASS', 'vaše_geslo');
```

> `config/db.php` je v `.gitignore` — ne bo kommitiran.

### 4. Zaženite aplikacijo

Odprite brskalnik in pojdite na:

```
http://localhost/valuer-app/public/
```

## Struktura projekta

```
valuer-app/
├── config/db.php              # Konfiguracija PDO
├── includes/
│   ├── auth.php               # Varovanje sej, flash sporočila
│   ├── header.php / footer.php
│   └── valuation_fields.php   # Skupna polja obrazca
├── public/
│   ├── index.php              # Preusmeritev
│   ├── register.php / login.php / logout.php
│   ├── dashboard.php          # Seznam cenitiev
│   ├── valuation_add.php / valuation_edit.php
│   └── api/valuation_delete.php  # AJAX brisanje
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── database.sql
└── README.md
```

## Varnost

- Gesla shranjena z `password_hash()` (bcrypt)
- Vse poizvedbe s PDO in pripravljenimi stavki (brez SQL injection)
- Izhod zavarovan z `htmlspecialchars()` (brez XSS)
- Seje za preverjanje pristnosti; vse strani z cenitvami so zaščitene
- Strežniška validacija poleg odjemalske
