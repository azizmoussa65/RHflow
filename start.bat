@echo off
title HRFlow - Demarrage
color 0A

echo.
echo  ██╗  ██╗██████╗ ███████╗██╗      ██████╗ ██╗    ██╗
echo  ██║  ██║██╔══██╗██╔════╝██║     ██╔═══██╗██║    ██║
echo  ███████║██████╔╝█████╗  ██║     ██║   ██║██║ █╗ ██║
echo  ██╔══██║██╔══██╗██╔══╝  ██║     ██║   ██║██║███╗██║
echo  ██║  ██║██║  ██║██║     ███████╗╚██████╔╝╚███╔███╔╝
echo  ╚═╝  ╚═╝╚═╝  ╚═╝╚═╝     ╚══════╝ ╚═════╝  ╚══╝╚══╝
echo.
echo  Demarrage de l'application HRFlow...
echo  =====================================
echo.

REM Demarrer le backend PHP
echo  [1/2] Demarrage du Backend (port 8000)...
start "HRFlow Backend" cmd /k "cd /d C:\Users\STE GIMS\Desktop\PFE\backend && color 0B && echo Backend HRFlow demarre sur http://localhost:8000 && echo. && php -S localhost:8000 -t public"

REM Attendre 2 secondes que le backend soit pret
timeout /t 2 /nobreak > nul

REM Demarrer le frontend Vue.js
echo  [2/2] Demarrage du Frontend (port 5173)...
start "HRFlow Frontend" cmd /k "cd /d C:\Users\STE GIMS\Desktop\PFE\frontend && color 0D && echo Frontend HRFlow demarre sur http://localhost:5173 && echo. && npm run dev"

REM Attendre 4 secondes que le frontend compile
echo.
echo  Chargement en cours... (attente 5 secondes)
timeout /t 5 /nobreak > nul

REM Ouvrir le navigateur
echo  [3/3] Ouverture du navigateur...
start http://localhost:5173

echo.
echo  ✓ HRFlow est lance !
echo  → Application : http://localhost:5173
echo  → Backend API : http://localhost:8000
echo.
echo  Pour arreter : fermez les 2 fenetres "HRFlow Backend" et "HRFlow Frontend"
echo.
pause
