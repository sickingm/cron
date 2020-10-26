<?php	

	if($debug)error_reporting(E_ALL); 
	mb_internal_encoding('UTF-8');
	date_default_timezone_set("America/Chicago");	
	require_once($_SERVER["DOCUMENT_ROOT"]."/define_include_paths.inc");

	extract($_GET);

    require_once"utility_functions.php";

    require_once "PHPMailerAutoload.php";  // Brings in PHPMailer 
    
    $site='signup';
	$db_name=$signup;
    require "creds_db.inc"; 
    require "creds_mail.inc"; 
    require "email_signup_messages.inc";    // Get the html and plaintext messages to be used.
    $html_message = str_replace('##SIGNUP##',$signup,$html_message);
    $html_message = str_replace('##SUBJECT##',$subject,$html_message);
    $plaintext_message = str_replace('##SIGNUP##',$signup,$plaintext_message);

    $mail = new PHPMailer;      // Define mail object to be used  ***()****
    $mail ->SMTPDebug = 0;      // Enable verbose debug output
    $mail->isSMTP();            // Set mailer to user SMTP
    $mail->HOST = $mailer_host; // Identify mail server
    $mail->SMTPAuth = true;     // Enable SMTP Authentication
	$mail->SMTPSecure = $SMTPSecure;
    $mail->Port=$mailer_port;  
    $mail->Username = $mailer_username;
    $mail->Password = $mailer_password;       
    $mail->setFrom("noreply@sickingfamily.com","");
    $mail->addReplyTo("noreply@sickingfamily.com","");
    $mail->isHTML(true);
    $mail->Subject=$subject;  
   
// Open database
 	if (!connect_and_select()){
        $errmsg = "\n<table border='1'><tr><th colspan=4>Could not connect to database</br>";
        $errmsg .= "\nError opening '<span style='color:red'>$signup</span>' database<br>\nNo such signup available.  Please check your spelling.";
        $errmsg .= $debug ? mysqli_error($db_link) : "";
        $errmsg .= "</th></tr>";
        errmsg($errmsg, array('signup'=>'none'),"&nbsp;",TRUE);
	}  

if($debug) echo <<<MSG
\nHOST: {$mail->HOST}<br>
\nUsername: {$mail->Username}<br>
\nPassword: {$mail->Password}<br>
\nFrom: {$mail->From}<br>
\nSubject: {$mail->Subject}<br>
MSG;

if ($debug)echo "\nAbout to process db records<br><br>".__FILE__ ." @line #".__LINE__;
// Process each person's record

//Read in full_name for all records 
    $sql ='SELECT 
            CONCAT(first_name," ",last_name) AS user, email
            FROM names';
    $result=do_query($sql);
	
// Cycle through records and output table
	$first=TRUE;
    while ($row = mysqli_fetch_array($result, MYSQLI_BOTH)) {
    	if($first && $debug)echo '\n<br>'.__FILE__ .'@line #'.__LINE__;
        extract($row);  // Here we obtain $full_name and $email     
        $mail->addAddress($email,"$user ($email)"); 
        $mail->Body=str_replace('##USER##',$user,$html_message);
        $mail->AltBody=str_replace('##USER##',$user,$plaintext_message);
		$adrs = $mail->getToAddresses();
		if($debug){
			echo '\n<br>Addressees: <pre>';
			print_r($adrs); 
			echo '\n</pre>';
		}
    if(!$mail->send()) {
        echo <<<ERR
        \n\n>Message could not be sent.  Mailer Error: <pre>{$mail->ErrorInfo}</pre>
        \n\n\n
ERR;
    } 
		else echo "\n.....message sent to $user ($email)!";
        $mail ->clearAddresses(); //Remove currect address so the next email sent is only to the next recipient.
        $first = false;
    }
    identifier_comment("END   ".__FILE__." line #".__LINE__);
