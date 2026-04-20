@echo off
TITLE FASO PAN - Unified Runner

echo ======================================================
echo           LANCEMENT DE FASO PAN FULLSTACK
echo ======================================================

:: 1. Lancer le Backend dans une nouvelle fenêtre
echo [BACKEND] Lancement sur http://127.0.0.1:8000...
start "FASO PAN - Backend" cmd /k "cd backend && php artisan serve --host=127.0.0.1 --port=8000"

:: 2. Lancer le Frontend dans une nouvelle fenêtre
echo [FRONTEND] Lancement sur le port 5173 (ou 5174 si occupe)...
start "FASO PAN - Frontend" cmd /k "cd frontend && npm run dev"

echo.
echo ------------------------------------------------------
echo Backend : http://127.0.0.1:8000
echo Frontend : http://localhost:5173 (ou 5174)
echo ------------------------------------------------------
echo.
echo Appuyez sur une touche pour fermer ce script (les serveurs resteront ouverts).
pause > nul
