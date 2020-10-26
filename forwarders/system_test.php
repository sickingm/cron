<?php



// L('cat /etc/valiases/sickingfamily.com')
   L('cat /home3/sickingf/public_html/cron/forwarders.txt');
   L('cat /etc/valiases/sickingfamily.com');
   L('cp /dev/null /etc/valiases/sickingfamily.com');
   L('cp /home3/sickingf/public_html/cron/forwarders/forwarders.txt /etc/valiases/sickingfamily.com');
L('cat /etc/valiases/sickingfamily.com');



function L($cmd){
	echo "<pre>
executing [[$cmd]]<br /><br />";
	$LL = system($cmd,$retval);
	echo 	"
   Last line: $LL
Return value: $retval<hr />";
}
?>