# Первый запуск: поднимает контейнеры, устанавливает WordPress, активирует тему и загружает демо-контент.
# Запуск из папки проекта:  powershell -ExecutionPolicy Bypass -File scripts\setup.ps1
# Ошибки внешних команд проверяем вручную по $LASTEXITCODE (в PowerShell 5.1 вывод в stderr иначе прерывает скрипт).
$ErrorActionPreference = 'Continue'
Set-Location (Split-Path $PSScriptRoot -Parent)

if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
    Write-Host "Создан файл .env из .env.example. Задайте в нём свои пароли и запустите скрипт ещё раз." -ForegroundColor Yellow
    exit 1
}

# Читаем .env
$envVars = @{}
Get-Content .env | Where-Object { $_ -match '^\s*[^#].*=' } | ForEach-Object {
    $k, $v = $_ -split '=', 2
    $envVars[$k.Trim()] = $v.Trim()
}

function Wp {
    docker compose run --rm -T wpcli wp @args
    if ($LASTEXITCODE -ne 0) { throw "Команда wp $args завершилась с ошибкой" }
}

Write-Host "1/5 Запускаю контейнеры..." -ForegroundColor Cyan
docker compose up -d
if ($LASTEXITCODE -ne 0) { throw "docker compose up не удался" }

Write-Host "2/5 Жду, пока WordPress подготовит файлы..." -ForegroundColor Cyan
$ready = $false
for ($i = 0; $i -lt 60; $i++) {
    docker compose exec -T wordpress test -f /var/www/html/wp-config.php 2>$null
    if ($LASTEXITCODE -eq 0) { $ready = $true; break }
    Start-Sleep -Seconds 2
}
if (-not $ready) { throw "WordPress не подготовил wp-config.php за 2 минуты. Посмотрите: docker compose logs wordpress" }

Write-Host "3/5 Устанавливаю WordPress..." -ForegroundColor Cyan
docker compose run --rm -T wpcli wp core is-installed 2>$null
if ($LASTEXITCODE -ne 0) {
    Wp core install --url="$($envVars.WP_URL)" --title="Remont" `
        --admin_user="$($envVars.WP_ADMIN_USER)" --admin_password="$($envVars.WP_ADMIN_PASSWORD)" `
        --admin_email="$($envVars.WP_ADMIN_EMAIL)" --skip-email --locale=ru_RU
} else {
    Write-Host "   уже установлен — пропускаю"
}

Write-Host "4/5 Русский язык, адреса страниц, тема..." -ForegroundColor Cyan
docker compose run --rm -T wpcli wp language core install ru_RU --activate 2>$null
if ($LASTEXITCODE -ne 0) { Write-Host "   (не удалось скачать русский перевод админки — сайт работает и без него)" -ForegroundColor Yellow }
Wp rewrite structure '/%postname%/' --hard
Wp theme activate remont

Write-Host "5/5 Загружаю демо-контент..." -ForegroundColor Cyan
Wp eval-file /seed/seed.php

Write-Host ""
Write-Host "Готово!" -ForegroundColor Green
Write-Host "Сайт:     $($envVars.WP_URL)"
Write-Host "Админка:  $($envVars.WP_URL)/wp-admin  (логин и пароль — в файле .env)"
