# UPGRADE V1.4.0

Baca `docs/UPGRADE-v1.4.0.md`. Perintah utama setelah backup dan redeploy:

```bash
cd /var/www/html
sh scripts/upgrade-v1.4.0.sh
sh scripts/smoke-test-v1.4.0.sh
```

Jangan menjalankan `db:wipe`, `migrate:fresh`, atau `first-install.sh`.
