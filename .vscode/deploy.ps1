# Live deploy script (vadkarvadasz.hu only)
# Kizarolag eles deploy: git commit + git push, majd GitHub Actions FTP workflow.

Set-Location (Resolve-Path "$PSScriptRoot\..")

$stamp = Get-Date -Format 'yyyy.MM.dd_HH.mm'
$msg = "Deploy_$stamp"

git add .
git diff --cached --quiet

if ($LASTEXITCODE -ne 0) {
    git commit -m $msg
} else {
    Write-Host 'Nincs commitolhato valtozas, push indul.' -ForegroundColor Yellow
}

git push

if ($LASTEXITCODE -eq 0) {
    Write-Host 'Live deploy trigger kesz: vadkarvadasz.hu workflow indul.' -ForegroundColor Green
    exit 0
}

Write-Host 'Hiba: a push nem sikerult.' -ForegroundColor Red
exit 1
