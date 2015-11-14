<?php
	function connect()
	{
		// Connects to the Database 
		mysql_connect("localhost", "security-project", "69BgYftvzpEH", false, 65536) or die(mysql_error());
		mysql_select_db("hackme") or die(mysql_error()); 
	}
?>
