<?php

echo "Using system cp method<br />";
$lastline = system("/etc/valiases/sickingfamily.com  forwarder_dump1.bin",$retcode); 
echo "LAST LINE: $lastline<br />\n";
echo "RETURN CCODE: $retcode<br />";
echo "<br />";

echo "<br />About to xcopy valiases file<br />";
exec("xcopy /etc/valiases/sickingfamily.com /public_html/forwarder_dump2.bin");
echo "<br />";

echo "<br />About to copy valiases file<br />";
if(copy("/etc/valiases/sickingfamily.com","forwarder_dump3.bin")) echo "Copy successful";
else echo "Copy unsuccessful";
echo "<br />";



echo "About to open alias file<br />";
$handle = fopen("/etc/valiases/sickingfamily.com", "r");
echo "Open completed<br />";
$contents = file_get_contents("/etc/valiases/sickingfamily.com");
echo "<pre>";
echo $contents;
echo "</pre>";

?>