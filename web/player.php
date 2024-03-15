<!DOCTYPE html>
<html lang="en" xml:lang="en">
<head>
    <title><?php echo $_SERVER['PHP_SELF']; ?></title>
    <meta charset=”utf-8”/>
</head>

<body>
<?php
    const playedMediaFile = '_playedmedia.txt'; // in webroot
    const mpvWinBinary = '..\\bin\\mpv.exe';
    const mpvNixBinary = 'mpv';
    const mpvWinPipeName = '\\\\.\\pipe\\mpvkviz';
    const mpvNixSocketName = '/tmp/mpvkviz.sock';
    const killCommand = "{ \"command\": [\"quit\",\"1\"] }\n";
    const pathToMedia = './media/';
    const badInputMsg = "ERROR. BAD INPUT: ";
    const winSizeParam = '--fs ';

    function exitScript()
    {
        echo "</body></html>";
        die(1);
        return "null";
    }
    
    function logToConsole($message,$dieNow=false)
    {
        error_log($message, 4);
        if ($dieNow) exitScript();
    }

    function statusReport($message,$dieNow=false)
    {
        echo "<p><b>STATUS=".$message.".</b></p>";
        if ($dieNow) exitScript();
        return "null";
    }

    function killMpvWin()
    {
        $mpvpipeFP = @fopen(mpvWinPipeName, 'r+');
        if (!$mpvpipeFP)
        {
            // nothing to pipe
            return;
        }
        fwrite($mpvpipeFP, killCommand);
        fclose($mpvpipeFP);
    }
    
    function killMpvNix()
    {
        $socket = stream_socket_client('unix://'.mpvNixSocketName, $errno, $errst);
        if ($socket)
        {
            fwrite($socket, killCommand);
            $resp = fread($socket, 256);
            fclose($socket);
        }
        if (file_exists(mpvNixSocketName)) { unlink(mpvNixSocketName); }
    }
    function strcontains ($haystack,$needle)
    {
        return empty($needle) || strpos($haystack, $needle) !== false;
    }
    
    function inputFilter($inputString)
    {
        $pattern = '/[\\w\\(\\)\\.\\-`,_]/';
        // Allowed: word_char ( ) . - , '
        if (strcontains($inputString, '..')) return false;
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
    
    function listMediaCommand()
    {
        if (!file_exists(pathToMedia)) { statusReport("Path does not exist !",true); }

        $filesArray = array_diff( scandir(pathToMedia), array('.','..') );
        echo '<div id="MediaListContainer">';
        foreach ($filesArray as &$fileName)
        {
            if (!inputFilter($fileName)) statusReport(badInputMsg.$fileName,true); //protect
            if (strcontains($fileName,'readme') || strcontains($fileName,'README') ) continue;
            echo "$fileName<br>";
        }
        echo '</div>';
        statusReport("OK",false);
    }
    
    function playMediaCommand($mediaName)
    {   
        if (!inputFilter($mediaName)) statusReport(badInputMsg.$mediaName,true);
        
        $mediaFileWithPath = pathToMedia.$mediaName;
        /*
            if ( checkFileType($mediaFileWithPath) == "video" ) $winSizeParam = '--window-maximized=yes ';
            if ( checkFileType($mediaFileWithPath) == "audio" ) $winSizeParam = '--window-minimized=yes ';
        */
        if (!file_exists($mediaFileWithPath)) statusReport("MEDIA NOT FOUND. Media File: ".$mediaName,true);
        if (file_exists(mpvWinBinary))
        {   // Win
            killMpvWin();
            $mpvRunLine='start "" /B '.mpvWinBinary.' --input-ipc-server='.mpvWinPipeName.' --no-osc --screen=1 --title="KVIZ - NVO ORKA" --no-terminal --hwdec=auto --cuda-decode-device=auto '.winSizeParam.$mediaFileWithPath;
        }
        else
        {   // nix
            killMpvNix();
            $mpvRunLine = mpvNixBinary.' --input-ipc-server='.mpvNixSocketName.' --no-osc --screen=1 --title="KVIZ - NVO ORKA" --no-terminal --hwdec=auto --cuda-decode-device=auto '.winSizeParam.$mediaFileWithPath.'> /dev/null 2>&1 &';
        }
        
        // $output=null;
        // $retval=null;
        // $result = exec($exeFile, &$output, &$retval);
        if (logUsedMedia(playedMediaFile,$mediaName)) { statusReport("OK",false); }
        pclose(popen($mpvRunLine,"r"));
    }

    function processInput()
    {
        $input = strip_tags($_SERVER['QUERY_STRING']);
        if (!(strlen($input)>0)) statusReport(badInputMsg.'no input',true);
        parse_str($input,$queryArray);
        $command = (!empty($queryArray['command'])) ? $queryArray['command'] : statusReport("No command specified",true);
        if (!inputFilter($command)) statusReport(badInputMsg.$command,true);
        if ($command==='listMedia') { listMediaCommand(); return; }
        if ($command==='playMedia')
        {
            $mName = (!empty($queryArray['medianame']))? $queryArray['medianame'] : statusReport("No media name specified",true);
            if (!inputFilter($mName)) statusReport(badInputMsg.$mName,true);
            playMediaCommand($mName);
            return;
        }
        statusReport(badInputMsg.'unknown command',false);
    }
    
    processInput();

?>
</body>
</html>
