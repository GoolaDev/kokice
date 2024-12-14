<?php /* $ sudo php --server localhost:8080 --docroot  . */ ?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">
    <head>
        <title><?php echo $_SERVER['PHP_SELF']; ?></title>
        <meta charset=”utf-8”/>
    </head>
    <body>
<?php

echo "<p>Begin TestCode.</p>";
echo "<p>Sending quit command</p>";

$socketName='unix:///tmp/mpvkviz.sock';
$command="{ \"command\": [\"quit\",\"1\"] }\n";
const mpvNixSocketName = "/tmp/mpvkviz.sock";
 
$socket = stream_socket_client($socketName, $errno, $errst);
if (!$socket)
{
    echo "<p>socket file not found, branch</p>";
}
else
{
    fwrite($socket, $command);
    $resp = fread($socket, 4096);
    fclose($socket);
    echo "<p>socket works OK branch.</p>";
}
$socketFileName = substr($socketName,7);
if (file_exists($socketFileName)) { unlink($socketFileName); }
 
$mpvRunLine = 'mpv --input-ipc-server='.mpvNixSocketName.' --no-osc --screen=1 --title="KVIZ - NVO ORKA" --no-terminal --hwdec=auto --cuda-decode-device=auto ../media/AMADEUS.webm> /dev/null&';
echo $mpvRunLine;
pclose(popen($mpvRunLine,"r"));


echo "<p>End TestCode.</p>";
?>
    </body>
</html>
