@echo off
:loop
php artisan schedule:run
ping localhost -n 61 > nul
goto loop