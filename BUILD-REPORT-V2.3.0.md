# Build Report — Sullamul Ḥifẓ v2.3.0

Tanggal: 7 Agustus 2026  
Rilis: **v2.3.0 — Integrated Learning Ecosystem**

## Ringkasan
v2.3.0 dibangun di atas v2.2.0 Academy Portal. Fokus utama adalah menjaga seluruh pengalaman Quran Learning di domain Academy, menambah learning path/bookmark/refleksi, memperkaya Parent & Teacher Academy, dan memasang fondasi roadmap 10 fase.

## Pemeriksaan statis
- 283 file PHP: **lolos `php -l`**.
- 4 file JavaScript publik: **lolos `node --check`**.
- 25 shell script: **lolos `bash -n`**.
- `manifest.webmanifest` dan `academy-manifest.webmanifest`: JSON valid.
- CSS v2.3: jumlah kurung kurawal seimbang.
- 167 action controller yang direferensikan route: class/method ditemukan; method Quran yang diwariskan diperiksa sebagai pengecualian yang disengaja.
- Target `view()` controller/route: tidak ada view statis yang hilang.
- Blade baru/diubah untuk Academy, layout, dan admin: pasangan directive utama seimbang.
- Tidak ditemukan `eval()`, `shell_exec()`, wildcard `trustProxies('*')`, private key, atau APP_KEY nyata dalam source.
- Service worker hanya meng-cache daftar aset statis; navigasi dan `/media/` tidak dimasukkan Cache Storage.

## Migration v2.3
Migration baru:
`database/migrations/2026_08_07_001100_integrated_learning_ecosystem_v230.php`

Menambahkan:
- metadata/kategori/learning track Academy;
- learning path dan item;
- bookmark;
- refleksi privat;
- portofolio santri;
- community spaces/posts;
- learning insights;
- integration connections.

Migration bersifat additive dan tidak menghapus tabel pembelajaran lama.

## Perbaikan ketahanan redeploy
Seeder yang dijalankan pada startup diperbaiki agar tidak menimpa pilihan admin:
- feature flag tidak kembali ke default pada restart;
- konten Parent/Teacher Academy lama tidak ditulis ulang;
- status launch check tidak kembali ke pending;
- nama/status cabang dan periode yang sudah diubah admin tidak ditimpa oleh seeder template.

## Academy v2.3
- `academy.sullamulhifz.or.id/audio` memakai player internal Academy.
- Al-Husary dan Al-Minshawi tetap memakai sumber Quran Learning yang sama dengan aplikasi TPA.
- preset dapat dijalankan dan disimpan/bookmark.
- learning path dapat menggabungkan materi Academy + preset Qur’an.
- refleksi pribadi tersedia dan dapat dikaitkan wali hanya dengan anak yang memang terhubung.
- program dapat difilter berdasarkan learning track.
- feature flag Parent Academy, Teacher Academy, STIFIn, Family Learning, Character & Talent, Audio Qur’an, Learning Path, dan refleksi dipisahkan.

## Roadmap 10 fase
Fase 1–6 memiliki implementasi yang dapat dipakai. Fase 7–10 memiliki schema, permission, feature flag, dan scaffold aman; statusnya tetap **fondasi**, bukan fitur final.

## Build-time guard
Dockerfile tetap menjalankan:
- Composer IPv4 + retry;
- `php artisan package:discover`;
- `php artisan route:list`.

Dengan demikian kegagalan bootstrap/registrasi route akan menghentikan image build sebelum Coolify mengganti container produksi.

## Batas pengujian workspace
Folder `vendor/` dan `composer.lock` tidak tersedia pada paket sumber. Karena itu pengujian runtime Laravel penuh tidak dijalankan di workspace ini. Dependency installation dan `route:list` akan divalidasi pada tahap Docker build di Coolify. `composer.lock` tetap direkomendasikan untuk rilis berikutnya agar resolusi dependency menjadi deterministik.
