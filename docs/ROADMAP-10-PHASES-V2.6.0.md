# Roadmap 10 Fase — Sullamul Ḥifẓ v2.6.0

Prinsip status: **100% = implementasi lengkap + validasi produksi lulus.** Kehadiran menu/tabel saja tidak cukup.

| Fase | Nama | Posisi saat v2.6.0 |
|---|---|---|
| 1 | Platform Core | Fondasi kuat; release gate final tetap mengikuti dashboard produksi |
| 2 | Full Qur’an Engine | Full corpus/mushaf sudah dibangun; release gate final mengikuti data produksi |
| 3 | Tahfizh Learning Engine | **Workflow guru–wali telah divalidasi end-to-end dalam pengujian proyek**; checklist produksi tetap menjadi catatan audit |
| 4 | Qur’an Journey | **Aktif dikembangkan di v2.6.0; implementasi kandidat, menunggu validasi produksi** |
| 5 | Academy LMS 2.0 | Belum ditutup |
| 6 | Family & Teacher Ecosystem | Belum ditutup |
| 7 | Personal Learning System | Belum ditutup |
| 8 | Character, Talent & Portfolio | Belum ditutup |
| 9 | Insight, Automation & AI Assist | Belum ditutup |
| 10 | Ecosystem / SaaS | Belum ditutup |

## Fase 4 yang sedang dikerjakan

**Qur’an Journey: Marhalah, Milestone, Program Khatam & Warisan Ulama**

- Marhalah: Juz 30 Āyah ≥1 ayat; 29 Tsalātsiyyah 3 baris; 28 Khamsiyyah 5 baris; 27 Niṣfiyyah ½ halaman; 26 Ṣafḥah 1 halaman; Juz 1–25 Ṣafḥatayn 2 halaman.
- Porsi adalah per sesi, bukan kewajiban harian.
- Fondasi 5 Juz: Juz 30→26; mencakup Qāf–An-Nās sebagai manzil Qaf, tanpa menyamakan seluruh Juz 26–30 dengan al-Mufaṣṣal.
- Milestone memisahkan selesai hafalan dan terjaga.
- Khatam 30 Hari: 30 langkah, 1 Juz per langkah.
- Fami Bisyauqin: 7 manzil Fa–Mim–Ya–Ba–Syin–Wau–Qaf.
- Warisan Ulama: Juz, Ḥizb, Rubu‘ al-Ḥizb, Manzil, Rukū‘, Waqaf, Sajdah, Makki/Madani.
- Delapan `quran_rubus` lama dilabel ulang sebagai segment legacy agar tidak disalahartikan sebagai Rubu‘ al-Ḥizb.

Lihat `PHASE-04-QURAN-JOURNEY-V2.6.0.md` untuk definisi dan release gate lengkap.
