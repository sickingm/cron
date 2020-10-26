<?php
$to = "matt@sickingfamily.com";
$subject = "Test Message -- ".date(DATE_ATOM);
$body = "see contents of happy_birthdy.php";
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= 'From: DBC@sickingfamily.com';
	
(mail($to, $subject, $body, $headers)) 
?>