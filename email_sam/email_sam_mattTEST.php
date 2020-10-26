<?php	
echo 'Begin email_sam.php<br /><pre>$_GET=';

print_r($_GET);
echo '</pre>';
extract ($_GET);

if(isset($simulate)){
	$sim=TRUE;
	echo "<br /> simulate is set<br />";
} else {
	$sim=FALSE;
	echo "< br/>simulate is not set";
}

	ob_flush();
	mb_internal_encoding('UTF-8');
	if($debug)error_reporting(E_ALL); 
	date_default_timezone_set('America/Chicago');	
	require_once($_SERVER['DOCUMENT_ROOT'].'/common/define_include_paths.inc');
	require_once'utility_functions.php';
    require_once 'mailer.inc'; 

	extract($_GET);
  
// Fetch the file containing the body of the message

	if(!$body = file_get_contents('email_sam_message.html')) {
		echo 'Can\'t open email_sam_message.html.html';
		die();
	}
 
	$subject = 'House Payment due on the 20th of this month';
	$user =    'Sam Sicking';
    $email =   'sickingmatt@gmail.com';
    $to=$email;
	$from='matt@sickingfamily.com';
 	
	if($sim)echo "<br />SIMULATING.....<br /><br />";
	echo '<hr />';
	echo "Subject: $subject";
	echo "\n<br>Emailing to $user ($email)....<br />";
	echo "<br />$body<br />";

	if($sim OR $sent=mail_it($to,$subject,$body,$from)){ 
		echo "\n<br />.....message sent " ;
		echo $sim ? "(simulated) " :"";
		echo "to $user (at $email) .<br >";
	} 
	else {
		echo "\n<br>Message could not be sent.  Mailer Error: <pre>" & error_get_last()['message'] & "/pre>";
	}
	echo '<hr />';
	ob_flush();			

	identifier_comment('END   '.__FILE__.' line #'.__LINE__);    


?>