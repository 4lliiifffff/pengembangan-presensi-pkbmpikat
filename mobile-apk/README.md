# mobile-apk/

Folder ini berisi project **Capacitor** untuk membungkus aplikasi web Presensi PKBM Pikat
(`https://absen.pkbmpikat.com`) menjadi file APK Android yang bisa diinstall langsung.

## Cara Kerja

APK ini bertindak sebagai WebView shell yang memuat langsung URL produksi.
Tidak ada bundling kode Laravel - semua tetap berjalan di server.

## Setup & Build

### 1. Install dependensi
```bash
cd mobile-apk
npm install
```

### 2. Tambahkan platform Android
```bash
npx cap add android
```

### 3. Sync project
```bash
npx cap sync android
```

### 4. Buka di Android Studio (untuk build APK)
```bash
npx cap open android
```

Di Android Studio:
- **Testing**: Run ▶ (ke emulator atau device)
- **Build APK debug**: Build → Build Bundle(s) / APK(s) → Build APK(s)
- **Build APK release**: Build → Generate Signed Bundle / APK → APK

### 5. Lokasi file APK hasil build
```
android/app/build/outputs/apk/debug/app-debug.apk
android/app/build/outputs/apk/release/app-release.apk
```

## File Penting

| File | Fungsi |
|---|---|
| `capacitor.config.ts` | Konfigurasi utama (App ID, URL, nama) |
| `android/app/src/main/AndroidManifest.xml` | Permissions kamera, GPS, internet |
| `android/app/src/main/res/` | Icon dan splash screen |

## Catatan

- Perubahan fitur di server `absen.pkbmpikat.com` **otomatis terlihat** di APK tanpa perlu rebuild
- APK hanya perlu di-rebuild jika ada perubahan permission, icon, atau konfigurasi native
- APK bisa didistribusikan langsung via file (tidak perlu Play Store)
