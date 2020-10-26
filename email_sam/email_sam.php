<?php	
echo "Begin email_sam.php<br /><pre>\n_GET=";
	
print_r($_GET);
echo "</pre> ......<br />\n";
extract ($_GET);
ob_flush();

if(isset($simulate)){
	$sim=TRUE;
	echo "<br /> simulate is set<br />";
} else {
	$sim=FALSE;
	echo "< br/>simulate is not set<br />";
}
	ob_flush();
	mb_internal_encoding('UTF-8');
	if ($debug) error_reporting(E_ALL);
	date_default_timezone_set("America/Chicago");
	require_once $_SERVER["DOCUMENT_ROOT"] . "/common/define_include_paths.inc";
	require_once "utility_functions.php";
	echo "About to load mailer.inc<br />";
	require_once "mailer.php" ;
	require_once "creds_db.inc";
// Fetch the file containing the body of the message
	if(!$body = file_get_contents('email_sam_message.html')) {
		echo 'Can\'t open email_sam_message.html.html';
		die();
	}
 
	$subject = 'House Payment due on the 20th of this month';
	$user = 'Sam Sicking';
	$to =   'samsicking0510@gmail.com';
	$to =   'matt@sickingfamily.com,samsicking0510@gmail.com';
	$from = 'matt@sickingfamily.com';
	
	if($sim)echo "<br />SIMULATING.....<br /><br />";
	echo '<hr />';
	echo "Subject: $subject";
	echo "\n<br>Emailing to $user ($to)....<br />";
	echo "<br />$body<br />";

	if($sim OR $sent=mail_it($to,$subject,$body,$from)){ 
		echo "\n<br />.....message sent " ;
		echo $sim ? "(simulated) " :"";
		echo "to $user (at $to) .<br >";
	} 
	echo '<hr />';
	ob_flush();			

	identifier_comment('END   '.__FILE__.' line #'.__LINE__);    


?>