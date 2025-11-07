<h1 align="center">🗳️ Pilketos Web App</h1>

<p align="center">
  <i>Sistem Pemilihan Ketua OSIS Digital Berbasis Web</i><br>
  Dibangun menggunakan <b>PHP</b>, <b>HTML</b>, <b>CSS</b>, dan <b>JavaScript</b>
</p>

---

## 📖 Deskripsi

**Pilketos Web App** adalah sistem pemilihan ketua OSIS berbasis web yang dirancang untuk mempermudah proses pemungutan suara secara digital di lingkungan sekolah.  
Website ini memiliki sistem autentikasi multi-role untuk **Admin**, **Guru**, dan **Siswa**.

---

## ⚙️ Fitur Utama

- 🔐 **Multi-role Login System**  
  Role admin, guru, dan siswa dengan sambutan berbeda:  
  - Guru → *Selamat datang, Guru!*  
  - Siswa → *Selamat datang, Siswa!*  

- 📋 **Halaman Voting Interaktif**  
  Menampilkan:
  - Foto kandidat  
  - Visi & misi  
  - Program kerja unggulan  
  - Tombol **Vote**  

- 🧮 **Dashboard Admin**  
  - Ubah password  
  - Tambah akun (guru/siswa)  
  - Tambah/ubah data paslon  
  - Lihat hasil voting dalam bentuk **diagram dan tabel real-time**  

- 💬 **Halaman Edukatif & Motivatif**  
  Menampilkan deskripsi dan pesan untuk mendorong partisipasi siswa dalam demokrasi sekolah.

---

## 🧠 Tujuan Pengembangan

Proyek ini bertujuan untuk:
- Mendigitalisasi proses pemilihan ketua OSIS  
- Meningkatkan transparansi dan efisiensi pemungutan suara  
- Melatih kemampuan pengembangan **web full-stack sederhana**  
- Mengimplementasikan autentikasi, CRUD, dan visualisasi data  

---

## 🛠️ Tech Stack

| Kategori | Teknologi |
|-----------|------------|
| **Frontend** | HTML, CSS, JavaScript |
| **Backend** | PHP (Native) |
| **Database** | MySQL |
| **Hosting** | Sebelumnya dihosting di server sekolah selama periode Pilketos |

---

## 🧩 Struktur Role (Visual Diagram)

```mermaid
flowchart TD
    A[🔐 Login Page] -->|Role: Admin| B[⚙️ Admin Dashboard]
    A -->|Role: Guru| C[👨‍🏫 Guru Dashboard]
    A -->|Role: Siswa| D[👨‍🎓 Siswa Dashboard]

    B --> B1[👥 Kelola Akun<br>(Guru & Siswa)]
    B --> B2[🧾 Kelola Data Paslon]
    B --> B3[📊 Lihat Hasil Voting<br>(Diagram & Tabel)]
    B --> B4[🔑 Ubah Password Admin]

    C --> C1[👋 Sambutan: 'Selamat Datang, Guru!']
    C --> C2[🗳️ Halaman Voting]
    C --> C3[📄 Deskripsi & Motivasi]

    D --> D1[👋 Sambutan: 'Selamat Datang, Siswa!']
    D --> D2[🗳️ Halaman Voting]
    D --> D3[📄 Deskripsi & Motivasi]

---

## 🖼️ Preview (Optional)

> <p align="center">
>   <img src="assets/demo-vote.png" alt="Voting Page Preview" width="600"/>
> </p>

---

## 🧑‍💻 Pengembang

**Sakti Arif Dwi Putra**  
💼 *Backend & Fullstack Developer (Entry Level)*  
📍 Indonesia  

---

<p align="center">
  © 2025 Pilketos Web App — Dibuat untuk digitalisasi pemilihan OSIS sekolah
</p>
