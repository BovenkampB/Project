<?php
	GLOBAL $_CONFIG, $DB;
	
	$query = "SELECT TOP 3 * FROM Voorwerp WHERE datumtijd >= getDate() ORDER BY datumtijd ASC";
	$source = runPreparedQuery($query, array(), array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
	$_CONFIG['params']['caroussel'] = "";

	$timeStampQuery = 	"DECLARE
						@start DATETIME ,
						@end DATETIME,
						@x INT

						SELECT @end = ? , @start = getDate(), @x = DATEDIFF(s, @start, @end)

						SELECT  CONVERT(VARCHAR, DATEDIFF(dd, @start, @end)) + ' Dag(en) ' + 
						    CONVERT(VARCHAR, DATEDIFF(hh, @start, @end) % 24) + ' Uur ' +
						    CONVERT(VARCHAR, DATEDIFF(mi, @start, @end) % 60) + ' Minuten ' +
						    CONVERT(VARCHAR, DATEPART(ss, DATEADD(s, @x, CONVERT(DATETIME2, '0001-01-01')))) + ' Seconden'";

	$count = 0;
	$htmlItem = '<div class="item">';
	$active = 'active';

	while($row = fetchArray($source))
	{
		$count++;

		$iquery = "SELECT TOP 1 filenaam FROM Bestand WHERE Voorwerpnummer = ?";
		$isource = runPreparedQuery($iquery, array($row['voorwerpnummer']), array("Scrollable" => SQLSRV_CURSOR_KEYSET));
		$iresource = fetchArray($isource);
		$bquery = "SELECT TOP 1 * FROM Bod WHERE Voorwerpnummer = ? ORDER BY bodbedrag ASC";
		$bsource = runPreparedQuery($bquery, array($row['voorwerpnummer']), array("Scrollable" => SQLSRV_CURSOR_KEYSET));
		
		if(numRows($bsource) > 0)
		{
			$bresource = fetchArray($bsource);
			$row['startprijs'] = $bresource['bodbedrag'];
		}

		if(numRows($isource) > 0)
			$image = $iresource['filenaam'];
		else
			$image = "404.png";

		$timeLeft = fetchArray(runPreparedQuery($timeStampQuery, array($row['datumtijd']->format('Y-m-d H:i:s'))));

		if($count == 3)
			$htmlItem = '';
		
		if($count > 1)
			$active = '';

		$row['beschrijving'] = substr($row['beschrijving'], 0, 200) . '...';

		$_CONFIG['params']['caroussel'] .= '<div class="item '.$active.'">
									          <div class="container">
									            <div class="carousel-caption">
									              <img src="upload/'.$image.'">
									              <h1>'.$row['titel'].'</h1>
									              <p class="lead">'.$row['beschrijving'].'.</p>
									              <br/>
									              <h2>Huidig bod: &euro;'.$row['startprijs'].'</h2>
									              <h4>Resterende tijd: <br /> '.$timeLeft[0].'</h4>
									              <a class="btn btn-large btn-primary" href="?p=product&amp;id='.$row['voorwerpnummer'].'">Bieden</a>
									            </div>
									          </div>
									        </div>
									        ';
	}

	$query = "SELECT TOP 3 * FROM Voorwerp WHERE datumtijd >= getDate() ORDER BY voorwerpnummer DESC";
	$source = runPreparedQuery($query, array(), array("Scrollable" => SQLSRV_CURSOR_KEYSET));

	$_CONFIG['params']['newItems'] = "";

	while($row = fetchArray($source))
	{
		$iquery = "SELECT TOP 1 filenaam FROM Bestand WHERE Voorwerpnummer = ?";
		$isource = runPreparedQuery($iquery, array($row['voorwerpnummer']), array("Scrollable" => SQLSRV_CURSOR_KEYSET));
		$iresource = fetchArray($isource);

		if(numRows($isource) > 0)
			$image = $iresource['filenaam'];
		else
			$image = "404.png";
		

		$bquery = "SELECT TOP 1 * FROM Bod WHERE Voorwerpnummer = ? ORDER BY bodbedrag ASC";
		$bsource = runPreparedQuery($bquery, array($row['voorwerpnummer']), array("Scrollable" => SQLSRV_CURSOR_KEYSET));
		
		if(numRows($bsource) > 0)
		{
			$bresource = fetchArray($bsource);
			$row['startprijs'] = $bresource['bodbedrag'];
		}

		$row['beschrijving'] = substr($row['beschrijving'], 0, 100) . '...';

		$timeLeft = fetchArray(runPreparedQuery($timeStampQuery, array($row['datumtijd']->format('Y-m-d H:i:s'))));

		$_CONFIG['params']['newItems'] .= '<div class="span4">
								          <img src="upload/'.$image.'"> 
								          <h2>'.$row['titel'].'</h2>
								          <p>'.$row['beschrijving'].'</p>
								          <h2>&euro; '.$row['startprijs'].'</h2>
								          <h4>Resterende tijd:<br /> '.$timeLeft[0].'</h4>
								          <p><a class="btn btn-large btn-primary" href="?p=product&amp;id='.$row['voorwerpnummer'].'">Bieden</a></p>
								        </div><!-- /.span4 -->';
	}
?>