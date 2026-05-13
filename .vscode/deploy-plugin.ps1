Write-Host 'A kulon Plugin deploy megszunt. Teljes live deploy indul...' -ForegroundColor Yellow
& "$PSScriptRoot\deploy.ps1"
exit $LASTEXITCODE
