<?php
	GLOBAL $_CONFIG, $DB;

	$_CONFIG['params']['contentType'] = 'class="container"';
	$_CONFIG['params']['css'] .= '<link href="../css/productpagina.css" rel="stylesheet">';

	if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
		header('Location: ?p=start');
		exit();
	}

	$query = "SELECT * FROM Voorwerp WHERE voorwerpnummer = ?";
	$source = runPreparedQuery($query, array($_GET['id']));
	$row = fetchArray($source);

	$_CONFIG['params']['productNaam']         = $row['titel'];
	$_CONFIG['params']['productBeschrijving'] = $row['beschrijving'];
	$_CONFIG['params']['productEndDay']       = date_diff(date_create($row['tijdaanduiding']), date_create($row['looptijdeindedag']))->format('%R%a days');
	$_CONFIG['params']['productEndTime']	  = $interval;
	echo $interval;

?>