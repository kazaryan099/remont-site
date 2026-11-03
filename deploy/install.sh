#!/bin/sh
# Установка сайта DOMO-remont на новый хостинг из копии репозитория.
# Запускать на сервере по SSH: sh deploy/install.sh
# Перед запуском: создать базу и почтовый ящик в панели, заполнить deploy/hosting.env.
# Если в папке сайта уже есть WordPress, скрипт останавливается и ничего не меняет.
set -eu

ROOT=$(cd "$(dirname "$0")/.." && pwd)
ENV_FILE=${ENV_FILE:-$ROOT/deploy/hosting.env}
[ -f "$ENV_FILE" ] || { echo "Нет файла $ENV_FILE: скопируйте deploy/hosting.env.example и заполните."; exit 1; }
set -a; . "$ENV_FILE"; set +a

for v in SITE_URL WEB_ROOT DB_NAME DB_USER DB_PASSWORD ADMIN_USER ADMIN_PASSWORD ADMIN_EMAIL; do
  eval "val=\${$v:-}"
  [ -n "$val" ] || { echo "В hosting.env не заполнено: $v"; exit 1; }
done
if [ -e "$WEB_ROOT/wp-config.php" ]; then
  echo "В $WEB_ROOT уже есть wp-config.php. Установка остановлена, ничего не изменено."
  exit 1
fi

PHP_BIN=${PHP_BIN:-php}
WP_VERSION=${WP_VERSION:-7.1.3}
CLI=${WP_CLI_PHAR:-$HOME/.wp-cli.phar}
if [ ! -f "$CLI" ]; then
  echo "== Скачиваю WP-CLI"
  curl -fsSL -o "$CLI" https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
fi
# Память с запасом: распаковка WordPress и обработка фото не укладываются в обычные 128 МБ.
wp() { "$PHP_BIN" -d memory_limit=512M -d error_reporting=E_ERROR -d display_errors=0 "$CLI" --path="$WEB_ROOT" "$@"; }

echo "== Скачиваю WordPress $WP_VERSION"
mkdir -p "$WEB_ROOT"
wp core download --locale=ru_RU --version="$WP_VERSION" --force --quiet

echo "== Создаю wp-config.php"
"$PHP_BIN" "$ROOT/deploy/make-config.php" "$WEB_ROOT/wp-config.php"

echo "== Устанавливаю WordPress"
# Временный случайный пароль: настоящий ставит apply-env.php, чтобы его не было в командной строке.
wp core install --url="$SITE_URL" --title="DOMO" --admin_user="$ADMIN_USER" \
  --admin_password="$(od -An -N24 -tx1 /dev/urandom | tr -d ' \n')" --admin_email="$ADMIN_EMAIL" --skip-email --quiet

echo "== Копирую тему"
rm -rf "$WEB_ROOT/wp-content/themes/remont"
cp -R "$ROOT/theme" "$WEB_ROOT/wp-content/themes/remont"
wp theme activate remont --quiet

echo "== Наполняю сайт: услуги, проекты, фото, страницы, меню"
wp eval-file "$ROOT/seed/seed.php"

echo "== Данные компании и настройки хостинга"
wp eval-file "$ROOT/deploy/site-settings.php"
wp eval-file "$ROOT/deploy/apply-env.php"

echo "== Настраиваю адреса страниц"
HTACCESS="$WEB_ROOT/.htaccess"
: > "$HTACCESS"
case "$SITE_URL" in
  https://*)
    host=${SITE_URL#https://}
    {
      echo '# Всё на https. Проверяем и прямой https, и заголовок прокси хостинга, иначе возможен цикл.'
      echo '<IfModule mod_rewrite.c>'
      echo 'RewriteEngine On'
      echo 'RewriteCond %{HTTPS} off'
      echo 'RewriteCond %{HTTP:X-Forwarded-Proto} !https'
      echo "RewriteRule ^(.*)\$ https://$host/\$1 [R=301,L]"
      echo '</IfModule>'
      echo
    } >> "$HTACCESS"
    ;;
esac
cat "$ROOT/deploy/htaccess-wordpress.txt" >> "$HTACCESS"
wp rewrite structure '/%postname%/' --quiet
wp cache flush --quiet

echo
echo "Готово: $SITE_URL (админка: $SITE_URL/wp-admin/, логин $ADMIN_USER)"
