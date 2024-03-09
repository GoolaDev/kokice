<!DOCTYPE html>
<html lang="en" xml:lang="en">
    <head>
        <title><?php echo $_SERVER['PHP_SELF']; ?></title>
        <meta charset=”utf-8”/>
    </head>
    <body>
<?php
  
    function statusReport($message,$dieNow=false)
    {
        echo "<p><b>STATUS=".$message.".</b></p>";
        if ($dieNow) die(1);
    }

    function killMpv()
    {
        $command = "{ \"command\": [\"quit\",\"1\"] }\n";
        $mvppipeFP = @fopen(mvppipe, 'r+');
        if (!$mvppipeFP)
        {
            // nothing to pipe
            return;
        }
        fwrite($mvppipeFP, $command);
        fclose($mvppipeFP);
    }
    
    function killMpv2()
    {
        
        exec('echo quit>>'.mvppipe);
    }
    
    function killMpv3()
    { // has performance issues
        $taskname='mpv.exe';
        $found=true;
        while($found)
        {
            $task_list = array();
            exec("start /B tasklist 2>NUL", $task_list);
            foreach ($task_list as &$task)
            {
                if (strpos($task, $taskname) !== false)
                {
                    exec('..\\bin\\SendKeys.exe "ORKA" "%{F4}"');
                    $found = true;
                    break;
                }
                else
                {
                    $found = false;
                }
            }
        }
        unset($task);
    }
    
    function inputFilter($inputString)
    {
        $pattern = '/[\\w\\(\\)\\.\\-\',]/';
        
        for ($i = 0; $i < strlen($inputString) && $i<80 ; $i++)
        {
            $char = $inputString[$i];
            
            $result = preg_match($pattern,$char);
            //echo "<p>".$char." ".$result."</p>";
            if ($result) continue;
            //echo "<p>Filter status:bad input</p>";
            return false; // invalid input found
        }
        return true; // filter passed OK!
    }
    
    function isMediaNameExists2(&$mediaName,$mediaFile)
    {
        try
        {
            $mediaArray = @file($mediaFile,FILE_IGNORE_NEW_LINES);
            if ($mediaArray===false) throw new Exception("MEDIA FILE ERROR");
            if (!(count($mediaArray)>0)) throw new Exception("MEDIA ARR 0");
        } 
        catch(Exception $e)
        {
            statusReport($e->getMessage(),true);
            return false;
        }
                
        foreach ($mediaArray as &$item)
        {
            if ($item===$mediaName) { unset($item);return true; }
        }
        unset($item);
        return false;
    }
    // checks for raw file existance
    function isMediaNameExists(&$mediaName)
    {
        if (file_exists($mediaName)) return true;
        return false;
    }
    
    function logUsedMedia($fileName,$logMsg)
    {
        $logFile = @fopen($fileName, "a");
        if (!$logFile)
        {
            //header('Internal Server Error:500', true, 500);
            statusReport("FILE OPEN ERROR",false);
            return false;
        }
        
        $dt = new DateTime("now",new DateTimeZone('UTC'));
        // $logLine = 'TimeStamp: '.$dt->format('Y-m-d_H:i:s').' Filename: '.$logMsg.PHP_EOL;
        $logLine2 = $logMsg.' #'.$dt->format('Y-m-d_H:i:s').'#'.PHP_EOL;
        if (!fwrite($logFile, $logLine2,150))
         {
            //header('Internal Server Error:500', true, 500);
            statusReport("FILE WRITE ERROR",false);
            return false;
        }
        
        return true;
    }
    /// constants
    const playedMediaFile = '_playedmedia.txt'; // in webroot
    const mpvFileWithPath = '..\\bin\\mpv.exe';
    const mvppipe = '\\\\.\\pipe\\mpvkviz';
    const mediaListFile = '_medialist.txt';
    
    $input = isset($_GET['medianame']) ? $_GET['medianame'] : '';
    $input = strip_tags($input);
    $input = stripslashes($input);
        
    if (!(strlen($input)>0) || !inputFilter($input)) statusReport("FILTER ERROR. BAD INPUT. $Input: ".$input,true);
    
    $mediaFile=$input;
    $mediaFileWithPath='.\\media\\'.$mediaFile;
    
    if (!isMediaNameExists($mediaFileWithPath)) statusReport("MEDIA NOT FOUND. Media File: ".$mediaFile,true);
    
    killMpv();

    $exeFile='start "" /B '.mpvFileWithPath.' --input-ipc-server='.mvppipe.' --no-osc --screen=1 --title="KVIZ - NVO ORKA" --no-terminal --hwdec=auto --cuda-decode-device=auto --window-maximized=yes '.$mediaFileWithPath;
    
    // $output=null;
    // $retval=null;
    // $result = exec($exeFile, &$output, &$retval);
    if (logUsedMedia(playedMediaFile,$mediaFile)) { statusReport("OK",false); }
    pclose(popen($exeFile,"r"));

?>

    </body>
</html>

