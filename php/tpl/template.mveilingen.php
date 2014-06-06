<?php
	GLOBAL $_CONFIG, $DB;

	$_CONFIG['params']['contentType'] .= 'class="container"';
	$_CONFIG['params']['siteContent'] = '';
	$_CONFIG['params']['css'] .= '<link href="../css/productpagina.css" rel="stylesheet">';
	$_CONFIG['params']['feedback'] = '';

	if(isset($_POST['button']) && is_numeric($_POST['button']))
	{
		$query = 'UPDATE Voorwerp SET betaald = 1 WHERE voorwerpnummer = ?';
		runPreparedQuery($query, array($_POST['button']));
	}

	$query = "SELECT * FROM Voorwerp WHERE verkoper = ?";
	$source = runPreparedQuery($query, array($_SESSION['user']['username']));

	while($row = fetchArray($source))
	{
		$oquery = "SELECT * FROM Gebruiker WHERE gebruikersnaam = ?";
		$osource = fetchArray(runPreparedQuery($oquery, array($row['koper'])));

		$betaalButton = ($row['betaald'] == 0 ? '<td class=borderbutton><form method="post"><button class="btn btn-large btn-success" id="button" type="submit" name="button" value="'.$row['voorwerpnummer'].'">Betaald</button></form> </td>' : '');
		$_CONFIG['params']['siteContent'] .= '<tr>
												<td> '.$row['titel'].' </td>
												 <td> '.$row['datumtijd']->format( 'Y-m-d H:i:s' ).' </td>
												<td> '.$osource['voornaam'].'&nbsp;'.$osource['achternaam'].' </td>
												<td> '.$osource['email'].' </td>
												'.$betaalButton.'
											</tr>';
	}

	$query = "SELECT * FROM Feedback INNER JOIN Voorwerp ON Feedback.voorwerpnummer = Voorwerp.voorwerpnummer WHERE Voorwerp.verkoper = ? ORDER BY datum DESC";
	$source = runPreparedQuery($query, array($_SESSION['user']['username']));

	while($row = fetchArray($source))
	{
		$_CONFIG['params']['feedback']  .= 	'<tr>
												<td> '.$row['feedbacksoort'].' </td>
												<td> '.$row['commentaar'].' </td>
											</tr>';
	}
?>