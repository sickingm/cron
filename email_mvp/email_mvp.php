<?php	
ob_start();
echo '<pre>
Begin email_mvp.php<br />$_GET=';
print_r($_GET);
echo "</pre>";
	ob_flush();
$debug=false;
	mb_internal_encoding('UTF-8');
if($debug)error_reporting(E_ALL); 
	date_default_timezone_set("America/Chicago");	
	require_once($_SERVER["DOCUMENT_ROOT"]."/common/define_include_paths.inc");

	extract($_GET);
	require_once"utility_functions.php";
	
	$site='mvp';
	$db_name='mvp';
	require "creds_db.inc"; 
	require "mailer.inc";
	
//Open the database
	require_once($_SERVER["DOCUMENT_ROOT"]."/mvp/mvp_db_open.inc"); 
	
//Get the various dates to use
//Also check to make sure the stored event date is not in the past	
	require($_SERVER["DOCUMENT_ROOT"]."/mvp/mvp_db_check_date.inc"); 
	
// Check if today is a good day to send the email
	require("is_it_ok_to_mail_today.inc");
	
// Get the subject from the parameter list
require_once($_SERVER["DOCUMENT_ROOT"]."/mvp/mvp_get_config_vars.inc");	
	
//Fetch the two messages needed for the mailing.
//One ($html_message) is the html formatted version,
//the other ($plaintext_message) is plain text.
	$msg_prefix = "email_mvp_message";
	if(!$html_message = file_get_contents("$msg_prefix.html")) {
		echo "Can't open $msg_prefix.html";
		die();
	}
	if(!$plaintext_message = file_get_contents("$msg_prefix.txt")) {
		echo "Can't open $msg_prefix.txt";
		die();
	}
	identifier_comment(__FILE__." line #".__LINE__); 	
//Read in the Subject of the email  
//Load the subject of this email	
//require "email_mvp_subject.inc";
	
	$subject = str_replace('##DATE##',			$event_date_human,$subject);
	$subject = str_replace('##FULLDATE##',  $event_date_human,$subject);
	
	$html_message = str_replace('##SUBJECT##', $subject,           $html_message);
	$html_message = str_replace('##DATE##',    $event_date_human,  $html_message);

identifier_comment(__FILE__." line #".__LINE__); 	

/*
	$plaintext_message = str_replace('##SUBJECT##',$subject,					$plaintext_message);
	$plaintext_message = str_replace('##DATE##',	 $event_date_human,	$plaintext_message);
	
	$mail->Username = $mailer_username;
	$mail->Password = $mailer_password;       
	$mail->setFrom("noreply@sickingfamily.com","");
	$mail->addReplyTo("noreply@sickingfamily.com","");
	$mail->isHTML(true);
	$mail->Subject=$subject;  
*/	
	// Process each person's record
	$first = TRUE; //Flag used for printing out just the first instance
	
	//Read in full_name for all records 
	$sql ='SELECT 
	CONCAT(first_name," ",last_name) AS user, email
	FROM names';
	
	if(isset($only_user)) $sql .= " WHERE CONCAT(first_name,' ' ,last_name) = '$only_user'";
	
	$result=do_query($sql);
	
	if(isset($simulate))echo "<br />SIMULATING.....<br /><br />";
	// Cycle through records and output table
	while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
	extract($row);  // Here we obtain $full_name and $email     

	echo "<hr />";
	echo "Subject:$subject";
	echo "\n<br>Emailing to $user ($email)....<br />";
    
    $body = str_replace('##USER##',$user,$html_message);
    
	if($first){
		$first = FALSE;
		echo "<br />$body<br />";
	}
	if(isset($simulate) OR mail_it($email, $subject,$body)) echo "\n.....message sent to $user ($email)<br>";
	else echo "\n<br>Message could not be sent.  ";
	ob_flush();			
	}
	identifier_comment("END   ".__FILE__." line #".__LINE__);    
?>