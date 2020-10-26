<?php
/*
 *  Fetches the two messages needed for the mailing.
 *  One ($html_message) is the html formated version,
 *  the other ($plaintext_message) is plain text.
 */

	$msg_prefix = "email_signup_message";
    if(!$html_message = file_get_contents("$msg_prefix.html")) {
    	echo "Can't open $msg_prefix.html";
    	die();
    }

    if(!$plaintext_message = file_get_contents("$msg_prefix.txt")) {
    	echo "Can't open $msg_prefix.txt";
    	die();
    }

?>