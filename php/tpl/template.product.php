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

	if($row['voorwerpnummer'] != $_GET['id']){
		header('Location: ?p=start');
		exit();
	}

	$_CONFIG['params']['productNaam']         = $row['titel'];
	$_CONFIG['params']['productBeschrijving'] = $row['beschrijving'];

	if (time() >= $row['datumtijd']->getTimestamp())
	{
		$_CONFIG['params']['productTimeLeft'] = "<b>Veiling gesloten</b>";
	}
	else
	{
		$query = 	"DECLARE
						@start DATETIME ,
						@end DATETIME,
						@x INT

						SELECT @end = ? , @start = getDate(), @x = DATEDIFF(s, @start, @end)

						SELECT  CONVERT(VARCHAR, DATEDIFF(dd, @start, @end)) + ' Dag ' + 
						    CONVERT(VARCHAR, DATEDIFF(hh, @start, @end) % 24) + ' Uur ' +
						    CONVERT(VARCHAR, DATEDIFF(mi, @start, @end) % 60) + ' Minuten ' +
						    CONVERT(VARCHAR, DATEPART(ss, DATEADD(s, @x, CONVERT(DATETIME2, '0001-01-01')))) + ' Seconden'";
		$time = fetchArray(runPreparedQuery($query, array($row['datumtijd']->format( 'Y-m-d H:i:s' ))));
		$_CONFIG['params']['productTimeLeft'] = '<p><b>Veiling sluit over</b>: <br />' . $time[0];
	}

	
?>