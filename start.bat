@echo off

set bindIp=0.0.0.0
set bindir=.\bin
set webroot=.\web

start "" /B %bindir%\QuickPHP\QuickPHP.exe /Minimized /ShowtrayIcon=true /Bind=%bindIp% /Port=8080 /Root=%webroot% /AllowDirList=false /PHPMaxSecs=0 /NoConfirm=true /Start /StartBrowser
rem TIMEOUT /T 5
rem start "" /B %bindir%\mpv.exe --input-ipc-server=\\.\pipe\mpvkviz --screen=1 --title="KVIZ - NVO ORKA" --no-terminal --hwdec=auto --cuda-decode-device=auto --fs .\_VideoTestSignal.mp4
rem exit
rem start "" /B %bindir%\baretailpro.exe
