<?php
	GLOBAL $DB, $_CONFIG;
	
	if(!isset($_SESSION['user']))
	{
		header('Location: ?p=register&page=1');
		exit();
	}	
	if(isset($_GET['page']))
	{
		header('Location: ?p=registerv');
		exit();
	}
	
	$_CONFIG['params']['contentType'] = 'class="container"';
	$_CONFIG['params']['css'] .= '<link href="../css/registreren.css" rel="stylesheet">';
	$_CONFIG['params']['errorMessage'] = "";
	
?>