Write-Host 'A kulon Tema deploy megszunt. Teljes live deploy indul...' -ForegroundColor Yellow
& "$PSScriptRoot\deploy.ps1"
exit $LASTEXITCODE
