@echo off
chcp 65001 >nul
cd /d "%~dp0"
if not exist "tests\kiem_thu_8_ca.php" (
  echo Khong tim thay tests\kiem_thu_8_ca.php.
  echo Giai nen thu muc tests vao thu muc goc source CAR SERVICE.
  pause
  exit /b 1
)
"C:\xampp\php\php.exe" "tests\kiem_thu_8_ca.php"
echo.
echo Ket qua duoc luu trong tests\ket_qua_8_ca.txt va tests\ket_qua_8_ca.json.
pause
