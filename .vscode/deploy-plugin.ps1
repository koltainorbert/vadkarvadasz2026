# Production-first deploy script (plugin)
$localConfig = "$PSScriptRoot\local-config.ps1"
if (Test-Path $localConfig) { . $localConfig }

if (-not $LOCAL_WP_PLUGIN) {
    Write-Host 'Production mod: LocalWP plugin target nincs beallitva.' -ForegroundColor Yellow
    Write-Host 'Eles deploy push utan automatikus FTP workflow-bol fut.' -ForegroundColor Cyan
    Write-Host 'Plugin deploy kesz (production trigger mode).' -ForegroundColor Green
    exit 0
}

if (-not (Test-Path $LOCAL_WP_PLUGIN)) { New-Item -ItemType Directory -Path $LOCAL_WP_PLUGIN -Force | Out-Null }
Write-Host "Plugin -> $LOCAL_WP_PLUGIN" -ForegroundColor Cyan
Copy-Item "$PSScriptRoot\..\wp-plugin\vadaszapro-core\*" $LOCAL_WP_PLUGIN -Recurse -Force
Write-Host 'Plugin deploy kesz!' -ForegroundColor Green
