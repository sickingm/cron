<?php

###############################################################
# cPanel Email Forwarder Creator 1.2
###############################################################
# Visit http://www.zubrag.com/scripts/ for updates
###############################################################
#
# Can be used in 3 ways.
#
# 1) Sample run from browser:
# cpanel_email_forwarder.php?euser=john&edomain=site1.com&eforward=johny@site2.com
#
# 2) When run from browser without parameters will show entry form
#
# 3) Sample run from you script (by including):
# $REQUEST['euser'] = 'john';
# $REQUEST['edomain'] = 'site1.com';
# $REQUEST['eforward'] = 'johny@site2.com';
# include("cpanel_email_forwarder.php");
#
###############################################################

////////////////////////////////////////////////////////////////
/////////////////////// SETTINGS START  ////////////////////////
////////////////////////////////////////////////////////////////

// cpanel user (the one you login to cpanel)
define('CPANELUSER','sickingf');

// cpanel password (the one you login to cpanel)
define('CPANELPASS','cPanel[blu3h057]');

// your cpanel domain (localhost, or domain name)
define('CPANELDOMAIN','sickingfamily.com');

// cPanel skin (usually "x")
// Check http://www.zubrag.com/articles/determine-cpanel-skin.php to know it for sure
define('CPANEL_SKIN','bluehost');

// Allow multiple forwarders for the same email?
// true - allow, false - disallow
define('ALLOW_MULTIPLE', false);

////////////////////////////////////////////////////////////////
/////////////////////// END OF SETTINGS ////////////////////////
////////////////////////////////////////////////////////////////

function getVar($name, $def = '') {
  if (isset($_REQUEST[$name]) && ($_REQUEST[$name] != ''))
    return $_REQUEST[$name];
  else
    return $def;
}

$cpuser = CPANELUSER;
$cppass = CPANELPASS;
$cpdomain = CPANELDOMAIN;
$cpskin = CPANEL_SKIN;

$euser=getVar('euser');
$edomain=getVar('edomain');
$eforward = getVar('eforward');

if (empty($euser) || empty($edomain) || empty($eforward)) {
  echo "All fields are required to create email forwarding:<br>
<form method='post'>
  Username:<input name='euser' value='$euser'> (sample: john)<br>
  Domain:<input name='edomain' value='$edomain'> (sample: mysite.com)<br>
  Redirect to:<input name='eforward' value='$eforward'> (sample: jimm@site2.com)<br>
  <input type='submit' value='Create forwarder' style='border:1px solid black'>
</form>";
  die();
}

if (!ALLOW_MULTIPLE) {
  // get list of existing forwarders for this email
  $forwarders = array();
  preg_match_all('/\?email=' . $euser. '@' . $edomain . '=([^"]*)/', file_get_contents("http://$cpuser:$cppass@$cpdomain:2082/frontend/$cpskin/mail/fwds.html"), $forwarders);

  if (count($forwarders[1]) > 0) {
    die("Email forwarder for this account already exists.");
  }
} // ALLOW_MULTIPLE

// Create email forwarder
$f = fopen
("http://$cpuser:$cppass@$cpdomain:2082/frontend/$cpskin/mail/doaddfwd.html?email=$euser&domain=$edomain&forward=$eforward",
 "r");
if (!$f) {
  die('Cannot create forwarding. Possible reasons: "fopen" function disallowed on your server, or PHP is running in SAFE mode');
}

$text = "";

// Check result
while (!feof ($f)) {
  $text = $text . fgets ($f, 1024);;
}
fclose($f);

if (ereg ("failure", $text, $out) || !strpos($text,"redirected")) {
  die('Cannot create email forwarding.' . $text);
}

echo "Email Forwarder created: $euser@$edomain =&gt; $eforward";

?>