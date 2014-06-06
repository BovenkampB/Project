<?php
	GLOBAL $_CONFIG, $DB;

	/* Defineer site parameters */
	$_CONFIG['params']['contentType']    = 'class="container"';
	$_CONFIG['params']['css'] 			.= '<link href="../css/productpagina.css" rel="stylesheet">';
	$_CONFIG['params']['errorMessage']   = '';
	$_CONFIG['params']['feedbackForm']   = '';
	$_CONFIG['params']['bidForm']        = '';
	$_CONFIG['params']['footerJS']       = '<script src="../js/gallery.js"></script>';

	/* Kijk of er wel een item opgevraagd wordt */
	if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
		header('Location: ?p=start');
		exit();
	}

	/* Haal informatie van dit product op */
	$query = "SELECT * FROM Voorwerprubriek 
				INNER JOIN Voorwerp ON Voorwerprubriek.voorwerpnummer = Voorwerp.voorwerpnummer
				INNER JOIN Rubriek ON Voorwerprubriek.rubrieknummer = Rubriek.rubrieknummer
				WHERE Rubriek.Rubrieknummer = Voorwerprubriek.rubrieknummer AND Voorwerp.voorwerpnummer = ?";

	$source = runPreparedQuery($query, array($_GET['id']));
	$row = fetchArray($source);

	/* Kijk of de juiste informatie geladen is */
	if($row['voorwerpnummer'] != $_GET['id']){
		header('Location: ?p=start');
		exit();
	}

	/* Defineer pagina parameters */
	$_CONFIG['params']['productNaam']         = $row['titel'];
	$_CONFIG['params']['productBeschrijving'] = $row['beschrijving'];
	$_CONFIG['params']['productNummer'] 	  = $row['voorwerpnummer'];
	$gesloten = false;

	/* Query om tijd te krijgen */
	$timeStampQuery = 	"DECLARE
						@start DATETIME ,
						@end DATETIME,
						@x INT

						SELECT @end = ? , @start = getDate(), @x = DATEDIFF(s, @start, @end)

						SELECT  CONVERT(VARCHAR, DATEDIFF(dd, @start, @end)) + ' Dag(en) ' + 
						    CONVERT(VARCHAR, DATEDIFF(hh, @start, @end) % 24) + ' Uur ' +
						    CONVERT(VARCHAR, DATEDIFF(mi, @start, @end) % 60) + ' Minuten ' +
						    CONVERT(VARCHAR, DATEPART(ss, DATEADD(s, @x, CONVERT(DATETIME2, '0001-01-01')))) + ' Seconden'";

	/* Kijk Of Veiling Nog Open Is */
	if (time() >= $row['datumtijd']->getTimestamp())
	{
		$gesloten = true;
		$_CONFIG['params']['productTimeLeft'] = "<b>Veiling gesloten</b>";
		$_CONFIG['params']['feedbackForm'] = loadFeedbackForm();
	}
	else
	{
		$time = fetchArray(runPreparedQuery($timeStampQuery, array($row['datumtijd']->format( 'Y-m-d H:i:s' ))));
		$_CONFIG['params']['productTimeLeft'] = '<p><b>Veiling sluit over</b>: <br />' . $time[0];
		$_CONFIG['params']['bidForm'] = loadBidForm();
	}

	/* Laad Sub Artikelen */
	$query = "SELECT TOP 3 * FROM Voorwerp
				INNER JOIN Voorwerprubriek ON Voorwerprubriek.voorwerpnummer = Voorwerp.voorwerpnummer
				WHERE voorwerprubriek.rubrieknummer = ? AND datumtijd > getDate()
				ORDER BY Voorwerp.datumtijd DESC, Voorwerp.voorwerpnummer ASC";

	$source = runPreparedQuery($query, array($row['rubrieknummer'], $row['voorwerpnummer']));

	$row['filenaam'] = '%productImage%';
	$counter = 1;
	$lastItem = $row;

	while($item = fetchArray($source))
	{
		if(!isset($item['filenaa']))
			$item['filenaam'] = '404.png';

		addItem($counter, $item);
		$lastItem = $item;
		$counter++;
	}

	while($counter < 4)
	{
		addItem($counter, $lastItem);
		$counter++;
	}

	/* Handel $_POST Biedingen af. */
	if(isset($_POST['bid']) && isset($_POST['item']))
	{
		if(is_numeric($_POST['bid']) && is_numeric($_POST['item']))
		{
			if(isset($_SESSION['user']['loggedin']))
			{
				if($gesloten == false)
				{
					$query = "SELECT Top 1 *  FROM Bod WHERE Voorwerpnummer = ? ORDER BY bodbedrag DESC";
					$source = runPreparedQuery($query, array($_POST['item']));
					$resultSet = fetchArray($source);
					if($resultSet['bodbedrag'] + calculateBidStep($resultSet['bodbedrag']) <= $_POST['bid'])
					{
						$query = "INSERT INTO Bod VALUES (?, ?, ?, ?, ?)";
						runPreparedQuery($query, array($_POST['item'], $_POST['bid'], $_SESSION['user']['username'], date('Y-m-d'), date('H:i:s')));
					}
					else
					{
						$_CONFIG['params']['errorMessage'] = '<div class="alert alert-error">  
													          <a class="close" data-dismiss="alert">×</a>  
													          <strong>Ongeldig bedrag!</strong>Er is een ongeldig bedrag ingevuld!.  
													        </div>';
					}
				}
				else
				{
					$_CONFIG['params']['errorMessage'] = '<div class="alert alert-error">  
													          <a class="close" data-dismiss="alert">×</a>  
													          <strong>Veiling is gesloten!</strong>Er kan niet meer geboden worden.
													        </div>';
				}
			}
			else
			{
				$_CONFIG['params']['errorMessage'] = '<div class="alert alert-error">  
												          <a class="close" data-dismiss="alert">×</a>  
												          <strong>Log eerst in!</strong>Log eerst in voor je gaat bieden.
												        </div>';
			}
		}
		else
		{
			$_CONFIG['params']['errorMessage'] = '<div class="alert alert-error">  
												          <a class="close" data-dismiss="alert">×</a>  
												          <strong>Ongeldig bedrag!</strong>Er is een ongeldig bedrag ingevuld!.  
												        </div>';
		}
	}

	if(isset($_POST['feedback']) && $gesloten)
	{
		if(isset($_SESSION['user']['loggedin']))
		{
			if(strlen($_POST['commentaar']) > 3)
			{
				if(isset($_POST['type']) && ($_POST['type'] == "Positief" || $_POST['type'] == "Neutraal" || $_POST['type'] == "Negatief"))
				{
					$query = "INSERT INTO Feedback VALUES (?, ?, ?, ?, ?, ?)";
					runPreparedQuery($query, array($_GET['id'], $_SESSION['user']['username'], $_POST['type'], date('d-m-y'), date('H:i'), $_POST['commentaar']));
					//echo ("INSERT INTO Feedback VALUES (".$_GET['id'].", '".$_SESSION['user']['username']."', '".$_POST['type']."', '".date('d-m-y')."', '".date('H:i')."', '".$_POST['commentaar']."')");
				}
			}
		}
	}

	/* Laad de biedingen */
	loadBids($row);

	/* Laad alle afbeeldingen voor het product */
	loadImages($row);

	function addItem($counter, $item)
	{
		GLOBAL $_CONFIG, $timeStampQuery;
		$_CONFIG['params']['productRelated' . $counter] = $item['voorwerpnummer'];
		$_CONFIG['params']['productRelated' . $counter . 'Titel'] = $item['titel'];
		$_CONFIG['params']['productRelated' . $counter . 'Beschrijving'] = $item['beschrijving'];
		$_CONFIG['params']['productRelated' . $counter . 'Prijs'] = $item['startprijs'];
		$_CONFIG['params']['productRelated' . $counter . 'TimeLeft'] = fetchArray(runPreparedQuery($timeStampQuery, array($item['datumtijd']->format('Y-m-d H:i:s'))));
		$_CONFIG['params']['productRelated' . $counter . 'Image'] = $item['filenaam'];
	}

	function calculateBidStep($price)
	{
		if($price < 49.99)
			return 0.50;
		else{
			if($price < 499.99)
				return 1.00;
			else{
				if($price < 999.99)
					return 5.00;
				else{
					if($price < 4999.99)
						return 10.00;
					else
						return 50.00;
				}
			}
		}
	}

	function loadBids($item)
	{
		GLOBAL $_CONFIG;
		$_CONFIG['params']['bids'] = null;

		$query = "SELECT * FROM Bod WHERE Voorwerpnummer = ? ORDER BY bodbedrag DESC";
		$source = runPreparedQuery($query, array($item['voorwerpnummer']));

		$highestFound = false;
		$_CONFIG['params']['minBid'] = fixBidValue($item['startprijs']);
		while($bid = fetchArray($source))
		{
			if(!$highestFound)
			{
				$_CONFIG['params']['bidStep'] = calculateBidStep($bid['bodbedrag']);

				if($bid['bodbedrag'])
					$_CONFIG['params']['minBid'] = fixBidValue(($bid['bodbedrag'] + $_CONFIG['params']['bidStep']));
				else
					$_CONFIG['params']['minBid'] = $item['startprijs'];

				$highestFound = true;
			}

			$_CONFIG['params']['bids'] .= '<br />&euro; ' . $bid['bodbedrag'] . ' - ' . $bid['gebruikersnaam'];
		}

		if($_CONFIG['params']['bids'] == null)
		{
			$_CONFIG['params']['bids'] = "<br />Er zijn nog geen biedingen. Begin met bieden!";
		}
	}

	function fixBidValue($value)
	{
		$count = explode('.', $value);
		if(count($count) == 1)
		{
			return $value . ".00";
		}
		else if(strlen($count[1]) == 1)
			return $value . "0";
		else return $value;
	}

	function loadImages($item)
	{
		GLOBAL $_CONFIG;

		$query = "SELECT * FROM Bestand WHERE Voorwerpnummer = ?";
		$source = runPreparedQuery($query, array($item['voorwerpnummer']));

		$firstFound = false;
		$_CONFIG['params']['productSubImages'] = null;

		while($image = fetchArray($source))
		{
			if(!$firstFound)
			{
				$_CONFIG['params']['productImage'] = $image['filenaam'];
				$firstFound = true;
			}
			else
			{
				$_CONFIG['params']['productSubImages'] .= '<img src="upload/'.$image['filenaam'].'"  alt="subfoto">';
			}
		}
	}

	function loadFeedBackForm()
	{
		if(isset($_SESSION['user']) && $_SESSION['user']['loggedin'])
		{
			if($_SESSION['user']['username'] == highestBid())
			{
				$query = "SELECT * FROM feedback WHERE voorwerpnummer = ?";
				$source = runPreparedQuery($query, array($_GET['id']), array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
				if(numRows($source) < 1)
				{
					return '<table>
							<form method="post">
								<tr>
									<h4> Feedback over dit product. </h4>
							        <td> Beoordeling: </td>
							        <td>
								        <select name="type">
								            <option value="Positief">Positief</option>
								            <option value="Neutraal">Neutraal</option>
								            <option value="Negatief">Negatief</option>				
								        </select> 
							        </td>
						      </tr>
						      <tr>
						        <td> Commentaar: </td>
						        <td> <textarea type="textarea" name="commentaar" placeholder="Commentaar"></textarea> </td>
						      </tr>
						      <tr>
						        <td><input class="btn btn-large btn-primary" id="button" type="submit" name="feedback" value="Plaats commentaar"/></td>
						      </tr>
						    </form>
						</table>';
				}
			}
		}
		else
			return '';
	}

	function loadBidForm()
	{
		
		return '<form method="post">
		          <input class="span2" type="number" step="0.01" min="%minBid%" name="bid" value="%minBid%" />
		          <input type="hidden" value="%productNummer%" name="item" />
		         <p><button class="btn btn-primary" type="submit" href="#">Bod uitbrengen</button></p>
		        </form>';
	}

	function highestBid()
	{
		$query = "SELECT TOP 1 * FROM Bod WHERE Voorwerpnummer = ? ORDER BY bodbedrag DESC";
		$source = runPreparedQuery($query, array($_GET['id']), array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
		if(numRows($source) == 1)
		{
			$item = fetchArray($source);
			return $item['gebruikersnaam'];
		}else{
			return null;
		}
	}
?>