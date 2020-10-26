<?php

/* firstOrThirdTuesda// Returns true (and therefore allows the emails to be sent) whenever:
	- there exists a global $_GET variable called "forced"
	- It is the first Tuesday of the month
	- It is the third Tuesday of the month
	UNLESS the date of the next dance is more than 10 days away
*/
// Note this Tuesday correspopnds to the date that the email is sent, not the date of the workshop
		if(isset($_GET["force"])) return;

	if($datedif>10){
			echo '<br />Too far in the future;  Exiting....<br />';
			die();
	}	
		
		$t1 = strtotime("first Tuesday of this month ");
		$t2 = strtotime("third Tuesday of this month");
		$today = strtotime("today");
		if($t1 == $today or $t2==$today) return;
		
		echo "<br />Wrong day.  Exiting....";
		die();
	
?>

