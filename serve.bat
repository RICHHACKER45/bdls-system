@echo off
:: Automatically navigate to the folder where this .bat file is saved
cd /d "%~dp0"

:: Run your NPM command
call bun run start

:: Keep terminal open to see results
pause
