<?php
	GLOBAL $_CONFIG, $DB;

	$_CONFIG['params']['css'] .= '
    <link href="../css/globaal.css" rel="stylesheet">
    <link href="../css/productpagina.css" rel="stylesheet">';
	$_CONFIG['params']['contentType'] .= 'class="container"';
	$_CONFIG['params']['itemsList'] = '';

	if(isset($_GET['s']) && isset($_GET['t']))
	{
		$_POST['s'] = $_GET['s'];
		$_POST['t'] = $_GET['t'];
	}
	if(!isset($_POST['s']) || !isset($_POST['t']))
	{
		$_CONFIG['params']['itemsList'] = '<h2><b>Vul AUB een zoekterm in</b></h2>';
	}
	else
	{
		if($_POST['t'] == "1")
			$query = 'SELECT * FROM Voorwerp WHERE titel LIKE ?';
		else
			$query = 'SELECT * FROM Voorwerprubriek 
						INNER JOIN Voorwerp ON Voorwerprubriek.voorwerpnummer = Voorwerp.voorwerpnummer
						LEFT JOIN Rubriek ON Voorwerprubriek.rubrieknummer = Rubriek.rubrieknummer
						WHERE Rubriek.Rubrieknummer = Voorwerprubriek.rubrieknummer AND Rubriek.rubrieknaam LIKE ?';

		$source = runPreparedQuery($query, array('%' . $_POST['s'] . '%'), array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));

		if(numRows($source) > 0){
			while($row = fetchArray($source))
			{
				$query = 'SELECT * FROM Bestand WHERE voorwerpnummer = ? ORDER BY filenaam ASC';
				$qqq = runPreparedQuery($query, array($row['voorwerpnummer']));
				$result = fetchArray($qqq);
				$_CONFIG['params']['itemsList'] .= '<div class="span2">
											    	  <img src="upload/'.$result['filenaam'].'"> 
											    	  <h4>'.$row['titel'].'</h4>          
											    	  <a class="btn btn-primary" href="?p=product&id='.$row['voorwerpnummer'].'">Bekijk</a>
											    	</div>';
			}
		}else{
			$_CONFIG['params']['itemsList'] = '<h2><b>Geen producten gevonden.</b></h2>';
		}
	}
?>