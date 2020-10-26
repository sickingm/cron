<?php

$file = "/dev/aliases/sickingfamily.com";
echo "About to open $file <br />";
exit();
if (!fopen($file ,"r")) echo "Can't open it"; else echo "Success";
echo "done";

?>