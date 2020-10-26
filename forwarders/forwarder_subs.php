<?php
/*
Contains:
forwarder_create - adds one forwarder to the the list
forwarder_delete_all - deletes all existing forwarders
forwarder_update_valiases - Copies the local newly created forwarders.txt file over the valises file

This method simply creates a csv file which must be manually imported into the forwarder list
See CPanel / Email / Import Addresses
*/

function forwarder_create($user,$fwd) {
    static $fhandle = 0;
    static $fname = "forwarders.txt";
    global $print;

    if ($fhandle == 0){
        echo("<br />\nCREATING FILE '$fname'<br />\n<br />\r\n");
        $fhandle = fopen($fname,"w");
    }

    if(empty($user)){
        db_text("<br />\nClosing $fname<br />\n");
        fclose($fhandle);
        echo "<br />\nDONE";
        exit;
    }

    if($print)echo " --- $user@sickingfamily.com==>$fwd<br />\n";
        fwrite($fhandle, "$user@sickingfamily.com: $fwd\n");
        return;
    }

function forwarder_delete_all() {
    /*
    This reoutine deletes all Forwarders in the system.
    Use this in preparation for creating a new set of forwarders, 
    thereby making sure that any obsolete forwarders are removed
    */
    echo "<br />\nDELETING valiases FROM /etc/valiases/sickingfamily.com<br />\n";
    $lastline = system("cp /dev/null /etc/valiases/sickingfamily.com",$retcode); 
    echo "LAST LINE: $lastline<br />\n";
    echo "RETURN CCODE: $retcode<br />";

}

function forward_update_valiases(){
    system('cp /home3/sickingf/public_html/cron/forwarders/forwarders.txt /etc/valiases/sickingfamily.com',$retcode);
    echo "RETURN CCODE: $retcode<br />";;
}

function SYS($cmd,$description){
    $nl ='<br />\n';
    echo $nl.$description.$nl;
    $lastline = system($cmd, $retcode);
    echo "$nl RETURN CODE: $retcode $nl";
    echo "$nl LAST LINE: $lastline $nl";
}

?>