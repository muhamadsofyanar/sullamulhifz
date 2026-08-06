# Fase Terpadu v1.5.0 — Academic Core Complete

Seluruh fase di bawah dikemas sebagai **satu rilis, satu upload, dan satu redeploy**. Fase adalah pengelompokan pekerjaan, bukan perintah redeploy terpisah.

## Fase 0 — Deployment Safety

- Migration additive otomatis saat container baru mulai.
- Tidak menjalankan `db:wipe`, `migrate:fresh`, atau initial seeder.
- Container menunggu database, menjalankan migration, memverifikasi delapan rubu', lalu menjalankan NGINX Unit.
- Jika migration gagal, container baru berhenti dan tidak boleh dianggap berhasil.

## Fase 1 — Profil Lembaga

- Profil TPA Al-Insyirah yang dapat diedit admin.
- Master brand Sullamul Ḥifẓ.
- Tagline “Bukan Sekadar Hafal, Tapi KUAT”.
- Kontak, alamat, penanggung jawab, visi, misi, catatan pendaftaran, dan footer rapor.
- Data yang belum ditetapkan boleh kosong; aplikasi tidak mengarang informasi.

## Fase 2 — Tahun Ajaran dan Semester

- Tahun ajaran tetap menyimpan riwayat.
- Semester aktif: Semester 1, Semester 2, atau Antara Semester.
- Status pendaftaran: ditutup, dibuka, atau internal.
- Struktur kelas, kelompok, jadwal, dan penugasan lama tidak dihapus.

## Fase 3 — Kurikulum Juz 30

- Master delapan rubu' Juz 30.
- Rubu' menjadi milestone, bukan marhalah, ranking, atau label kemampuan.
- Marhalah tetap: Āyah, Tsalātsiyyah, Khamsiyyah, Niṣfiyyah, Ṣafḥah, dan Ṣafḥatayn.

## Fase 4 — Target Hafalan Personal

- Admin dan guru dapat memberi target per santri.
- Target memuat rubu', surat, ayat, marhalah, jenis target, tanggal, batas waktu, dan catatan.
- Status: aktif, berjalan, penguatan, selesai, jeda, atau dibatalkan.
- Setoran dengan surat dan rentang ayat yang sama memperbarui status target terkait.

## Fase 5 — Observasi Metode Belajar

- Guru mencatat metode yang dicoba dan respons nyata santri.
- Kategori: metode belajar, kesiapan, fokus, komunikasi, dan dukungan keluarga.
- STIFIn tetap “Belum Dites” sampai ada hasil resmi.
- Observasi tidak menentukan kelas, marhalah, nilai, atau ranking.

## Fase 6 — Portal Wali dan Profil Santri

- Wali melihat target aktif, setoran, murāja'ah, tahsīn, kehadiran, dan rapor.
- Admin melihat target dan observasi pada profil santri.
- Catatan observasi internal tidak dibuka ke wali secara otomatis.

## Fase 7 — Operasi dan Dokumentasi

- Smoke test v1.5.0.
- Verifikasi Academic Core melalui Artisan.
- Upgrade, rollback, database, dan handover.
- Release marker dan changelog diperbarui.

## Ditunda ke rilis berikutnya

- Academy LMS penuh.
- Pembayaran dan sertifikat.
- Multi-lembaga/multi-cabang penuh.
- Rekomendasi otomatis berbasis STIFIn.
- Forum komunitas bebas.
- Aplikasi Android/iOS native.
