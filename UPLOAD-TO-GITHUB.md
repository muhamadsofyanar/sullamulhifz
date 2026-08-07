# Cara Mengunggah Paket Rencana ke GitHub

Paket ini hanya menambahkan dokumentasi dan template issue. Tidak mengubah aplikasi, database, migration, route, atau Dockerfile.

## Langkah

1. Ekstrak ZIP.
2. Buka folder hasil ekstrak.
3. Salin seluruh isi folder ke root repository Sullamul Hifz.
4. Saat Windows menanyakan konflik, pilih **Replace files in the destination** hanya bila file yang sama memang berasal dari paket ini.
5. Periksa `git status`.
6. Pastikan tidak ada file rahasia atau data pribadi.
7. Commit dan push:

```bash
git add LAUNCH-PLAN.md UPLOAD-TO-GITHUB.md docs/launch .github/ISSUE_TEMPLATE
git commit -m "docs: add TPA launch plan and pilot checklist"
git push origin main
```

## Dampak deployment

Coolify mungkin melakukan redeploy otomatis karena commit baru, tetapi tidak ada perubahan runtime. Tidak perlu menjalankan migration, seeder, atau Terminal.
