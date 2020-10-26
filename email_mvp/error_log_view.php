<?php

echo "<pre>\n";
if(!$log=file_get_contents ("error_log" , FALSE  )) {
    Echo "Unable to open error_log....<br />";
} 
else {
    echo "Opening error_log:<br />$log";
}
echo "</pre>";
?>      