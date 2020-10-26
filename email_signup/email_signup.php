<?php	
echo '<pre>
Begin email_signup.php<br />$_GET=';
print_r($_GET);
echo "</pre>";
ob_flush();

	mb_internal_encoding('UTF-8');
	if($debug=='on')error_reporting(E_ALL); 
	date_default_timezone_set("America/Chicago");	
	require_once($_SERVER["DOCUMENT_ROOT"]."/common/define_include_paths.inc");
    require_once "mailer.inc";
	
	extract($_GET);
	If (empty($signup)) {
		Echo '\n<br><strong>No event name given.  Aborting...<br>';
		die();
	}
	
	if(isset($force)) {
		$force=(int) filter_var($force, FILTER_VALIDATE_BOOLEAN);
	} else $force = false;

ob_flush();

    require_once"utility_functions.php";

    require_once "PHPMailerAutoload.php";  // Brings in PHPMailer 
    
    $site='signup';
    $db_name=$signup;
    require "creds_db.inc"; 
    
// Open database
 	if(!$db_link=connect_and_select($db_host, $db_user, $db_password, $full_db_name)) {
        $errmsg =  "\n<table border='1'><tr><th colspan=4>Could not connect to database</br>";
        $errmsg .= "\nError opening '<span style='color:red'>$signup</span>' database<br>No such signup available.  Please check your spelling.</br>";
        $errmsg .= $debug ? mysqli_error($db_link) : "";
        $errmsg .= "</th></tr>";
        errmsg($errmsg, array('signup'=>'none'),"&nbsp;",TRUE);
	}     


// Get the subject from the parameter list
require_once($_SERVER["DOCUMENT_ROOT"]."/signups/signups_get_config_vars.inc");	
// Get date if next event.  
// If it is in the past calculate the next one. 
// If it's a week or more in the future abort because it's too soon to send out the email.   
	require($_SERVER["DOCUMENT_ROOT"]."/signups/signups_db_check_date.inc"); 
	if($datedif>6 && !$force){
		echo "<hr>Next date is too far in the future.  Exiting....<hr>";
		die();
	}

 /*
 *  Fetch the two messages needed for the mailing.
 *  One ($html_message) is the html formated version,
 *  the other ($plaintext_message) is plain text.
    if(!$plaintext_message = file_get_contents("$signup/$msg_prefix.txt")) {
    	echo "Can't open $msg_prefix.txt";
    	die();
    } 
    
    $plaintext_message = str_replace('##SIGNUP##',$signup,$plaintext_message);
 
 */
 
	$msg_prefix = "email_signup_message";
    if(!$html_message = file_get_contents("$signup/$msg_prefix.html")) {
    	echo "Can't open $msg_prefix.html";
    	die();
    }

    $subject = str_replace('##FULLDATE##',  $event_date_human,$subject);
    $subject = str_replace('##STARTTIME##', $start_time,      $subject);
    $subject = str_replace('##ENDTIME##',   $end_time,        $subject);
    
    $html_message = str_replace('##FULLDATE##', $event_date_human,$html_message);
    $html_message = str_replace('##STARTTIME##',$start_time,      $html_message);
    $html_message = str_replace('##ENDTIME##',  $end_time,        $html_message);
    $html_message = str_replace('##SIGNUP##',   $signup,          $html_message);
    $html_message = str_replace('##SUBJECT##',  $subject,         $html_message);
    $html_message = str_replace('##ENDTIME##',  $end_time,        $html_message);
 
// Process each person's record

//Read in full_name for all records 
    $sql ="SELECT 
            CONCAT(first_name,' ',last_name) AS user, email
            FROM names";
    if(isset($only_user))$sql .= " WHERE CONCAT(first_name,' ',last_name) = '$only_user'";
    $result=do_query($sql);
    $first = true;  // flag to handle printing of first email for quality checking
		
// Cycle through records and output table
    while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
			extract($row);  // Here we obtain $full_name and $email     
	echo "<hr />";
	echo "\n<br>Emailing to $user ($email)....<br />";
			$body=str_replace('##USER##',$user,$html_message);
			if($first) {
				echo "Subject: ".$subject;
				echo '<br />'. $body.'<br />';
				$first =false;
			}

			mail_it($email, $subject, $body);
            echo"<hr><hr>";
			ob_flush();			
	echo "<hr />";
    }
    identifier_comment("END   ".__FILE__." line #".__LINE__);    
?>   