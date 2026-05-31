@echo off
:: 1. Go to the folder where this script is saved
cd /d "%~dp0"

:: 2. Your command goes here (Change this line for each new .bat)
call npm run clear-all

:: 3. Stop the window from closing so you can see errors
pause
