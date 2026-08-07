#!/usr/bin/env sh
set -eu

PUBLIC_URL="${PUBLIC_URL:-https://sullamulhifz.or.id}"
PORTAL_URL="${PORTAL_URL:-https://app.sullamulhifz.or.id}"
API_URL="${API_URL:-https://api.sullamulhifz.or.id}"

check() {
  url="$1"
  expected="$2"
  code="$(curl -L -sS -o /tmp/sullam-smoke-body -w '%{http_code}' "$url")"
  case "$code" in 200|301|302) ;; *) echo "GAGAL $url -> HTTP $code"; exit 1;; esac
  grep -qi "$expected" /tmp/sullam-smoke-body || { echo "GAGAL: teks '$expected' tidak ditemukan pada $url"; exit 1; }
  echo "OK $url"
}

check "$PUBLIC_URL" "Sullamul"
check "$PORTAL_URL/login" "Masuk"
check "$API_URL/api/health" '"status":"ok"'

echo "OK: smoke test v2.1.0 selesai."
