<?php
/*

This routine is called on Fridays during the season
It looks sends an alert to the newest Manager of the weks:
	- Reminds him of his MOTW duties
	- Reminds him to pick up the score book from Matt
	- Lets him know about the upcoming email messages that he will be copied on

	The players table contains:
		player_id  
		first_name   	 
		last_name  		 
		phone1  	
		phone2  	
		email1  	
		email2   
		city  
		yob (year of birth)
	
	The rosters table contains:
		roster_id   bigint(20)	 
		year_ptr  	year(4)
		player_ptr  bigint(20)
		status  	enum('paid','unpaid','sub')
		
	The games table contains:
		game_id		bigint(20)		Yes	NULL
		season_ptr	bigint(20)		Yes	0
		date		date			Yes	0000-00-00
		time		time			Yes	00:00:00
		field		varchar(4)		Yes	9
		hv			enum('H', 'V')	Yes	H
		manager_ptr	bigint(20)		Yes	0
		opponent	varchar(40)		Yes	
		wlt			enum('W', 'L', 'T', 'R', 'X')	Yes	NULL
		runs_for	int(11)			Yes	0
		runs_against int(11)		Yes	0
		
*/

$spaces = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$hvarray=array("H"=>"Home","V"=>"Visiting");

connect_and_select();
	
// Get the date of this week Thursday and check if there is a game
$this_thursday = date("Y-m-d",strtotime("This Thursday")); 
$year=date('Y');

$result=do_query("
	SELECT 
		game_id,
		date_format(time,'%l:%ipm') AS game_time,
		field,
		first_name AS mgr_first, last_name AS mgr_last,
		email1 AS mgr_email1, email2 AS mgr_email2,
		date AS mysql_game_date, 
		date_format(date,'%M %e, %Y') AS next_game_date, 
		opponent, hv
	FROM games, players 
	WHERE date between now() and adddate(now(),7) 
	AND manager_ptr = player_id
	ORDER BY games.date 
");
$nr=mysqli_num_rows($result);
if($nr==0) exit("No game scheduled this Thursday: $this_thursday");  //No game scheduled this week  So exit.

// There is a game so grab all of the details, looping through all games (in case there's a doubleheader)
while ($row=mysqli_fetch_array($result,MYSQLI_ASSOC)){
	
	extract ($row);  // Creates $next_game_date and $mysql_game_date,$game_time, $field, etc;

	$body = "
\n<html>
\n<head>
\n<title>Manager of the Week?</title>
\n<style>
\n.bugs {color:#CD0000}
\nbody {font-family: Comic Sans MS; font-size: 12pt}
\nhr {border:3px solid #CD0000;}
\n</style>
\n</head>
\n<body>
\n<hr>
\n$mgr_first, <br><br>
\nThis is an automated message reminding you that you are the designated Bugs 
\n<span class='bugs'>Manager of the Week</span> for the upcoming game:<br>
\n$spaces Date: <span class='bugs'>$next_game_date</span><br>
\n$spaces Time: <span class='bugs'>$game_time</span><br>
\n$spaces Opponent: <span class='bugs'>$opponent</span><br>
\n$spaces We are the <span class='bugs'>{$hvarray[$hv]}</span> team.<br>
</p>
";

	$body .= "
As a reminder, here is a summary of your responsibilities as Manager of the Week:
\n<br>$spaces- Pick up the scorebook from Matt today after he is done entering the 
\nbatting statistics into the website. 
\n<br>$spaces- Ensure we have enough players for the upcoming game, including finding subs, if necessary.
\n<br>$spaces- Prepare the lineup for the game.
\n<br>$spaces- Determine the defensive fielding assignments for the game.
\n<br>$spaces- Manage the rotation of fielding assignments during the game, as required.

\n<br><br>If you cannot attend the game this week, then it is <u>your responsibility</u> to find another 
player to swap weeks with. &nbsp;When you do, let Matt know who it is so that he can change the schedule 
\ndatabase accordingly. &nbsp;If your game rains out, you still have the responsibility to manage that rescheduled game
\n(in late July or early August).

\n<br><br>To assist you with your duties of ensuring that we have at least ten players at the game, you should check the 
\n<a href='http://www.sickingfamily.com/bugs/attendance/'>Attendance</a> page on the team website. 
\n&nbsp;Beginning the Monday before the game, automated reminders will be emailed to every player on the 
\nroster who has not yet indicated his availability to attend the game. &nbsp;You will be CC'd on each of these reminders.
<br><br>
There is also an Excel spreadsheet tool available <a href='http://www.sickingfamily.com/bugs/attendance/lineup_tool.xls'>here</a>
which you may find useful in plannning your lineup for the game.
\n<p>Thanks</p>Matt
\n<hr>
\n</body>
\n</html>
";

	$to_list  = "matt@sickingfamily.com";  // Always copy Matt
	$to_list = add_email($mgr_email1, $to_list);
	$to_list = add_email($mgr_email2, $to_list);
	$subject  = "Manager Of The Week -- Thursday $next_game_date, $game_time"; 
	$headers  = "MIME-Version: 1.0 \r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1 \r\n";
	$headers .= "From: matt@sickingfamily.com";

	if (mail($to_list, $subject, $body, $headers)) {
		echo "Message successfully sent!";
		echo "\n<br>Bugs New Manager of The Week alert.";
		echo "\nDate: $this_thursday<br>";
		echo "\nTime: $game_time<br>";
		echo "\nManager: $mgr_first $mgr_last<br>";
	}	
	else {
		echo "Message delivery failed...";
		echo "\n<br>(Bugs Attendance Check)";
		echo "\n<br>Subject: <span class='bugs'>$subject</span>";
		echo "\n<br>To List: <span class='bugs'>$to_list</span>";
		echo "\n<br>headers: <span class='bugs'>$headers</span>";
		echo "\n<br>Body:<br>$body";
	}	
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////
function add_email($to,$addr) {
	if(empty($addr)) return $to; //If no email address then return TO list as is
	if(empty($to)) return $addr; //If TO lsit is currently empty then TO list becomes current address
	else return $to.", ".$addr;  // Otherwise append current address to TO list separated by comma
}