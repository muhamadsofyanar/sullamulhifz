# Upgrade v2.6.2 — Stage Schedule History

Versi ini merapikan konsep pola pelaksanaan Qur’an Journey agar arahan guru berlaku pada Juz/Marhalah tempat arahan itu dibuat.

## Perubahan utama

- Default tahap baru adalah **Fleksibel**.
- Catatan seperti “Senin minimal 1 ayat” tidak diwariskan otomatis ketika santri berpindah dari Juz 30/Āyah ke Juz 29/Tsalātsiyyah.
- Arahan tahap lama **tidak dihapus**; ia diarsipkan pada riwayat Marhalah.
- Guru dapat mengubah pola pelaksanaan dan arahan pada tahap aktif dari halaman Qur’an Journey santri.
- Riwayat menampilkan Juz, Marhalah, porsi, pola pelaksanaan, periode, dan catatan guru pada tahap sebelumnya.
- Migrasi mengoreksi data legacy v2.6.0 yang sempat membawa catatan tahap lama ke tahap baru.

## Migrasi database

Migrasi baru menambahkan snapshot berikut pada `student_marhalah_histories`:

- `journey_juz_number`
- `stage_code`
- `portion_label`
- `cadence_mode`
- `cadence_notes`

Tidak ada data perjalanan yang dihapus.

## Perilaku perpindahan tahap

Contoh:

1. Juz 30 · Āyah · arahan: “Senin minimal 1 ayat”.
2. Juz 30 selesai hafalan dan guru melanjutkan ke Juz 29.
3. Catatan Juz 30 masuk **Riwayat arahan tahap sebelumnya**.
4. Juz 29 · Tsalātsiyyah dimulai dengan pola **Fleksibel**, tanpa catatan yang diwariskan.
5. Guru dapat mengisi arahan baru, misalnya “Senin dan Kamis, satu porsi 3 baris per setoran”.

## Environment

Tidak ada environment variable baru.
