<?php
/*

This routing is called daily from Mon thru Fri during the season by _cron_bugs_mtwt.php
It looks for all roster players who haven't specified whether they will be 
attending the game, and sends them an email list.

It also sends emails to players who responded "Maybe" but only if the total 
number of yeses is less than 10.

Copies of all emails also go to the manager of the week.

	The attendance table contains:
		date_ptr   	date
		player_ptr  bigint(20)
		coming  	enum('U','Y','N','M')
		comments  	text
		last_change	timestamp
		
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

These statistics are gathered at the beginning of the program (their meaning shouldbe obvious from their names):

	$nr (Number of rows in schedule table.  I.e, number of remaining games this year.
	
	$num_players
	
	$roster_yes
	$roster_no 
	$roster_maybe
	$roster_unknown
	$roster_responded = $roster_yes + $roster_no + $roster_maybe;
	
	$subs_yes		
	$subs_no  		
	$subs_invited
	$subs_unneeded
	$subs_responded = $subs_yes + $subs_no + $subs_unneeded;
	
	$total_yes
	$total_no
	$total_maybe
	$total_unknown
	$total_responded = $roster_responded + $subs_responded;
	
	$roster_total 
	$subs_total
		
*/

	connect_and_select();  //Access the data base
	
	
//debug_on();	
// Get the date of this week Thursday and check if there is a game
	$this_thursday = date("Y-m-d",strtotime("This Thursday")); 
    $year=date('Y');

	$game_result=do_query("
		SELECT 
			game_id,
			date_format(time,'%l:%ipm') AS game_time,
			field,
			first_name AS mgr_first, last_name AS mgr_last,
			email1 AS mgr_email1, email2 AS mgr_email2,
			date AS mysql_game_date, 
			date_format(date,'%M %e, %Y') AS next_game_date 
		FROM games, players 
		WHERE date between now() and adddate(now(),7) 
		AND manager_ptr = player_id
		ORDER BY games.date, games.time
	");
	$nr=mysqli_num_rows($game_result);
	if($nr==0) exit("No game scheduled this Thursday: $this_thursday");  //No game scheduled this week  So exit.

// There is a game so grab all of the details (While loop is required for doubleheaders)
	while ($row=mysqli_fetch_array($game_result,MYSQLI_ASSOC)) {
	 
		extract ($row);  // Creates $next_game_date and $mysql_game_date,$game_time, $field, etc;
/*
	Get the status (sub, paid, unpaid) availability (Y/N/M/U) for all players
	Note that the meaning of the 'M' and 'U' availabilities differ between
	roster players and subs:
	For roster players they mean "Maybe" and "Unknown"
	For subs they mean "Invited" and "Uninvited"
*/
		$player_result = do_query ("
			SELECT *  
			FROM (
			   SELECT player_id, first_name, last_name, email1, email2, status
			      FROM players, rosters  
			      WHERE player_id =player_ptr  
			      AND year_ptr ='$year'  
			   ) AS current_roster   
			   LEFT JOIN (
			      SELECT player_ptr, coming from attendance 
			      WHERE game_ptr='$game_id' 
			   ) AS availability 
			   ON current_roster.player_id = availability.player_ptr
			   ORDER BY last_name, first_name
			   ");
		$num_players = mysqli_num_rows($player_result);
			
// Initialze the summary variables
		$p=0;  // Number of confirmed players
		$roster_yes = 0;
		$roster_no = 0;
		$roster_maybe = 0;
		$roster_unknown = 0;
		$subs_yes = 0; 		
		$subs_no = 0; 		
		$subs_invited = 0;
		$subs_unneeded = 0;
		
//debug_on();
		
// Cycle through each player and tally the availabilities
		while ($row = mysqli_fetch_array($player_result,MYSQLI_ASSOC) ) {
// In case no attendance records exist yet, must manually set attendance status to 'unknown'
			if(empty($row['coming'])) {  
				$row['coming']='U';
				$row['player_ptr']=$row['player_id'];
			}
			$data[$p++]=$row;
			if ($row['coming'] == "Y") { 
				if ($row['status'] == 'sub') $subs_yes++;  
				else $roster_yes++;// only two statuses: sub & roster, so this must be a roster player
			}
			else if ($row['coming'] =='M') {
				if ($row['status'] == 'sub') $subs_invited++;
				else $roster_maybe++;
			}
			else if ($row['coming'] =='N') {
				if ($row['status'] == 'sub') $subs_no++;
				else $roster_no++;
			}
			else { // remaining entries are unknowns (U's or NULLs)
db_text ("subs unknown input");
db_echo ("coming", $row['coming']);
db_echo ("status", $row["status"]);
db_echo ("last_name",$row["last_name"]);
				if ($row['status'] == 'sub') $subs_unneeded++;
				else $roster_unknown++;
			}
		}
			
debug_off();
		$total_yes = $roster_yes + $subs_yes;
		$total_no = $roster_no + $subs_no;
		$total_maybe = $roster_maybe + $subs_invited;
		$total_unknown = $roster_unknown + $subs_unneeded;
		
		$roster_responded = $roster_yes + $roster_no + $roster_maybe;
		$subs_responded = $subs_yes + $subs_no + $subs_invited;
		$total_responded = $roster_responded + $subs_responded;
		
		$roster_total = $roster_yes + $roster_no + $roster_maybe + $roster_unknown; 
		$subs_total = $subs_yes + $subs_no + $subs_invited + $subs_unneeded; 
		
db_echo("roster_yes",$roster_yes,0);
db_echo("subs_yes",$subs_yes,0);
db_echo("total_yes",$total_yes);
db_echo("roster_no",$roster_no,0);
db_echo("subs_no",$subs_no,0);
db_echo("total_no",$total_no);
db_echo("roster_maybe",$roster_maybe,0);
db_echo("subs_invited",$subs_invited,0);
db_echo("total_maybe",$subs_invited);
db_echo("roster_unknown",$roster_unknown,0);
db_echo("subs_unknown",$subs_unneeded, 0);
db_echo("total_unknown",$total_unknown);
db_echo("roster_responded",$roster_responded,0);
db_echo("subs_responded",$subs_responded, 0);
db_echo("total_responded",$total_responded);
db_echo("num_players",$num_players);
db_echo("data",$data);
		
// Do we have enough to field a team?
		$to_list = "";
		$to_list = add_email($to_list, $mgr_email1);
		$to_list = add_email($to_list, $mgr_email2);
	
		$body = "
			\n<html>
			\n<head>
			\n<title>Are You Coming To The Game?</title>
			\n<style>
				body {font-family: Comic Sans MS; font-size: 12pt;}
				hr {border:3px solid #CD0000;}
				li {list-style-type: none; margin-left: 10; margin-top:0; margin-bottom:0;}
				td {text-align: center;}
				ul {margin-top:0; margin-bottom:0;}
				.bugs {color:#CD0000; margin-top:0; margin-bottom:0;}
				.subtle-table td{color:#CD0000; text-align:left;padding-top: 0; padding-bottom: 0}
				.subtle-table th {color:#000; text-align: right; font-weight:normal; padding-top: 0; padding-bottom: 0;}
				.summary, .summary * {font-weight:normal; padding-top: 0; padding-bottom: 0; }
				.summary td {color:#CD0000; }
			</style>
			\n</head>
			\n<body>
			\n<hr>This is an automated message regarding the next <span class='bugs'>bUGS</span> Men’s Softball game:
		";
	
		$body .= "
			<table class='subtle-table'>
				<tr><th>Date:</th><td>$next_game_date</td></tr>
				<tr><th>Time:</th><td>$game_time</td></tr>
				<tr><th>Field:</th><td>$field</td></tr>
				<tr><th>Manager:</th><td>$mgr_first $mgr_last(<a href='mailto:$mgr_email1'>$mgr_email1</a>)</td></tr>
			</table>
		";
	
		$today = date("l, F jS");	
		
		if ($roster_unknown >0) {
			$article = pluralize($roster_unknown, "this"  , "these");
			$number = pluralize($roster_unknown, "" , "$roster_unknown");
			$verb = pluralize($roster_unknown, " has", "s have");
			$pronoun = pluralize($roster_unknown, "his"  , "their");
			$body .= "	\n<p>As of $today, ";
			$body.= "$article <span class='bugs'>$number</span> roster player$verb 
					<u><span class='bugs'>not</span></u> 
					indicated $pronoun availability for the game ";
			$body .= "<ul class='bugs'>";
			for ($p=0; $p<$num_players; $p++) {  //list missing players and collect their emails
				if($data[$p]['coming'] == 'U' AND $data[$p]["status"]<>"sub") {
					$body.= "\n	<li>{$data[$p]['first_name']} {$data[$p]['last_name']}</li>";
					$to_list = add_email($to_list,$data[$p]['email1']);
					$to_list = add_email($to_list,$data[$p]['email2']);
				}
			}
			$body .= "\n</ul>";
		}
			
		else $body .= "All $roster_total players have recorded their attendance for this game. ";
		
		// Paragraph describing the roster players who have not yet responded
		if ($roster_maybe > 0) {
			$tmp1 = pluralize($roster_maybe, "One" , "These $roster_maybe");
			$tmp2 = pluralize($roster_maybe, " has", "s have");
			$tmp3 = pluralize($roster_maybe, "he"  , "they");	
			$body .="\n<br><span class='bugs'>$tmp1</span> roster player$tmp2
					said that <span class='bugs'><u>maybe</u></span> $tmp3 will attend. ";
			$body .= "<ul class='bugs'>";
			for ($p=0; $p<$num_players; $p++) {  // List Maybe players and collect their emails
				if($data[$p]['coming'] == 'M' and $data[$p]['status']<>'sub') {
					$body.= "<li>{$data[$p]['first_name']} {$data[$p]['last_name']}</li>";
					$to_list = add_email($to_list,$data[$p]['email1']);
					$to_list = add_email($to_list,$data[$p]['email2']);
				}
			}
			$body .= "\n</ul>";
		}
		
		
		// Paragraph defining the subs who were invited but haven't responded
		if ($subs_invited > 0) {
			$tmp1 = pluralize($subs_invited, "One" , "These $subs_invited");
			$tmp2a = pluralize($subs_invited, " has", "s have");
			$tmp2b = pluralize($subs_invited, " has", " have");
			$tmp3 = pluralize($subs_invited, "he"  , "they");	
			$body .="\n<br><span class='bugs'>$tmp1</span> sub$tmp2a
					been invited to play but $tmp2b not yet indicated whether $tmp3 will attend. ";
			$body .= "<ul class='bugs'>";
			for ($p=0; $p<$num_players; $p++) {  // List Maybe players and collect their emails
				if($data[$p]['coming'] == 'M' and $data[$p]['status']=='sub') {
					$body.= "<li>{$data[$p]['first_name']} {$data[$p]['last_name']}</li>";
					$to_list = add_email($to_list,$data[$p]['email1']);
					$to_list = add_email($to_list,$data[$p]['email2']);
				}
			}
			$body .= "\n</ul>";
		}
		
		
		
		$body .= "
			<p>Please update your attendance status as soon as possible at
			<a href='http://www.sickingfamily.com/bugs/attendance/'
				>http://www.sickingfamily.com/bugs/attendance/</a>
			<br>so that $mgr_first can determine if we will have a full roster, and contact subs if necessary.
			</p>";
		
		$body .= "
			<p>Attendance Summary: 
			<table class='summary' border='1'>
				<tr>
					<th>&nbsp;</th>
					<th>Yes</th>
					<th>Maybe</th>
					<th>No</th>
					<th>Unknown</th>
				</tr>
				<tr>
					<th>Roster Players</th>
					<td>$roster_yes</span></td>
					<td>$roster_maybe</span></td>
					<td>$roster_no</span></td>
					<td>$roster_unknown</span></td>
				</tr>
				<tr>
					<th>Subs</th>
					<td>$subs_yes</span></td>
					<td>$subs_invited</span></td>
					<td>$subs_no</span></td>
					<td>-</span></td>
				</tr>
				<tr>
					<th>Total</th>
					<td>$total_yes</span></td>
					<td>$total_maybe</span></td>
					<td>$total_no</span></td>
					<td>$roster_unknown</span></td>
				</tr>
				<tr>
				</tr>
			</table>
			</p>
		";
		
		$enough = pluralize ($total_yes-9,"just","","barely");
		if($total_yes<9)$enough="not";
		
		$body .= "
			<p>There<span class='bugs'><u>
			are $enough enough</u></span> 
			confirmed players to field a team.</p>
		";
		
		$body .= "
			<p>Thanks,</p>
			<blockquote>
				Matt
			</blockquote>
			<hr>
		";
		
		$to_list  = add_email($to_list,'matt@sickingfamily.com');  // Always copy Matt
		$subject  = "Are you coming to the game?  Thursday $next_game_date, $game_time"; 
		$headers  = "MIME-Version: 1.0 \r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1 \r\n";
		$headers .= "From: matt@sickingfamily.com";
	

		if (mail($to_list, $subject, $body, $headers)) {
			echo"Message successfully sent!\n(bUGS Attendance Check)";
		}
		else {	
			echo("Message delivery failed...\n<br>(bUGS Attendance Check)");
			echo "<br><br>Subject: <br>$subject";
			echo "<br><br>to_list: <br>$to_list";
			echo "<br><br>body: <br>$body";
			echo "<br><br>headers: <br>$headers";
		}
	}

//////////////////////////////////////////////////////////////////////////////////////////////////////////
function add_email($to,$addr) {
	if(empty($addr)) return $to; //If no email address then return TO list as is
	if(empty($to)) return $addr; //If TO lsit is currently empty then TO list becomes current address
	else return $to.", ".$addr;  // Otherwise append current address to TO list separated by comma
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////

function pluralize ($val, $string_one, $string_many, $string_zero="") {
db_echo("val",$val);
db_echo("string_one",$string_one);
db_echo("string_many",$string_many);
db_echo("string_zero",$string_zero);
	if ($val == 0) return $string_zero;
	if ($val == 1) return $string_one;
	if ($val > 1) return $string_many;
	else return "BAD (negative) value for \$val in function pluralize ($val)";
}