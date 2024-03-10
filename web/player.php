<?php /* $ sudo php --server localhost:8080 --docroot  . */ ?>
<!DOCTYPE html>
<html lang="en" xml:lang="en">
    <head>
        <title><?php echo $_SERVER['PHP_SELF']; ?></title>
        <meta charset=”utf-8”/>
    </head>
    <body>
<?php
    
    function consoleLog($message,$dieNow=false)
    {
        error_log($message, 4);
        if ($dieNow) die(1);
    }

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
    
    function inputFilter($inputString)
    {
        $pattern = '/[\\w\\(\\)\\.\\-\',]/';
        // Allowed: word_char ( ) . - , '
        for ($i = 0; $i < strlen($inputString) && $i<80 ; $i++)
        {
            $char = $inputString[$i];
            $result = preg_match($pattern,$char);
            if ($result) { continue; }
            return false; // invalid input found !!!
        }
        return true; // filter passed OK!
    }
    
    function isMediaNameExists2(&$mediaName,$mediaFile) // obsolete function
    {
        try
        {
            $mediaArray = @file($mediaFile,FILE_IGNORE_NEW_LINES);
            if ($mediaArray===false) { throw new Exception("MEDIA FILE ERROR"); }
            if (!(count($mediaArray)>0)) { throw new Exception("MEDIA ARR 0"); }
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
        if (file_exists($mediaName)) { return true; }
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
        
        if (file_exists('..\\bin\\ffprobe.exe')) $ffprobeFileWithPath ='..\\bin\\ffprobe.exe'; else $ffprobeFileWithPath='ffprobe';
        $command = 'start "" /B '.$ffprobeFileWithPath. ' -loglevel error -show_entries stream=codec_type -of json '.$mediaFilePathName;
        $output = shell_exec($command);
        
        $streamsArray = json_decode($output, true);
        
        if ( ($streamsArray['streams'][0]['codec_type']=='video') || ($streamsArray['streams'][1]['codec_type']=='video') )
            return "video";//( $videoParam = '--window-maximized=yes ' ); // it's a video(+maybe audio)
        //return ( $audioParam = '--window-minimized=yes ' );
        
        if ( ($streamsArray['streams'][0]['codec_type']=='audio') || ($streamsArray['streams'][1]['codec_type']=='audio') )
            return "audio";
        
        return null;
        // "audio" means audio only, "video" can be video or video+audio
    }
  
    const playedMediaFile = '_playedmedia.txt'; // in webroot
    const mvppipe = '\\\\.\\pipe\\mpvkviz';
    
    $input = isset($_GET['medianame']) ? $_GET['medianame'] : '';
    $input = strip_tags($input);
    //$input = escapeshellcmd(stripslashes($input));

    if (!(strlen($input)>0) || !inputFilter($input)) statusReport("FILTER ERROR. BAD INPUT: ".$input,true);
    
    $mediaFile=$input;
    if (file_exists('..\\bin\\mpv.exe'))
    {
        $mediaFileWithPath='.\\media\\'.$mediaFile;
    }
    else
    { // nix
        $mediaFileWithPath='./media/'.$mediaFile;
    }
    
    if (!file_exists($mediaFileWithPath)) statusReport("MEDIA NOT FOUND. Media File: ".$mediaFile,true);
    
    killMpv();
/*
    if ( checkFileType($mediaFileWithPath) == "video" ) $winSizeParam = '--window-maximized=yes ';
    if ( checkFileType($mediaFileWithPath) == "audio" ) $winSizeParam = '--window-minimized=yes ';
*/
    //$winSizeParam = '--window-maximized=yes ';
    $winSizeParam = '--fs ';
    if (file_exists('..\\bin\\mpv.exe'))
    {   // win
        $mpvBinaryWithPath ='..\\bin\\mpv.exe';
        $exeFile='start "" /B '.$mpvBinaryWithPath.' --input-ipc-server='.mvppipe.' --no-osc --screen=1 --title="KVIZ - NVO ORKA" --no-terminal --hwdec=auto --cuda-decode-device=auto '.$winSizeParam.$mediaFileWithPath;
    }
    else
    {   // nix
        $mpvBinaryWithPath='mpv';
        $exeFile=$mpvBinaryWithPath.' --input-ipc-server='.mvppipe.' --no-osc --screen=1 --title="KVIZ - NVO ORKA" --no-terminal --hwdec=auto --cuda-decode-device=auto '.$winSizeParam.'"'.$mediaFileWithPath.'"';
    }
    // $output=null;
    // $retval=null;
    // $result = exec($exeFile, &$output, &$retval);
    if (logUsedMedia(playedMediaFile,$mediaFile)) { statusReport("OK",false); }
    pclose(popen($exeFile,"r"));
?>
    </body>
</html>
