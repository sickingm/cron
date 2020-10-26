<?php
// _cron_bugs_MTWT.php
// 
// All of the tasks that are to be done Monday through Thursday of Game week - presumably at 12:01am

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

echo "\nExecuting bugs_attendance_check.php\n";
include "bugs_attendance_check.php";

?>