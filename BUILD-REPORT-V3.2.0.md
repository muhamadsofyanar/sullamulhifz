# Build Report v3.2.0

## Scope

Kandidat v3.2.0 melengkapi fondasi implementasi Fase 8–9 dan menambah readiness Fase 10 di atas baseline v3.1.1 yang sudah membawa Guided Quran Learning.

## Perubahan inti

- Character/Talent progress non-ranking dan portfolio evidence.
- Reminder Murāja‘ah terjadwal serta idempotent.
- AI Assist draft yang tidak dapat menjadi final tanpa human review dan audit.
- Community moderation audit dan payment ledger opsional.
- Patch morph map Guided Quran production dipertahankan; morph map User ditambahkan untuk database notification.

## QA workspace

- Seluruh file PHP yang ditambah/diubah diparse dengan `php-parser 3.7.0`.
- Runtime lokal tidak menyediakan PHP/Composer/vendor, sehingga full PHPUnit, Artisan, route cache, dan migration execution tidak diklaim telah berjalan lokal.
- Docker build production tetap memiliki package discovery, route registration, Blade compile, dan PHP syntax lint sebelum rolling update.

## Gate produksi

- Migration `002300` harus `Ran`.
- `sullam:verify-guided-quran` dan `sullam:verify-roadmap-foundations-v320` harus lulus.
- Launch Check tetap menjadi sumber kebenaran validasi, bukan keberadaan tabel semata.
