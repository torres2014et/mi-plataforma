@echo off
REM ============================================================
REM   Domicilios Ubate - Arranca el servidor para pruebas
REM   Abre 2 ventanas: el backend (Laravel) y el tunel (ngrok).
REM   Dejalas ABIERTAS mientras pruebas. Cierra para apagar.
REM ============================================================

echo Iniciando backend Laravel en el puerto 8000...
start "Backend Laravel" cmd /k "cd /d C:\xampp\htdocs\mi-plataforma && C:\xampp\php\php.exe artisan serve --host=0.0.0.0 --port=8000"

echo Iniciando Reverb (websockets / tiempo real) en el puerto 8080...
start "Reverb (websockets)" cmd /k "cd /d C:\xampp\htdocs\mi-plataforma && C:\xampp\php\php.exe artisan reverb:start --host=0.0.0.0 --port=8080"

echo Iniciando proxy de tiempo real en el puerto 9000 (une API + websockets)...
start "Proxy tiempo real" cmd /k "cd /d C:\xampp\htdocs\mi-plataforma && node proxy-tiempo-real.cjs"

echo Esperando 3 segundos...
timeout /t 3 /nobreak >nul

echo Iniciando tunel ngrok (dominio fijo) -> apunta al PROXY (9000)...
start "ngrok" cmd /k ""%LOCALAPPDATA%\ngrok-bin\ngrok.exe" http --url=https://retype-coil-charity.ngrok-free.dev 9000"

echo.
echo ============================================================
echo  LISTO. Se abrieron 4 ventanas:
echo   backend (8000) + Reverb (8080) + proxy (9000) + ngrok.
echo  NO las cierres mientras pruebas.
echo.
echo  Todo sale por UN solo dominio:
echo   https://retype-coil-charity.ngrok-free.dev
echo   - API y web  -> Laravel
echo   - /app (wss) -> Reverb  (GPS en vivo)
echo.
echo  TIEMPO REAL desde CUALQUIER SITIO: ya funciona por ngrok,
echo  tanto en la APP como en la WEB (el GPS web exige https,
echo  que ngrok ya da). La APP apunta sola al dominio.
echo ============================================================
echo.
pause
