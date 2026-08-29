@echo off
echo ==============================================
echo   MENJALANKAN DEPLOY UNTUK WINDOWS
echo ==============================================
echo.
bash deploy.sh
if %errorlevel% neq 0 (
    echo.
    echo [ERROR] Gagal menjalankan bash. 
    echo Pastikan Anda sudah menginstal Git untuk Windows (Git Bash).
)
echo.
pause
