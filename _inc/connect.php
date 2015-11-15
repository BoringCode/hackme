<?php
	$DB = new mysqli("localhost", "security-project", "69BgYftvzpEH", "hackme");

	if ($DB->connect_errno) {
		die($mysqli->connect_error);
	}
?>
