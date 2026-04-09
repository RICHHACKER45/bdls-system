@echo off
cd /d "%~dp0"

:menu
cls
echo ===============================
echo   LARAVEL QUICK TOOLS
echo ===============================
echo 1) Run Server
echo 2) Run Database Reset
echo 3) Run Formatting
echo 4) Exit
echo ===============================
set /p choice="Enter a number (1-4): "

:: Use 'call' followed by the name of your other .bat file
if "%choice%"=="1" call startserver.bat & goto menu
if "%choice%"=="2" call dbreset.bat & goto menu
if "%choice%"=="3" call format.bat & goto menu
if "%choice%"=="4" exit

echo Invalid choice, try again.
pause
goto menu
