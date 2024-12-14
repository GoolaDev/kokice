# Orka's pub quiz

This is a web app based on dart, with a touch of html and css with a small back-end done in php.
It uses a bunch of media files (like movie inserts and trailers) for teams to guess.
You pick (click on a number) and mpv player starts it. Teams now have to guess the movie/song name, director, author, ....

How to use?

First, make sure php server(QuickPHP is included) and mpv are properly installed. Check **./bin/readme.txt**

Collect media files and put them in **./web/media/** folder.
On Windows execute **./start.bat** and it will launch your default browser and mpv.exe which will play _VideoTestSignal.mp4

On Linux, execute **./start.bsh** from ./kokice folder.
Navigate to http://IP:Port, specified in start.bat for Win and start.bsh for *Nix.

After site loads, enter team names. For teams that are not used leave it blank!
Blank means no chars whatsoever.To award a point to a team click the number next to it while
holding Ctrl or Alt. **Ctrl+click decrements score (in case of a mistake). Alt+click increments it.**
To undo last clicked number hold ctrl and click it again.
To see what media files have been played open  **./www/_playedmedia.txt**.
The last entry in it, is the last one opened (by clicking a number).

**Warning:**
If you run a php server on public/private interface and not loopback only, then anyone
with access to that IP:Port can browse to the http://ip:port/_playedmedia.txt and see 
all the files previously launched via mpv player.
One can also list all the media files from ./web/media.

To compile kokice.dart from ./kokice folder run:
dart compile js -O2 -o ./web/kokice.dart.js ./web/kokice.dart

Issues:
