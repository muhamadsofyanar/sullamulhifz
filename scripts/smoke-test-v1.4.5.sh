#!/usr/bin/env sh
set -eu

PUBLIC_URL="${PUBLIC_SITE_URL:-https://sullamulhifz.or.id}"
PORTAL_BASE="${PORTAL_BASE_URL:-https://app.sullamulhifz.or.id}"

printf '%s\n' '=== Smoke test v1.4.5: domain separation ==='

public_status=$(curl -L -sS -o /dev/null -w '%{http_code}' "$PUBLIC_URL/")
portal_status=$(curl -L -sS -o /dev/null -w '%{http_code}' "$PORTAL_BASE/login")
public_login_target=$(curl -sS -o /dev/null -w '%{redirect_url}' "$PUBLIC_URL/login")
portal_public_target=$(curl -sS -o /dev/null -w '%{redirect_url}' "$PORTAL_BASE/tentang")

[ "$public_status" = "200" ] || { echo "GAGAL: website publik HTTP $public_status"; exit 1; }
[ "$portal_status" = "200" ] || { echo "GAGAL: halaman login portal HTTP $portal_status"; exit 1; }
[ "$public_login_target" = "$PORTAL_BASE/login" ] || { echo "GAGAL: /login publik menuju $public_login_target"; exit 1; }
[ "$portal_public_target" = "$PUBLIC_URL/tentang" ] || { echo "GAGAL: /tentang portal menuju $portal_public_target"; exit 1; }

printf '%s\n' 'LULUS: website publik dan portal sudah terpisah.'
