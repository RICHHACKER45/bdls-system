@echo off
:: 1. Go to the folder where this script is saved
cd /d "%~dp0"

:: 2. Use 'call' to ensure control returns to this script afterward
call npm run dbreset

:: 3. Now 'pause' will actually trigger, even on success or failure
pause
