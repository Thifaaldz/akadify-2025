# Sistem Verifikasi Ijazah Otomatis

## Deskripsi Sistem
Sistem ini dirancang untuk melakukan verifikasi ijazah secara otomatis menggunakan kombinasi **Laravel**, **Filament Admin**, **n8n**, **OCR**, dan **WhatsApp API (Waha)**. Tujuannya adalah mempermudah proses validasi ijazah siswa, memberikan notifikasi real-time, dan memungkinkan admin mengelola dokumen dengan mudah.

Teknologi utama yang digunakan:
- **Laravel + Livewire**: Untuk upload ijazah dan interaksi user.
- **n8n**: Workflow automation untuk memproses OCR, notifikasi, dan integrasi.
- **Filament Admin**: Panel untuk admin memonitor status verifikasi.
- **Waha (WhatsApp HTTP API)**: Mengirim notifikasi ke siswa secara otomatis.
- **OCR**: Untuk membaca data ijazah seperti nama, NISN, dan tanggal kelulusan.

---

## Alur Kerja Sistem

### 1. Upload Ijazah
1. Siswa melakukan upload ijazah melalui form di **Laravel Livewire**.
2. File ijazah disimpan di storage Laravel (`src/storage/app/ijazah`).
3. Setelah upload, file akan di-trigger ke workflow **n8n**.

---

### 2. Notifikasi Upload Berhasil
1. **n8n** menerima trigger dari Laravel.
2. Node **Waha** akan mengirim notifikasi WhatsApp ke siswa:
   > "Upload berhasil, silakan tunggu proses OCR berlangsung."

---

### 3. Proses OCR
1. File ijazah yang masuk akan diproses oleh **OCR** melalui workflow n8n.
2. Hasil OCR akan mem-parsing informasi penting:
   - Nama siswa
   - NISN
   - Tanggal kelulusan
   - Lain-lain sesuai kebutuhan
3. Jika OCR gagal atau data tidak sesuai:
   - Admin dapat melihat dokumen di **Filament Admin**.
   - Admin menekan tombol **Proses OCR** untuk mencoba ulang.

---

### 4. Verifikasi Ijazah
1. Setelah OCR berhasil, sistem membandingkan data hasil OCR dengan data **modul siswa** di Filament Admin.
2. Ada dua kemungkinan:
   - **Berhasil**:
     - Status siswa berubah menjadi **Terverifikasi**.
     - WhatsApp dikirim via **Waha**:
       > "Verifikasi berhasil. Data ijazah Anda telah valid."
   - **Gagal**:
     - Status siswa tetap **Belum Terverifikasi**.
     - WhatsApp dikirim via **Waha** dengan alasan:
       > "Verifikasi gagal, harap upload ulang. Kesalahan: [Nama berbeda / NISN tidak terdeteksi / Format dokumen tidak sesuai]."

---

### 5. Manajemen Dokumen di Admin
- Semua dokumen upload disimpan di Filament Admin.
- Dokumen dapat dilihat, diproses OCR ulang, atau diverifikasi manual jika terjadi error.
- Admin dapat men-trigger OCR sekali klik untuk dokumen yang gagal.

---

## Struktur Folder (Opsional)
/src
└─ /storage/app/ijazah -> Tempat upload file ijazah dari Laravel
/n8n
└─ /data -> Data n8n workflow dan konfigurasi
/ocr
└─ OCR processing scripts
/waha
└─ WhatsApp sessions & media


---

## Catatan Penting
- Pastikan **UID/GID** file ijazah sesuai dengan user **n8n (node:1000:1000)** agar workflow dapat membaca file tanpa masalah EACCES.
- Workflow n8n menggunakan node:
  - **Read/Write File** untuk akses file ijazah
  - **OCR Node / Custom OCR Service** untuk membaca isi dokumen
  - **HTTP Request / Waha Node** untuk mengirim notifikasi
- Laravel harus menyimpan file dengan permission yang benar agar n8n dapat memproses file.

---

## Referensi Workflow
1. Upload ijazah → trigger n8n  
2. Notifikasi Waha → tunggu OCR  
3. OCR proses → update status di Filament  
4. Notifikasi final berhasil/gagal → update status siswa  

---

## To-Do / Future Improvements
- Otomatisasi **chown** untuk file baru dari Laravel agar n8n tidak forbidden.
- Logging OCR hasil parsing dan kesalahan untuk tracking.
- Penanganan batch verifikasi untuk banyak siswa sekaligus.
