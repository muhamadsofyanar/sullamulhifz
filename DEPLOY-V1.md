# Sullamul Hifz v1.0.0 — Clean Coolify Deployment

This package is the complete repository source. Do not upload only the hotfix file.

## 1. Replace the GitHub repository contents

Extract this archive. Copy the contents of the `sullamul-hifz-v1.0.0` folder into the repository root, commit, and push to branch `main`.

Recommended commit message:

```text
Release Sullamul Hifz v1.0.0 stable
```

Critical file that must exist in GitHub:

```text
database/migrations/0001_01_03_000000_create_learning_tables.php
```

It must contain:

```text
asgn_submission_recipient_attempt_uq
```

## 2. Redeploy in Coolify

Redeploy the `sullamul-hifz` application. Wait until it is healthy.

Verify the new release in the application terminal:

```sh
cd /var/www/html
cat RELEASE
grep -n "asgn_submission_recipient_attempt_uq" database/migrations/0001_01_03_000000_create_learning_tables.php
```

You may also open:

```text
https://taysriulqurani.id/release.txt
```

It must show `Sullamul Hifz v1.0.0`.

## 3. Perform the clean first installation once

This step deletes all tables in the configured database. Run it only while the application has no real production data.

```sh
cd /var/www/html
CONFIRM_DATABASE_WIPE=YES sh scripts/first-install.sh
```

The final migration status must show all four migrations as `Ran`.

## 4. Login

Use `INITIAL_ADMIN_EMAIL` and `INITIAL_ADMIN_PASSWORD` from Coolify. The administrator will be asked to change the initial password.

## Future deployments

For subsequent source updates, do not wipe the database. Run:

```sh
cd /var/www/html
sh scripts/deploy.sh
```
