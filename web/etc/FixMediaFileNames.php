<!DOCTYPE html>
<html lang="en" xml:lang="en">
    <head>
        <title><?php echo $_SERVER['PHP_SELF']; ?></title>
        <meta charset=”utf-8”/>
    </head>
    <body>
<?php
    // this probably should be a shell script !
    const pathToMedia = '../media/';
    if (!file_exists(pathToMedia)) { echo "<p><strong>Path does not exist ! </strong></p>"; die(1); }

    function checkAndFix($fileNameOld)
    {
        $pattern = '/[\\w\\(\\)\\.\\-\',_]/';
        $fileNameNew='';
        $subChar = '_';
        // Allowed: word_char ( ) . - ' ,
        for ($i = 0; $i < strlen($fileNameOld) && $i<80 ; $i++)
        {
            $char = $fileNameOld[$i];
            $result = preg_match($pattern,$char);
            if ($result)
            {
                $fileNameNew = $fileNameNew.$char;
            }
            else
            {
                $fileNameNew = $fileNameNew.$subChar;
            }
        }
        if ($fileNameOld!==$fileNameNew)
        {
            echo "<p><strong>$fileNameOld will be renamed</strong></p>";
            rename(pathToMedia.$fileNameOld,pathToMedia.$fileNameNew);
        }
    }
   
    $filesArray = array_diff( scandir(pathToMedia), array('.','..') );
    
    echo "<p>Fixing filenames: </p>";
    foreach ($filesArray as &$fileName)
    {
        echo "<p>File: $fileName</p>";
        checkAndFix($fileName);
    }
    echo "<p>Done.</p>";
?>
    </body>
</html>
