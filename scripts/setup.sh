#!/usr/bin/env bash
# =============================================================================
# COG-TOR System: install or update on a PC.
#
# Run from the project folder in Git Bash (Windows) or a terminal (macOS/Linux):
#
#   bash scripts/setup.sh            Install or update. Keeps all data.
#   bash scripts/setup.sh --fresh    Wipe the database and load the default
#                                    accounts and sample academic setup.
#                                    Asks for confirmation first.
#
# Safe to run again after every "git pull".
# =============================================================================
set -euo pipefail
cd "$(dirname "$0")/.."

FRESH=0
case "${1:-}" in
  "") ;;
  --fresh) FRESH=1 ;;
  *) echo "Usage: bash scripts/setup.sh [--fresh]"; exit 1 ;;
esac

step() { printf '\n== %s\n' "$1"; }
ok()   { printf '   ok  %s\n' "$1"; }
fail() { printf '\nERROR: %s\n' "$1" >&2; exit 1; }

[ -f artisan ] && [ -f composer.json ] || fail "Run this from the cog-tor-system folder."

# -----------------------------------------------------------------------------
step "1/7 Checking tools"
command -v php >/dev/null      || fail "PHP not found. Install XAMPP (PHP 8.4) and add C:/xampp/php to PATH."
command -v composer >/dev/null || fail "Composer not found. Install it from getcomposer.org."
command -v npm >/dev/null      || fail "Node.js/npm not found. Install Node.js 18 or newer."

php -r 'exit(version_compare(PHP_VERSION, "8.4.0", ">=") ? 0 : 1);' \
  || fail "PHP 8.4 or newer is required (found $(php -r 'echo PHP_VERSION;')). Install the XAMPP build with PHP 8.4."
ok "PHP $(php -r 'echo PHP_VERSION;')"

node -e 'process.exit(parseInt(process.versions.node) >= 18 ? 0 : 1)' \
  || fail "Node.js 18 or newer is required (found $(node -v))."
ok "Node $(node -v)"

modules=$(php -m | tr 'A-Z' 'a-z')
missing=""
for ext in pdo_mysql mbstring openssl fileinfo gd zip iconv xml dom; do
  grep -qx "$ext" <<< "$modules" || missing="$missing $ext"
done
[ -z "$missing" ] || fail "PHP extensions missing:$missing
       Open C:/xampp/php/php.ini, remove the ';' in front of extension=<name> for each one,
       save, restart Apache in the XAMPP Control Panel, and run this script again."
ok "PHP extensions"

# -----------------------------------------------------------------------------
step "2/7 Environment (.env)"
if [ ! -f .env ]; then
  cp .env.example .env
  ok "created .env from .env.example"
  echo "   Check DB_DATABASE / DB_USERNAME / DB_PASSWORD in .env, then run this script again."
  NEW_ENV=1
else
  NEW_ENV=0
  ok ".env kept as is"
fi
if ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force >/dev/null
  ok "APP_KEY generated"
fi
grep -q '^APP_DEBUG=true' .env && echo "   NOTE  APP_DEBUG=true in .env. Set APP_DEBUG=false on the client/defense PC."
[ "$NEW_ENV" = 1 ] && exit 0

# -----------------------------------------------------------------------------
step "3/7 PHP packages (composer)"
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
ok "vendor/ installed"

# -----------------------------------------------------------------------------
step "4/7 Front-end assets (npm)"
npm ci --no-audit --no-fund
npm run build
[ -f public/build/manifest.json ] || fail "npm run build did not produce public/build/manifest.json."
ok "public/build ready"

# -----------------------------------------------------------------------------
step "5/7 Database"
php -r 'require "vendor/autoload.php"; $app = require "bootstrap/app.php";
  $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
  try { Illuminate\Support\Facades\DB::connection()->getPdo(); } catch (Throwable $e) { exit(1); }' \
  || fail "Cannot reach the database. Start MySQL in XAMPP, create the database named in .env (DB_DATABASE), and check DB_USERNAME / DB_PASSWORD."

if [ "$FRESH" = 1 ]; then
  echo "   This DELETES every record in database '$(grep '^DB_DATABASE=' .env | cut -d= -f2)'."
  printf '   Type WIPE to continue: '
  read -r answer
  [ "$answer" = "WIPE" ] || fail "Cancelled. Nothing was changed."
  php artisan migrate:fresh --seed --force
  ok "database rebuilt with the default accounts"
else
  php artisan migrate --force
  users=$(php artisan tinker --execute='echo \App\Models\User::count();' 2>/dev/null | tail -n 1 | tr -dc '0-9')
  if [ "${users:-0}" = "0" ]; then
    php artisan db:seed --force
    ok "empty database: default accounts and academic setup loaded"
  else
    ok "migrations up to date (existing data kept)"
  fi
fi

# -----------------------------------------------------------------------------
step "6/7 Caches"
php artisan optimize:clear >/dev/null
php artisan optimize >/dev/null
php artisan view:cache >/dev/null
ok "config, routes and views cached"

# -----------------------------------------------------------------------------
step "7/7 Self-check"
for f in public/vendor/sweetalert2/sweetalert2.all.min.js public/vendor/tom-select/js/tom-select.complete.min.js \
         public/vendor/fontawesome/css/all.min.css public/vendor/fonts/fonts.css vendor/bacon/bacon-qr-code/src/Writer.php; do
  [ -f "$f" ] || fail "Missing $f. Run: git pull, then this script again."
done
ok "offline libraries and fonts present"
php artisan records:audit | tail -n 1

cat <<'DONE'

Setup complete.

  Start:   php artisan serve      then open http://127.0.0.1:8000
  Accounts (fresh install only): admin@cogtor.test / registrar@cogtor.test, password "password".
           Change both passwords right away under Profile.
  Keep the same .env (APP_KEY) on this PC: TOR verify codes depend on it.
DONE
