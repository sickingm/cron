<?php
/*

This routine is called on Wednesday during the season (the day before each game))
It sends a reminder to each player who hasn't paid his fees yet to bring a check with him to the game

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
		                          
    The seasons table contains:
        year 	     year(4)	     No	0000
        league 	     text	         No	
        team_number  smallint(6)	 No	0
        number_of_games int(11)	     No	14
        fee 	     decimal(10,2)   No	0.00
        fee_comments varchar(256)	 No	
		
*/

connect_and_select();

// Get the fee info for he latest season
$result=do_query('
	SELECT year AS season, fee, fee_comments
	FROM seasons
    WHERE year=(SELECT MAX(year) FROM seasons)    
');
$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
extract($row);  //Creates the variable $year, $fee, fee_comments

// Find anyplayers who have not paid
$result=do_query("
    SELECT first_name, last_name, email1, email2
        FROM seasons, rosters, players
        WHERE rosters.year_ptr = '$season'
        AND seasons.year = '$season'
        AND players.player_id = rosters.player_ptr
        AND STATUS = 'unpaid'
");


$nr=mysqli_num_rows($result);
if($nr==0) exit("No unpaid fees");  //No one needs a paymen reminder, so exit.

// There is at ;leat one player owing the fees so grab all of the details, looping through all players who owe the fee.

$spaces = str_repeat("&nbsp;",10);
while ($row=mysqli_fetch_array($result,MYSQLI_ASSOC)){
	
	extract ($row);  // Creates $next_game_date and $mysql_game_date,$game_time, $field, etc;

	$body = "
\n<html>
\n<head>
\n<title>Bugs $season Season Team Fees</title>
\n<style>
\n.bugs {color:#CD0000}
\nbody {font-family: Comic Sans MS; font-size: 12pt}
\nhr {border:3px solid #CD0000;}
\n</style>
\n</head>
\n<body>
\n<hr>
\n$first_name, <br><br>
\nThis is an automated message reminding you that you your team fees (<span class='bugs'>\$$fee</span>) are due for the $season season. 
\n<br />($fee_comments)
\n<br />
\n<br />You can bring the fees to the next scheduled game or mail them to 
\n<br /><span class='bugs'>
\n<br />$spaces Matt Sicking
\n<br />$spaces 11892 Archerton Drive
\n<br />$spaces Bridgeton, MO 63044
\n<br /></span>
\n<br /> Thanks,
\n<br />
\n<br /> Matt
</p>
";

	$to_list  = $email1;
	$to_list = add_email($email2, $to_list);
	$subject  = "Bugs $season Season Team Fees Due"; 
	$headers  = "MIME-Version: 1.0 \r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1 \r\n";
	$headers .= "From: matt@sickingfamily.com\r\n";
    $headers .= "Cc: matt@sickingfamily.com"; // Always copy Matt

	if (mail($to_list, $subject, $body, $headers)) {
		echo "Message successfully sent!";
		echo "\n<br>(Bugs fees dues)";
		echo "\nPlayer: $first_name $last_name<br>";
	}	
	else {
		echo "Message delivery failed...";
		echo "\n<br>(Bugs fees dues)";
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