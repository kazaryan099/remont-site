# ВНИМАНИЕ: полностью удаляет локальную базу данных и файлы WordPress (загруженные фото, плагины, заявки).
# Файлы темы (папка theme) и демо-фото (seed) не затрагиваются.
# Запуск: powershell -ExecutionPolicy Bypass -File scripts\reset.ps1
Set-Location (Split-Path $PSScriptRoot -Parent)

Write-Host "Будут удалены контейнеры и тома db_data и wp_data: все записи, заявки и загруженные файлы локального сайта." -ForegroundColor Yellow
$answer = Read-Host "Чтобы продолжить, введите DELETE"
if ($answer -cne 'DELETE') {
    Write-Host "Отменено." -ForegroundColor Cyan
    exit 0
}

docker compose --profile tools down -v
Write-Host "Окружение сброшено. Для новой установки запустите scripts\setup.ps1" -ForegroundColor Green
