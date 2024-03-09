<?php /* $ sudo php --server localhost:8080 --docroot  . */ ?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">
    <head>
        <title><?php echo $_SERVER['PHP_SELF']; ?></title>
        <meta charset=”utf-8”/>
    </head>
    <body>
<?php

echo "<p>Begin TestCode:</p>";
if (file_exists('..//bin//readme.md')) $mpvBinaryWithPath ='Win'; else $mpvBinaryWithPath='Linux';
echo $mpvBinaryWithPath;

echo "<p>End of TestCode:</p>";
?>
    </body>
</html>
