<#
.SYNOPSIS
  Name: RequestLoader
  Handle user song requests.

  .DESCRIPTION
   Handle user song requests.

.PARAMETER InitialDirectory
  C:\Scripts\
  
.NOTES
    Updated: 2021-12-21     Initial release. //EZS
    Release Date: 2021-12-21
   
  Author: Erik Zalitis, erik@zalitis.se, +4673-941 22 74

# Comment-based Help tags were introduced in PS 2.0
#requires -version 2
#>

#----------------[ Declarations ]------------------------------------------------------

$InitDir = "C:\ezdatalinks\RequestLoader" # Change this is you run the script in another structure than the directories.

$ffmpeg_exe = "C:\ezdatalinks\ffmpeg.exe"
$sox_exe = "C:\Scripts\sox\sox.exe"
$Destination_dir = "C:\ezdatalinks\request"
$LogDir = $InitDir + "\Logs\"
$Temp_dir = $InitDir + "\Temporary"
$ReqID=$Destination_dir + "\templatefiles\intreq.mp3"
$Slots=2
$Password="cdE#4rFVbgT%"
$Domain="http://api.domain.tld"
$logfilefull=($LogDir + "RequestLoader_" + (Get-Date -Format "yyyy-MM-dd-HH-mm") + ".log")

#----------------[ Functions ]------------------------------------------------------

Function Logwrite ($message, $todisk = 1, $LD = $logfilefull) {
  $ts = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
  write-host ("[" + $ts + "] " + $message)

  $file = $LD

  if ($todisk -eq 1) { ("[" + $ts + "] " + $message) | out-file $file -append }
}

#----------------[ Script proper ]------------------------------------------------------


# Validate where in the hour we are at the moment

$clockposition=[int](Get-Date -Format "mm")



if ($clockposition -lt 30) { $NextSlot=1 } else { $NextSlot=2 }

# Create the folders if they do not exist.

if ((Test-path $InitDir) -eq $false) { New-Item -path $InitDir -ItemType "directory" }
if ((Test-path $Destination_dir) -eq $false) { New-Item -path $Destination_dir -ItemType "directory" }
if ((Test-path $Temp_dir) -eq $false) { New-Item -path $Temp_dir -ItemType "directory" }
if ((Test-path $LogDir) -eq $false) { New-Item -path $LogDir -ItemType "directory" }
#if ((Test-path ($LogDir + "Log.txt")) -eq $false) { New-Item -path ($LogDir + "Log.txt") -ItemType "file" }


# Main execution

#$SuccessFullFiles = New-Object System.Collections.ArrayList
#$FailedFiles = New-Object System.Collections.ArrayList


# Check the files in the slots. If they are older than 35 minutes, delete them

for ($num = 1 ; $num -lt $Slots+1 ; $num++)
{
   # logwrite("Checking status for " + "request" + $num + ".mp3" + ".")
 if ((Test-path ($Destination_dir + "\request" + $num + ".wav")) -eq $true) {

    $filetime=(Get-ChildItem ($Destination_dir + "\request" + $num + ".wav")).CreationTime
    $timespan = new-timespan -days 0 -hours 0 -minutes 35
    if (((get-date) - $filetime) -gt $timespan) { 
     logwrite ("File is stale and must be deleted")
     remove-item ($Destination_dir + "\request" + $num + ".wav") -Force

    } else 
    { 
        #logwrite ("File is fresh and will not be deleted")
     }
 
  } else { 
  #logwrite ("... not present. Slot " + $num + " is empty.")
   }
   
   }

# Each hour works like this on the broadcast automation server:
#  00:00 - 00:09 - Requests will be filled into slot 1
#  00:09 - 00:30 - Script will not perform any actions. Slot 1 may play and the tune end between 00:10 - 00:30.
#  00:30 - 00:39 - Requests will be filled into slot 2.
#  00:39 - 00:59 - Script will not perform any actions. Slot 2 may play and the tune end between 00:40 - 00:00.

# Time: - if we are NOT between 00:00 - 00:09 or between 00:30 - 00:39 - then the script will terminate here.

#if (($clockposition -ge 9 -and $clockposition -lt 30) -or ($clockposition -ge 39 -and $clockposition -le 59))

if (($clockposition -ne 9) -and ($clockposition -ne 53))
{
   #logwrite("Script is running at minute " + $clockposition + " and is now under curfew. No actions will be taken at this time.")
    exit
}


# If we are between 00:00 - 00:09 or between 00:30 - 00:39

#logwrite("Script is running at minute " + $clockposition + ". We're clear to check and load slot " + $NextSlot + ".")

    # Check if the next slot has a file in it. If so, the script will terminate. The file will not be old, this has already been checked in the beginning of the script.

    if (Test-path ($Destination_dir + "\request" + $NextSlot + ".wav")) 
    {
        #logwrite("The file is in place in slot " + $NextSlot + ". The script will terminate.")
        exit 
    }


    # Check for the next request if none, the script will terminate here.

    $url=$Domain + "/radio/getnextrequest/";
$params="{
`"Password`": `"" + $Password + "`",
`"StationID`": `"1`"
}";


try
{
    $CheckStatus=Invoke-WebRequest $url -Method Post -Body $params -UseBasicParsing -ContentType "application/json; charset=utf-8"

  #  $params|out-file "d:\req.txt"
#$uploadstatus.Content

$NextSong=ConvertFrom-Json ($CheckStatus.Content)

if ($null -eq $NextSong.Requests[0].TrackID) 
{ 
#logwrite ("There are no new requests. Script will now terminate.")
exit
 }

 
Logwrite ("******************* *************************** Started.")
Logwrite ("RequestLoader version 1.0, created by Erik Zalitis on ericade.radio. erik@zalitis.se.")

$TrackID=$NextSong.Requests[0].TrackID
$StationID=$NextSong.Requests[0].StationID
$Path=$NextSong.Requests[0].Path
$nameofrequester=$NextSong.Requests[0].nameofrequester
$greeting=$NextSong.Requests[0].greeting


$washedartist=$NextSong.Requests[0].Fullartist|Foreach-Object {
    $_ -replace '&#37;3A', ':' `
       -replace '&#37;5C', '\' `
       -replace '&#37;20', ' ' `
       -replace '&#37;C3&#37;B6', 'Ã¶' `
       -replace '&#37;', '&'

    } 

$washedtitle=$NextSong.Requests[0].Title|Foreach-Object {
    $_ -replace '&#37;3A', ':' `
       -replace '&#37;5C', '\' `
       -replace '&#37;20', ' ' `
       -replace '&#37;C3&#37;B6', 'Ã¶'
    } 

$ToPlay=($washedartist + " - " + $washedtitle)

}
catch
{
    logwrite ("[ERROR] Failed to query for the next tune. Script will now terminate." + $_)
    exit

}

    # If one is returned, create the composite file <StationID> + <Requested tune> at call it request<slotnumber>.mp3

Logwrite("Now creating the playfile for slot " + $NextSlot + " to play " + $ToPlay + ". Requester " + $nameofrequester + " sent the greeting " + $greeting + ".")


try
{
$sourcepath=$path|Foreach-Object {
        $_ -replace '&#37;', '%'
    } 
    $sourcepath=[System.Web.HttpUtility]::UrlDecode($sourcepath)

    $ReqFileFull="request"+$NextSlot+".wav"

    #gc "$ReqID","$sourcepath" -Encoding Byte -Read 512 | sc ($Destination_dir+"\"+$ReqFileFull) -Encoding Byte

        # Sox will fix the tune, so it will properly fade out. This will allow adding a StationID after the song.

        $Trim = $CueIn # Cut off x seconds in the beginning (CUE IN)
        $FADE_IN_L = "0" # Fade in from the new beginning
        $FADE_OUT_L = "4" # Fade out from the end
        $LENGTH = $CueOut # Fade point (CUE OUT)
    
        $out = ($Temp_dir + "\" + $ReqFileFull)
        if (test-path $out) { remove-item $out -force -Confirm:$false }
    
        $cmdline = "$sox_exe `"$sourcepath`" `"" + $out + "`" trim " + $Trim + " fade " + $FADE_IN_L + " " + $LENGTH + " " + $FADE_OUT_L
    
        try {
            logwrite("Command line: " + $cmdline + ".")
            invoke-expression -command $cmdline
        }
        catch {
            logwrite("[ERROR] Failed to create the file." + $_)
            exit
        }

    #$fromnames_all = ($ReqID + "|" + $sourcepath)
    $cmdline =  "$ffmpeg_exe -i `"" + $ReqID + "`" -i `"" + $out + "`" -metadata title=`""+ $washedtitle + "`" -metadata artist=`"" + "Listener-request: " + $washedartist + "`" -filter_complex '[0:0][1:0]concat=n=2:v=0:a=1[out]' -map '[out]' `"" + ($Destination_dir+"\"+$ReqFileFull) + "`" "
    #Write-Verbose "`t CMD: $cmdline"


    try {
        logwrite("Command line: " + $cmdline + ".")
        invoke-expression -command $cmdline
    }
    catch {
        logwrite("[ERROR] Failed to create the file." + $_)
        exit
    }
    


}
catch
{
    logwrite("[ERROR] Failed to create the file. Script will now terminate.")
    exit
}

# Advance the request queue with updateplayedrequest.

    $url=$Domain + "/radio/updateplayedrequest/";
$params="{
`"Password`": `"" + $Password + "`",
`"StationID`": `""+$StationID+"`",
`"Slot`": `""+$NextSlot+"`",
`"TrackID`": `"" + $TrackID + "`"
}";


try
{
 
 $CheckStatus=Invoke-WebRequest $url -Method Post -Body $params -UseBasicParsing -ContentType "application/json; charset=utf-8"

  # For debugging purposes:
  #$params|out-file "d:\req.txt"
  #$CheckStatus.Content|out-file "d:\resp.txt"

$CallResult=ConvertFrom-Json ($CheckStatus.Content)

if ($CallResult.subcode -eq "TRUE") { logwrite ("The tune has been set as played in the API."); } else { logwrite ("[ERROR] Failed to advance the tune.") }


}
catch
{
    logwrite ("[ERROR] Failed to advance the tune. Unknown error. Given EC: " + $_ + ".")

}


# Go to sleep - all done.


Logwrite ("******************* *************************** Stop.")