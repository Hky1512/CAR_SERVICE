$php = 'C:\xampp\php\php.exe'
if (-not (Test-Path $php)) { $php = 'php' }
$failed = $false
Get-ChildItem -Path app,public,tests -Recurse -Filter *.php | ForEach-Object {
    & $php -l $_.FullName
    if ($LASTEXITCODE -ne 0) { $failed = $true }
}
if ($failed) { exit 1 }
& $php tests\unit.php
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
if (Get-Command node -ErrorAction SilentlyContinue) {
    node --check public\assets\app.js
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }
}
Write-Host 'PASS: Lint PHP, unit tests va JavaScript (neu co Node).'
Write-Host 'Kiem thu MySQL: C:\xampp\php\php.exe tests\check_mysql.php'
exit 0
