# Panduan Deploy Aman ke Railway

File ini dibuat agar project Laravel HR Analytics tampil sama antara localhost dan Railway.

## Penyebab tampilan Railway berbeda

Railway menjalankan project dalam mode production. File CSS/JS dari Vite harus dibuild menjadi `public/build`.
Jika build frontend tidak berjalan, halaman Laravel tetap muncul tetapi tampilannya menjadi HTML polos tanpa styling.

Project ini sudah ditambahkan:

- `railway.toml` untuk memaksa Railway menjalankan `composer install`, `npm ci`, dan `npm run build`.
- `.railwayignore` agar file sensitif/cache tidak ikut terkirim.
- `.gitignore` yang lebih aman.
- `package.json` start script yang aman untuk Laravel.

## Variables Railway

Masuk ke Railway:

Service Laravel -> Variables -> Raw Editor

Isi minimal:

```env
APP_NAME="HR Analytics Dashboard"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}
APP_KEY=base64:ISI_APP_KEY_ASLI_DARI_php_artisan_key_generate_show
APP_CIPHER=AES-256-CBC

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

CACHE_STORE=file
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
```

Ambil APP_KEY dari local:

```bash
php artisan key:generate --show
```

Copy hasilnya lengkap, termasuk `base64:`.

## Setelah push ke GitHub

```bash
git add .
git commit -m "fix railway production deploy"
git push
```

Railway akan auto redeploy.

## Cek online

Buka domain Railway:

- `/login`
- `/`
- `/api/hr-analytics`

## Catatan database

Dashboard analitik HR pada project ini membaca dataset dari:

`public/data/hr_employee_attrition_data.csv`

MySQL Railway digunakan untuk fitur upload/download dokumen melalui tabel:

`uploaded_documents`

Agar upload/download aktif, migration dijalankan otomatis oleh `railway.toml`:

```bash
php artisan migrate --force
```

## Keamanan

Jangan push file berikut:

- `.env`
- file `.sql`
- `storage/app/private`
- `storage/framework/sessions`
- `storage/framework/views`
- `vendor`
- `node_modules`

Dataset di `public/data/hr_employee_attrition_data.csv` bersifat publik karena berada di folder public. Jika dataset asli/sensitif, jangan taruh di folder public.
