# Mali Oglasi - Laravel Aplikacija

Web aplikacija za male oglase napravljena u Laravel framework-u sa Livewire komponentama.

## Funkcionalnosti

- **Oglasi**: Naslov, opis, cena, stanje robe (polovno/novo), slika, kontakt telefon, lokacija, kategorija
- **Kategorije**: Nestovane kategorije (više nivoa) - Računari > Komponente > Grafičke kartice
- **Role**: Customer i Admin
- **Autentifikacija**: Laravel Breeze
- **Admin Dashboard**: CRUD za Customer-e, Kategorije, Oglase
- **Customer Dashboard**: CRUD nad svojim oglasima
- **Guest stranice**: Home, oglasi po kategoriji, single oglas
- **Search & Filter**: Pretraga po nazivu, opisu, ceni, lokaciji, kategoriji
- **Sortiranje**: Po ceni, datumu
- **Paginacija**: Oglasi su paginirani

## Instalacija

1. **Kloniraj projekat**
```bash
git clone https://github.com/bentilala93/mali-oglasi.git
```

2. **Instaliraj dependencije**
```bash
composer install
npm install
```

3. **Kopiraj .env fajl**
```bash
cp .env.example .env
```

4. **Generiši aplikacioni ključ**
```bash
php artisan key:generate
```

5. **Konfiguriši bazu podataka u .env fajlu**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mali_oglasi
DB_USERNAME=root
DB_PASSWORD=
```

6. **Pokreni migracije i seedere**
```bash
php artisan migrate:fresh --seed
```

7. **Kreiraj storage link**
```bash
php artisan storage:link
```

8. **Pokreni development server**
```bash
php artisan serve
```

9. **U drugom terminalu pokreni Vite**
```bash
npm run dev
```

## Podrazumevani korisnici

Nakon pokretanja seedera, kreiraju se sledeći korisnici:

### Admin korisnik:
- Email: admin@gmail.com
- Password: admin12

### Customer korisnici:
- Email: user1@gmail.com
- Password: user12
- Email: user23@gmail.com
- Password: user23

## Struktura projekta

- **Models**: Ad, Category, User
- **Livewire Components**: home, single-ad, admin-dashboard, customer-dashboard, admin-oglasi, admin-kategorije, admin-korisnici
- **Routes**: web.php sa dinamičkim rutama za kategorije
- **Migrations**: Jedna migracija sa svim tabelama
- **Seeders**: RoleSeeder, CategorySeeder, AdSeeder

## Tehnologije

- **Laravel 12**
- **Livewire 3.4**
- **Volt Function API 1.7.0**
- **Breeze 2.3**
- **Spatie Permission 6.20**
- **Tailwind CSS**
- **MySQL/PostgreSQL**

## Napomene

- Svi oglasi se automatski odobravaju u seed-u
- Soft delete je omogućen za oglase i korisnike
- Email verifikacija je omogućena