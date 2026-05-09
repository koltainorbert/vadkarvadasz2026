# Production-first deploy script
# LocalWP csak opcionlis fallback. Ha nincs LocalWP config, sikeresen kilep es jelzi,
# hogy az eles deploy push utan GitHub Actions workflow-bol fut.

$localConfig = "$PSScriptRoot\local-config.ps1"
if (Test-Path $localConfig) {
    . $localConfig
}

$hasLocalTargets = ($LOCAL_WP_PLUGIN -or $LOCAL_WP_THEME)

if (-not $hasLocalTargets) {
    Write-Host 'Production mod: LocalWP target nincs beallitva.' -ForegroundColor Yellow
    Write-Host 'Eles deploy: push a main branch-re, majd a .github/workflows/deploy.yml FTP workflow fut.' -ForegroundColor Cyan
    Write-Host 'Deploy kesz (production trigger mode).' -ForegroundColor Green
    exit 0
}

if ($LOCAL_WP_PLUGIN) {
    if (-not (Test-Path $LOCAL_WP_PLUGIN)) { New-Item -ItemType Directory -Path $LOCAL_WP_PLUGIN -Force | Out-Null }
    Write-Host "Plugin -> $LOCAL_WP_PLUGIN" -ForegroundColor Cyan
    Copy-Item "$PSScriptRoot\..\wp-plugin\vadaszapro-core\*" $LOCAL_WP_PLUGIN -Recurse -Force
}

if ($LOCAL_WP_THEME) {
    if (-not (Test-Path $LOCAL_WP_THEME)) { New-Item -ItemType Directory -Path $LOCAL_WP_THEME -Force | Out-Null }
    Write-Host "Tema   -> $LOCAL_WP_THEME" -ForegroundColor Cyan
    Copy-Item "$PSScriptRoot\..\wp-theme\vadaszapro-theme\*" $LOCAL_WP_THEME -Recurse -Force
}

if ($LOCAL_WP_CHILD) {
    if (-not (Test-Path $LOCAL_WP_CHILD)) { New-Item -ItemType Directory -Path $LOCAL_WP_CHILD -Force | Out-Null }
    Copy-Item "$PSScriptRoot\..\wp-theme\vadaszapro-child\*" $LOCAL_WP_CHILD -Recurse -Force
    Write-Host 'Child tema is deployolva!'
}

Write-Host 'Deploy kesz!' -ForegroundColor Green
