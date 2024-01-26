<!DOCTYPE html>
<html lang="en" xml:lang="en">
    <head>
        <title><?php echo $_SERVER['PHP_SELF']; ?></title>
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
        $mvppipe = @fopen(mvppipe, 'r+');
        if (!$mvppipe)
        {
            // nothing to pipe
            return;
        }
        fwrite($mvppipe, $command);
        fclose($mvppipe);
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
        $pattern = '/[\\w\\(\\)\\.\\-\',&]/';
        
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
    
    function isMediaNameExists(&$mediaName,$mediaFile)
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
    function isMediaNameExists2(&$mediaName)
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
    function checkFileType($mediaFilePathName)
    {   // *****  MAKE SURE THAT AUDIO ONLY FILEs DO NOT HAVE ANY IMAGES EMBEDDED. *****
        
        $command = 'start "" /B '.ffprobeFileWithPath. ' -loglevel error -show_entries stream=codec_type -of json '.$mediaFilePathName;
        $output = shell_exec($command);
        
        $streamsArray = json_decode($output, true);
        
        // if ($streamsArray['streams'][0]['codec_type']==null) echo 'code_type_0: NULL<br>'; else { echo ($streamsArray['streams'][0]['codec_type']);echo "<br>"; }
        // if ($streamsArray['streams'][1]['codec_type']==null) echo 'code_type_1: NULL<br>'; else { echo ($streamsArray['streams'][1]['codec_type']);echo "<br>"; }
        
        if ( ($streamsArray['streams'][0]['codec_type']=='video') || ($streamsArray['streams'][1]['codec_type']=='video') ) 
            return "video";//( $videoParam = '--window-maximized=yes ' ); // it's a video(+maybe audio)
        //return ( $audioParam = '--window-minimized=yes ' );
        if ( ($streamsArray['streams'][0]['codec_type']=='audio') || ($streamsArray['streams'][1]['codec_type']=='audio') )
            return "audio";
        
        return null;
        // "audio" means audio only, "video" can be video or video+audio
    }

    /// constants
    const playedMediaFile = '_playedmedia.txt'; // in webroot
    const mpvFileWithPath = '..\\bin\\mpv.exe';
    const ffprobeFileWithPath = '..\\bin\\ffprobe.exe';
    const mvppipe = '\\\\.\\pipe\\mpvkviz';
    
    $input = isset($_GET['medianame']) ? $_GET['medianame'] : '';
    $input = strip_tags($input);
    $input = escapeshellcmd(stripslashes($input));
        
    if (!(strlen($input)>0) || !inputFilter($input)) statusReport("FILTER ERROR. BAD INPUT. $Input: ".$input,true);
    
    $mediaFile=$input;
    $mediaFileWithPath='.\\media\\'.$mediaFile;
    
    if (!isMediaNameExists2($mediaFileWithPath)) statusReport("MEDIA NOT FOUND. Media File: ".$mediaFile,true);
    killMpv();
    
    $winSizeParam = '--window-maximized=yes '; // remove this line if you want to use $winSizeParam
    
    if ( checkFileType($mediaFileWithPath) == "video" ) $winSizeParam = '--window-maximized=yes ';
    if ( checkFileType($mediaFileWithPath) == "audio" ) $winSizeParam = '--window-minimized=yes ';
 
    $exeFile='start "" /B '.mpvFileWithPath.' --input-ipc-server='.mvppipe.' --no-osc --screen=1 --title="KVIZ - NVO ORKA" --no-terminal --hwdec=auto --cuda-decode-device=auto '.$winSizeParam.$mediaFileWithPath;
  
    // $output=null;
    // $retval=null;
    // $result = exec($exeFile, &$output, &$retval);
    if (logUsedMedia(playedMediaFile,$mediaFile)) statusReport("OK",false);
    pclose(popen($exeFile,"r"));
?>
    </body>
</html>
