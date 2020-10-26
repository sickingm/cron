<head>
</head><style>
p {
  font-family: "Lucida Console", Monaco, monospace;
  font-size:10pt;
}

</style>
</head>
<body>
<p>
<?php
////////////////////////////////////////////////////////////////////////////
//
//  +-----------------------------+
//  ¦ _cron_forwarders_create.php ¦
//  +-----------------------------+
////////////////////////////////////////////////////////////////////////////

require_once "forwarder_subs.php";

//First delete all existing forwarders
//forwarder_delete_all();

echo "<br />BEGIN FORWARDER CREATION<br />\n";

$site='cron'; 		
$db="sickingf_";	
$db_name="family";

if(!empty($_SERVER["DOCUMENT_ROOT"]))$doc_root= $_SERVER["DOCUMENT_ROOT"];
else $doc_root = getcwd()."/public_html";  //Is running under cron, so use current work directory/public html
include $doc_root . "/common/initialize.php"; 
identifier_comment("START ".__FILE__);

debug_on();
db_echo("<span 'Lucida Console', Monaco, monospace",0);
$print = FALSE;  //SET THIS TRUE TO SEE RUNNING ACCOUNT OF THE FORWARD_SFC FUNCTION

// Special forwards for anyone who has a true "sickingfamily.com" email address. (Matt, Jeannette and Luke)
	forward_sfc("sib_guys",  "matt", TRUE);
	forward_sfc("inlaw_gals","jeannette", TRUE);
	forward_sfc("gen3_guys", "luke", TRUE);	
	forward_sfc("matt", "sickingmatt@gmail.com", FALSE);	


// Get all emails sorted by userid, except for those ending in "@sickingfamily.com"
echo'<br /><br />';
$result = do_query("
	SELECT first_name, last_name, email, gender, userid, dbc, sib
		FROM members, emails
		WHERE member_id = member_ptr
		AND email NOT LIKE '%@sickingfamily.com'
		ORDER BY userid
	");

// Loop through all emails and define forwarders	
$previous_id = ""; // For distribution lists that only need to be done once per userid

while ($row = mysqli_fetch_assoc($result)) {
	extract ($row); // Load db data into separate variables

// Report on, and then ignore all records where no gender is given 
	if(empty($gender)){
		echo "<br />*** No gender defined for $first_name $last_name ($userid) --- Forwarding details skipped***<br />\n";
		continue;
	}

	$guygal = ( $gender == "M" ? "guys" : "gals" ); //Create gender suffix for special dist. lists
	
	if($previous_id != $userid) {  // First time with this userid, so take care of group distribution lists
		$previous_id = $userid;  // remember userid so we don't do this the next time through
		if($dbc == "Y") {  // DBC members are either sibs or inlaws or Jinny
			if ($userid != "Jinny") { //Forward to sibs or inlaws unless it's jinny'
				if ($sib == "Y")forward_sfc("sib_$guygal",  $userid, TRUE);
				else     forward_sfc("inlaw_$guygal", $userid, TRUE);				
			}
		}
		else { // Not a DBC member so must be generation 3
			forward_sfc("gen3_$guygal",$userid, TRUE);  
		}
	}
    
	forward_sfc($userid,$email); 
}


// Define sibs as sib_guys and sib_gals
forward_sfc("sibs","sib_gals",TRUE);
forward_sfc("sibs","sib_guys",TRUE);

// Define inlaws as inlaw_guys and inlaw_gals
forward_sfc("inlaws","inlaw_gals",TRUE);
forward_sfc("inlaws","inlaw_guys",TRUE);

// Define gen2 guys as sib guys + inlaw guys
forward_sfc("gen2_guys","sib_guys",TRUE);
forward_sfc("gen2_guys","inlaw_guys",TRUE);

// Define gen2 gals as sib gals + inlaw gals
forward_sfc("gen2_gals","sib_gals",TRUE);
forward_sfc("gen2_gals","inlaw_gals",TRUE);

// Define gen2 as gen2_guys + gen2 gals
forward_sfc("gen2","gen2_guys",TRUE);
forward_sfc("gen2","gen2_gals",TRUE);

//Define dbc guys same as gen2 guys
forward_sfc("dbc_guys","gen2_guys",TRUE);

//Define dbc gals same as gen2 gals plus mom
forward_sfc("dbc_gals","gen2_gals",TRUE);
forward_sfc("dbc_gals","jinny",TRUE);

// Define dbc as dbc_guys + dbc_gals
forward_sfc("dbc","dbc_guys",TRUE);
forward_sfc("dbc","dbc_gals",TRUE);

// Define gen3 as gen3_guys and gen3_gals
forward_sfc("gen3","gen3_gals",TRUE);
forward_sfc("gen3","gen3_guys",TRUE);

// Define all as dbc plus gen3
forward_sfc("all","dbc",TRUE);
forward_sfc("all","gen3",TRUE);

// Define guys as dbc_guys plus gen3_guys
forward_sfc("guys","dbc_guys",TRUE);
forward_sfc("guys","gen3_guys",TRUE);

//define gals as dbc_gals plus gen3_gals
forward_sfc("gals","dbc_gals",TRUE);
forward_sfc("gals","gen3_gals",TRUE);

echo "<br />\nCOPYING FORWARDERS.TXT over VALIASES<br />\n";
$lastline = forward_update_valiases();
echo "<br />\nDONE<br />\n";

function forward_sfc($from, $to, $to_sfc=FALSE){
	db_text("<br />forward_sfc",0);
	db_text(str_repeat("&nbsp", 10-strlen($from)) ,0);
	db_text("$from:" ,0);
	db_text("$to ". ($to_sfc ? "(TRUE)":"(FALSE)") ,0);
	$f=strtolower($from);
	$t=strtolower($to);
	if($to_sfc)$t.="@sickingfamily.com";
	forwarder_create($f,$t);
}

echo "<br />\nEND FORWARDER CREATION<br/>";
identifier_comment("END ".__FILE__);

?>
</p></body>