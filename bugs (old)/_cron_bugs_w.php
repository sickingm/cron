<?php
/*  
	+------------------+
	+ _cron_bugs_F.php +
	+------------------+
 
	All of the tasks that are to be done on Wednesday only - presumably at 12:01am
	
*/

$site='cron'; 		
$db="sickingf_";	
$db_name="bugs";

if(!empty($_SERVER["DOCUMENT_ROOT"]))$doc_root= $_SERVER["DOCUMENT_ROOT"];
else $doc_root = getcwd()."/public_html";  //Is running under cron, so use current work directory/public html
include $doc_root . "/common/initialize.php"; 
//
////////////////////////////////////////////////////////
//
// Put includes to all daily tasks here
//
////////////////////////////////////////////////////////

echo "\nExecuting bugs_new_manager_alert.php<br>\n";
include "bugs_player_fees_owed.php";

?>