@echo off
cd /d %~dp0
node tools\server.mjs --host 127.0.0.1 --port 4173
pause
