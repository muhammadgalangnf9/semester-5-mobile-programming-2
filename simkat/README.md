# Struktur Direktori `lib`

Folder `lib` berisi kode sumber aplikasi Flutter SIPEKAT.

```text
lib/
├── main.dart
├── core/
│   ├── constans/                 # Belum ada file
│   ├── services/
│   │   ├── api_service.dart
│   │   ├── auth_service.dart
│   │   ├── jadwal_ronda_service.dart
│   │   ├── kegiatan_service.dart
│   │   ├── kelola_warga_service.dart
│   │   └── pengumuman_service.dart
│   └── theme/
│       └── app_theme.dart
├── layouts/
│   ├── auth_layout.dart
│   └── main_layout.dart
├── models/                       # Belum ada file
├── providers/                    # Belum ada file
├── screens/
│   ├── auth/
│   │   └── login_screen.dart
│   ├── dashboard/
│   │   └── dashboard_screen.dart
│   ├── jadwal_ronda/
│   │   ├── detail_jadwal_ronda_screen.dart
│   │   └── jadwal_ronda_screen.dart
│   ├── kegiatan/
│   │   ├── detail_kegiatan_screen.dart
│   │   └── kegiatan_screen.dart
│   ├── kelola_warga/
│   │   ├── detail_warga_screen.dart
│   │   ├── edit_warga_screen.dart
│   │   ├── kelola_warga_screen.dart
│   │   └── tambah_warga_screen.dart
│   └── pengumuman/
│       ├── detail_pengumuman_screen.dart
│       └── pengumuman_screen.dart
└── widgets/
    ├── custom_button.dart
    ├── navbar.dart
    └── sidebar.dart
```

## Fungsi Direktori

| Direktori atau file | Fungsi |
| --- | --- |
| `main.dart` | Titik masuk aplikasi dan konfigurasi awal Flutter. |
| `core/constans/` | Tempat konstanta yang digunakan bersama. |
| `core/services/` | Layanan aplikasi, termasuk komunikasi dengan API dan autentikasi. |
| `core/theme/` | Konfigurasi tema dan gaya visual aplikasi. |
| `layouts/` | Kerangka tata letak halaman, seperti layout autentikasi dan layout utama. |
| `models/` | Tempat model data aplikasi. |
| `providers/` | Tempat pengelolaan dan penyediaan state aplikasi. |
| `screens/` | Halaman aplikasi yang dikelompokkan berdasarkan fitur. |
| `screens/auth/` | Halaman autentikasi, termasuk login. |
| `screens/dashboard/` | Halaman dashboard. |
| `screens/jadwal_ronda/` | Halaman daftar dan detail jadwal ronda. |
| `screens/kegiatan/` | Halaman daftar dan detail kegiatan. |
| `screens/kelola_warga/` | Halaman daftar, tambah, edit, dan detail warga. |
| `screens/pengumuman/` | Halaman daftar dan detail pengumuman. |
| `widgets/` | Komponen UI yang dapat digunakan kembali, seperti tombol dan navigasi. |

`models/`, `providers/`, dan `core/constans/` saat ini belum memiliki file sumber.
