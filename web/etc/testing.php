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

    function inputFilter($inputString) // php pattern goes inside  /[ ]/
    {
        $pattern = '/[\\w\\(\\)\\.\\-,]/'; //allow alphanumeric characters plus _,(),.,-,
        
        for ($i = 0; $i < strlen($inputString) && $i<80 ; $i++)
        {
            $char = $inputString[$i];
            $result = preg_match($pattern,$char);
            if ($result) { continue; }
            echo "<p>Bad Char: [". $char."]</p>";
            //return false; // invalid input found !!!
        }
        return true; // filter passed OK!
    }
   
    $filesArray = array_diff( scandir(pathToMedia), array('.','..') );
    
    echo "<p>Fixing filenames: </p>";
    foreach ($filesArray as &$fileName)
    {
        echo "<p>Filename: $fileName</p>";
        //echo "<p>";
	    inputFilter($fileName);
		//echo "</p>";
    }
    echo "<p>Done.</p>";
?>
    </body>
</html>
