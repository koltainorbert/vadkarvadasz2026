# Production-first deploy script (theme)
$localConfig = "$PSScriptRoot\local-config.ps1"
if (Test-Path $localConfig) { . $localConfig }

if (-not $LOCAL_WP_THEME) {
    Write-Host 'Production mod: LocalWP tema target nincs beallitva.' -ForegroundColor Yellow
    Write-Host 'Eles deploy push utan automatikus FTP workflow-bol fut.' -ForegroundColor Cyan
    Write-Host 'Tema deploy kesz (production trigger mode).' -ForegroundColor Green
    exit 0
}

if (-not (Test-Path $LOCAL_WP_THEME)) { New-Item -ItemType Directory -Path $LOCAL_WP_THEME -Force | Out-Null }
Write-Host "Tema -> $LOCAL_WP_THEME" -ForegroundColor Cyan
Copy-Item "$PSScriptRoot\..\wp-theme\vadaszapro-theme\*" $LOCAL_WP_THEME -Recurse -Force

if ($LOCAL_WP_CHILD) {
    if (-not (Test-Path $LOCAL_WP_CHILD)) { New-Item -ItemType Directory -Path $LOCAL_WP_CHILD -Force | Out-Null }
    Copy-Item "$PSScriptRoot\..\wp-theme\vadaszapro-child\*" $LOCAL_WP_CHILD -Recurse -Force
    Write-Host 'Child tema is deployolva!'
}

Write-Host 'Tema deploy kesz!' -ForegroundColor Green
