# Script de backup: base de datos + archivos subidos (storage/app)
# Uso: desde PowerShell, parado en la carpeta del proyecto -> .\backup.ps1

$fecha = Get-Date -Format "yyyy-MM-dd_HHmm"
$carpetaBackup = "backups"

if (-not (Test-Path $carpetaBackup)) {
    New-Item -ItemType Directory -Force $carpetaBackup | Out-Null
}

Write-Host "Respaldando base de datos..."
$dumpPath = "$carpetaBackup\practicaiii_$fecha.sql"
& mysqldump -u root practicaiii > $dumpPath
Write-Host "  -> $dumpPath"

Write-Host "Respaldando archivos subidos (storage/app)..."
$zipPath = "$carpetaBackup\storage_app_$fecha.zip"
Compress-Archive -Path "storage\app" -DestinationPath $zipPath -Force
Write-Host "  -> $zipPath"

Write-Host "Listo. Backup completo en: $carpetaBackup\"
