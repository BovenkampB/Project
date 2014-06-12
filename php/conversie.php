<?php
	$content = file_get_contents('DataBatch.sql');

	echo strip_tags($content);
?>