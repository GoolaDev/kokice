@echo off

set bindIp=0.0.0.0
set bindir=.\bin
set webroot=.\web
set mediaDir=%webroot%\media
set mediaListFile=_medialist.txt

del /F /Q  %webroot%\%mediaListFile% 2>nul

for %%f in (%mediaDir%\*) do echo %%~nxf>>%webroot%\%mediaListFile%

start "" /B %bindir%\QuickPHP\QuickPHP.exe /Minimized /ShowtrayIcon=true /Bind=%bindIp% /Port=8080 /Root=%webroot% /AllowDirList=false /PHPMaxSecs=0 /NoConfirm=true /Start /StartBrowser
TIMEOUT /T 5
start "" /B %bindir%\mpv.exe --input-ipc-server=\\.\pipe\mpvkviz --screen=1 --title="KVIZ - NVO ORKA" --no-terminal --hwdec=auto --cuda-decode-device=auto --fs .\_VideoTestSignal.mp4
exit
rem start "" /B %bindir%\baretailpro.exe
