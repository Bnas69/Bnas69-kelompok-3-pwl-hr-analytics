# Human Resource Analytics Dashboard

Project website untuk mata kuliah **Pemrograman Web Lanjut**.

**Universitas Dian Nusantara**  
**Kelompok 3 - Human Resource Analytics**

| Nama | NIM |
|---|---|
| Septian Dwi Saputra | 411232056 |
| Tiara Adisa Marcianda | 411232040 |
| Izatul Janah | 411232019 |

---

## 1. Deskripsi Project

Website ini dibuat untuk menganalisis risiko **attrition karyawan** menggunakan dataset simulasi berisi **15.000 data karyawan**.

Target utama dashboard adalah kolom:

```text
Attrition_Risk_Level
0 = Low Risk
1 = Medium Risk
2 = High Risk
```

Dashboard membantu tim Human Resource melihat:

- Jumlah total karyawan.
- Distribusi risiko Low, Medium, dan High.
- Role pekerjaan dengan jumlah High Risk terbesar.
- Rata-rata jam kerja, jumlah proyek, kepuasan kerja, dan work-life balance.
- Daftar prioritas karyawan dengan risiko keluar tinggi.
- Insight dan rekomendasi HR berdasarkan data.

---

## 2. Fitur Website

1. **Dashboard KPI**
   - Total karyawan.
   - Total High Risk.
   - Persentase High Risk.
   - Rata-rata pendapatan bulanan.
   - Rata-rata jam kerja bulanan.

2. **Visualisasi Data**
   - Doughnut chart distribusi risiko attrition.
   - Bar chart High Risk berdasarkan Job Role.
   - Bar chart rata-rata jam kerja dan jumlah proyek per level risiko.
   - Line chart Job Satisfaction dan Work-Life Balance.
   - Stacked bar chart risiko berdasarkan kelompok usia.

3. **Filter Data**
   - Filter berdasarkan Job Role.
   - Filter berdasarkan level risiko.

4. **Tabel Data Karyawan**
   - Menampilkan seluruh data karyawan dari CSV.
   - Tersedia pencarian, filter job role, filter risiko, dan pagination.

5. **API JSON Laravel**
   - Endpoint data analitik tersedia di:

```text
/api/hr-analytics
```

6. **Upload & Download File**
   - Mendukung gambar, PNG, JPG, PDF, Word, CSV, dan jenis file lain.
   - Mode `Database / tidak lokal`: isi file disimpan langsung di tabel `uploaded_documents`.
   - Mode `Storage lokal`: file disimpan di `storage/app/private/uploads/documents`, metadata tetap masuk database.

7. **Versi Static untuk Netlify**
   - Karena Netlify standar tidak menjalankan backend Laravel/PHP, project ini juga menyediakan versi static dashboard di folder `netlify`.

---

## 3. yang digunakan

Laravel 13: Framework PHP yang digunakan untuk membangun struktur website, route, controller, dan view.
PHP 8.3+: Bahasa pemrograman server-side untuk menjalankan proses aplikasi.
Node.js: Runtime JavaScript untuk mengelola package frontend.
Vite: Tool build untuk mengelola file CSS dan JavaScript.
Chart.js: Library JavaScript untuk menampilkan grafik pada dashboard.
HTML, CSS, JavaScript: Digunakan untuk membuat struktur, tampilan, dan interaksi website.
CSV Dataset: Sumber data utama yang berisi data karyawan untuk dianalisis.

---

## 4. Struktur Folder

```text
hr-analytics-laravel13/
├── app/
│   └── Http/
│       └── Controllers/
│           ├── HrAnalyticsController.php
│           └── UploadedDocumentController.php
├── database/
│   └── migrations/
│       └── 2026_06_02_000000_create_uploaded_documents_table.php
├── public/
│   └── data/
│       └── hr_employee_attrition_data.csv
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   └── app.js
│   └── views/
│       └── dashboard.blade.php
├── routes/
│   └── web.php
├── netlify/
│   ├── index.html
│   ├── assets/
│   │   ├── dashboard.css
│   │   └── static-dashboard.js
│   ├── data/
│   │   └── hr_employee_attrition_data.csv
│   └── public/
│       └── data/
│           └── hr_employee_attrition_data.csv
├── composer.json
├── package.json
├── vite.config.js
├── vite.static.config.js
├── netlify.toml
└── README.md
```

Jalankan migration sebelum mencoba fitur upload/download:

```bash
php artisan migrate
```

---

## 5. Kesimpulan

Website Human Resource Analytics ini dapat membantu tim HR memahami kondisi retensi karyawan secara visual dan terstruktur. Dengan klasifikasi Low, Medium, dan High Risk, perusahaan dapat menentukan strategi intervensi yang lebih tepat, terutama untuk karyawan dengan tingkat risiko keluar tinggi.


## Deploy Online Railway

Lihat file `PANDUAN_RAILWAY_AMAN.md` untuk langkah deploy Laravel ke Railway secara aman tanpa push `.env` dan file sensitif.
