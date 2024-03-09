# Orka's pub quiz

This is a web app based on dart,with a touch of html and css with a small back-end done in php.
It is made for windows but can be adapted for nix as well (see the bin folder and player.php).
It uses a bunch of media files (like movie inserts and trailers) for teams to guess.
You pick (click on  a number) and mpv player starts it. Teams now have to guess the movie/song name, director, author, ....

How to use?

First, make sure php server(QuickPHP is included) and mpv ara properly installed. Check **./bin/readme.txt**

Collect media files and put them in **./web/media/** folder
On Windows execute **./start.bat** and it will launch your default browser and mpv.exe which will play _VideoTestSignal.mp4

In Browser window enter team names. For teams that are not used enter blank name.
Blank means no chars whatsoever. To award a point to a team click the number next to it while
holding Ctrl or Alt. Ctrl+click decrements score (in case of a mistake). **Alt+click increments it.**
To see what media files have been played open  **./www/_playedmedia.txt**.
The last entry in it, is the last one opened (by clicking a number).

**Warning:**
If you run a php server on public/private interface and not loopback only, then anyone
with access to that IP can browse to the http://ip:8080/_playedmedia.txt and see the filename being played by mpv.


