<?php

/*
    This reoutine deletes all Forwarders in the system.
    Use this in preparation for creating a new set of forwarders, 
    thereby making sure that any obsolete forwarders are removed
*/

require_once "forwarder_subs.php";

echo "<br /><b>Deleting all forwarders....<br /><br />";
forwarder_delete_all();
echo "<br /><b><color: red>All forwarders Deleted!!!</color><br /><br />";

?>