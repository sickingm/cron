<?php
$site='cron'; 		
$db="sickingf_";	
$db_name="family";

if(!empty($_SERVER["DOCUMENT_ROOT"]))$doc_root= $_SERVER["DOCUMENT_ROOT"];
else $doc_root = getcwd()."/public_html";  //Is running under cron, so use current work directory/public html
include $doc_root . "/common/initialize.php"; 
//
connect_and_select();

$year = date("Y"); //Current year
	
$result=do_query("
	SELECT 
        first_name, last_name, month(birthday) as bd_month, day(birthday) as bd_day
	FROM 
        members 
");

$top = 
'BEGIN:VCALENDAR
PRODID:-//WebCalendar-vcs-v1.2.3
VERSION:1.0';

$bottom = '
END:VCALENDAR';

$filename = "sickingfamily_birthdays.ics";
if (!$f = fopen($filename, 'w')) {
     echo "<br />Cannot open file ($filename)<br />";
     exit;
}

fwrite($f, $top);

while ($row=mysqli_fetch_array($result,MYSQLI_ASSOC)){
    $row=mysqli_fetch_array($result,MYSQLI_ASSOC);
	extract ($row);
    
$bd = sprintf('%s%02u%02u',$year,$bd_month,$bd_day);
$event = "
BEGIN:VEVENT
UID:-SICKINGFAMILY-COM-CALENDAR-DBC-0000000000
SUMMARY;ENCODING=QUOTED-PRINTABLE:$first_name $last_name's Birthday
DESCRIPTION;ENCODING=QUOTED-PRINTABLE:DESCRIPTION:$first_name $last_name's Birthday
CLASS:PUBLIC
DTSTART;VALUE=DATE:$bd
DTEND:{$bd}T040000Z
RRULE:YD1 
END:VEVENT"  ;



    fwrite($f, $event);

}

fwrite($f, $bottom);
fclose($f);
?>