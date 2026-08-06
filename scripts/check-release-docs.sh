#!/bin/sh
set -eu

version=$(grep -Eo 'v[0-9]+\.[0-9]+\.[0-9]+' RELEASE | head -n 1 || true)

if [ -z "$version" ]; then
  echo "ERROR: Version tidak ditemukan pada RELEASE."
  exit 1
fi

bare=${version#v}

required_files="
UPGRADE-V${bare}.md
docs/releases/${version}.md
CHANGELOG.md
public/release.txt
START-HERE.md
docs/CURRENT-STATE.md
docs/ROADMAP.md
docs/RELEASE-STANDARD.md
docs/UPGRADE-STANDARD.md
"

for file in $required_files; do
  if [ ! -f "$file" ]; then
    echo "ERROR: Berkas wajib rilis tidak ditemukan: $file"
    exit 1
  fi
done

if ! grep -q "$version" CHANGELOG.md; then
  echo "ERROR: CHANGELOG.md belum memuat $version"
  exit 1
fi

if ! grep -q "$version" public/release.txt; then
  echo "ERROR: public/release.txt tidak konsisten dengan RELEASE ($version)"
  exit 1
fi

echo "Release documentation lengkap untuk $version."
