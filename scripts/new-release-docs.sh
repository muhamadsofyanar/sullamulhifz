#!/bin/sh
set -eu

version=${1:-}
name=${2:-"Nama Rilis"}

case "$version" in
  v[0-9]*.[0-9]*.[0-9]*) ;;
  *)
    echo "Pemakaian: sh scripts/new-release-docs.sh vX.Y.Z \"Nama Rilis\""
    exit 1
    ;;
esac

upgrade="UPGRADE-${version}.md"
release="docs/releases/${version}.md"

if [ -e "$upgrade" ] || [ -e "$release" ]; then
  echo "ERROR: Dokumen untuk $version sudah ada."
  exit 1
fi

cat > "$upgrade" <<EOT
# Upgrade ke Sullamul Ḥifẓ ${version}

## Ringkasan

${name}

## Versi asal yang didukung

- ...

## Backup

- ...

## Dampak database

- ...

## Environment Variables

- ...

## Langkah upgrade

1. ...

## Verifikasi

- ...

## Rollback

- ...
EOT

cat > "$release" <<EOT
# Sullamul Ḥifẓ ${version} — ${name}

- **Tanggal:** YYYY-MM-DD
- **Tipe:** patch/minor/major
- **Status:** planned

## Tujuan

...

## Perubahan

- ...

## Dampak database

...

## Environment Variables

...

## Upgrade

Lihat \`../../${upgrade}\`.

## Verifikasi

- [ ] Tests lulus
- [ ] Healthcheck 200
- [ ] Smoke test lulus

## Rollback

...
EOT

echo "Dibuat: $upgrade"
echo "Dibuat: $release"
echo "Selanjutnya perbarui CHANGELOG.md, RELEASE, dan public/release.txt."
