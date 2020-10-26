<head>

	<style>
		p {
			font-family: "Lucida Console", Monaco, monospace;
			font-size: 10pt;
		}
	</style>
</head>

<body>
	<?php
	////////////////////////////////////////////////////////////////////////////
	//  +-----------------------------+
	//  ¦ _cron_forwarders_create.php ¦
	//  +-----------------------------+
	////////////////////////////////////////////////////////////////////////////

	require_once "forwarder_subs.php";

	//First delete all existing forwarders
	//forwarder_delete_all();

	echo "<br />BEGIN FORWARDER CREATION<br />\n";

	// Add the cpanel forwarder software, including the setup for our site.
	$site = 'cron';
	$db = "sickingf_";
	$db_name = "family";

	if (!empty($_SERVER["DOCUMENT_ROOT"])) $doc_root = $_SERVER["DOCUMENT_ROOT"];
	else $doc_root = getcwd() . "/public_html";  //Is running under cron, so use current work directory/public html
	include $doc_root . "/common/initialize.php";
	identifier_comment("START " . __FILE__);

	debug_on();
	db_echo("<pre>", 0);
	$print = FALSE;  //SET THIS TRUE TO SEE RUNNING ACCOUNT OF THE FORWARD_SFC FUNCTION

	// Special forwards for anyone who has a true "sickingfamily.com" email address. (Matt, Jeannette and Luke)
	db_echo("<pre>,0)");
	forward_sfc("dbc",  "matt", TRUE);
	forward_sfc("dbc", "jeannette", TRUE);
	forward_sfc("all",  "matt", TRUE);
	forward_sfc("all", "jeannette", TRUE);
	forward_sfc("all", "luke", TRUE);
	db_echo("<pre>,0)");


	// Get all emails sorted by userid, except for those ending in "@sickingfamily.com"
	echo '<br /><br />';
	$result = do_query("
	SELECT first_name, last_name, email, gender, userid, dbc, sib
		FROM members, emails
		WHERE member_id = member_ptr
		AND email NOT LIKE '%@sickingfamily.com'
		ORDER BY userid
	");

	// Loop through all emails and define forwarders	
		db_text("<pre>",0);
	while ($row = mysqli_fetch_assoc($result)) {
		extract($row); // Load db data into separate variables
		forward_sfc($userid, $email);
		forward_sfc('all', $email);
		if ($dbc == "Y") forward_sfc("dbc", $email, FALSE);
	}

	echo "<br />\nCOPYING FORWARDERS.TXT over VALIASES<br />\n";
	$lastline = forward_update_valiases();
	echo "<br />\nDONE<br />\n";
		db_text("</pre>",0);
	function forward_sfc($from, $to, $to_sfc = FALSE)
	{
		db_text(str_repeat(" ",10-strlen($from)),0);
		db_text("[$from]-->", 0);
		db_text("[$to]");
		$f = trim(strtolower($from));
		$t = trim(strtolower($to));
		if ($to_sfc) $t .= "@sickingfamily.com";
		forwarder_create($f, $t);
	}

	echo "<br />\nEND FORWARDER CREATION<br/>";
	identifier_comment("END " . __FILE__);

	?>
	</p>
</body>