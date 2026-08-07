# Rencana Peluncuran Sullamul Ḥifẓ

Dokumen utama untuk menyiapkan **Sullamul Ḥifẓ v1.9.x** sampai layak diluncurkan di TPA Al-Insyirah.

- **Versi kandidat:** v1.9.0 — TPA Launch Complete
- **Website publik:** https://sullamulhifz.or.id
- **Portal aplikasi:** https://app.sullamulhifz.or.id
- **Lembaga implementasi pertama:** TPA Al-Insyirah
- **Data awal:** 88 santri, 88 wali, 4 guru, 6 kelas utama, dan 2 kelompok Tahfizh
- **Prinsip:** Human Before Data, No Ranking Culture, dan perlindungan data anak

## Dokumen kerja

1. [`docs/launch/10-STEPS.md`](docs/launch/10-STEPS.md) — sepuluh langkah sampai peluncuran.
2. [`docs/launch/MASTER-CHECKLIST.md`](docs/launch/MASTER-CHECKLIST.md) — checklist operasional lengkap.
3. [`docs/launch/PILOT-PLAN.md`](docs/launch/PILOT-PLAN.md) — rencana pilot terbatas.
4. [`docs/launch/RELEASE-GATES.md`](docs/launch/RELEASE-GATES.md) — syarat keputusan lanjut atau tunda.
5. [`docs/launch/ROLE-MATRIX.md`](docs/launch/ROLE-MATRIX.md) — pembagian tanggung jawab.
6. [`docs/launch/INCIDENT-PLAN.md`](docs/launch/INCIDENT-PLAN.md) — penanganan gangguan.
7. [`docs/launch/POST-LAUNCH-30-DAYS.md`](docs/launch/POST-LAUNCH-30-DAYS.md) — pemantauan 30 hari pertama.
8. [`docs/launch/STATUS.md`](docs/launch/STATUS.md) — status hidup yang diperbarui setiap hari.

## Aturan penggunaan

- Jangan menandai langkah selesai tanpa bukti.
- Jangan memasukkan password, `APP_KEY`, `DB_URL`, token, data key, nomor WhatsApp, email akun, atau data pribadi anak ke GitHub.
- Jangan menggunakan data nilai, absensi, setoran, atau rapor fiktif untuk 88 santri.
- Gunakan data contoh yang diberi label jelas hanya untuk demonstrasi.
- Setiap perubahan database production harus menggunakan migration additive.
- Dilarang menjalankan `db:wipe`, `migrate:fresh`, `first-install.sh`, dan seeder instalasi awal pada production.

## Status rilis

Rilis v1.9.0 tetap berstatus **kandidat peluncuran** sampai seluruh gate pada `RELEASE-GATES.md` lulus.
