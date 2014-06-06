<?php
	GLOBAL $_CONFIG;
	$_CONFIG['params']['contentType'] = 'class="container"';
	$_CONFIG['params']['css'] = '<link href="../css/registreren.css" rel="stylesheet">';
	if(isset($_SESSION['user']['loggedin']))
	{
		if(isset($_POST))
		{
			if(isset($_POST['type']) && isset($_POST['credv']) && isset($_POST['ibanv']) && $_POST['checkb'] == "on")
			{
				if($_POST['type'] == "iban")
					$_POST['credv'] = null;
				else if($_POST['type'] == "cred")
					$_POST['ibanv'] = null;
				else
					return;

				if($_SESSION['user']['verkoper'] == "1")
				{
					$query = "UPDATE Verkoper SET IBAN = ?, creditcardnummer =? WHERE gebruikersnaam = ?";
					runPreparedQuery($query, array($_POST['credv'], $_POST['ibanv'], $_SESSION['user']['username']));
				}else{
					$query = "INSERT INTO Verkoper VALUES (?, ?, ?, ?, ?)";
					runPreparedQuery($query, array($_SESSION['user']['username'], '', $_POST['ibanv'], '', $_POST['credv']));
				}
				$_SESSION['user']['verkoper']         = 1;
				$_SESSION['user']['creditcardnummer'] = $_POST['credv'];
				$_SESSION['user']['ibannummer']             = $_POST['ibanv'];
			}
		}

			$_CONFIG['params']['content'] = '<form method="post">
												<fieldset>
													<legend>Instellingen:</legend>
													<table>
													<tr>
											        <td><h5>Betalings optie selecteren:</h5></td>
													</tr>   
													<tr> 
														<td><input type="radio" name="type" value="iban" >IBAN Rekening nr:</td>
														<td><input class="input" type="text" name="ibanv" placeholder="NL63......" value="%ibannummer%"/> </td>
													</tr>
													<tr> 
														<td><input type="radio" name="type" value="cred">Creditcard nr:</td>
														<td> <input class="input" type="text" name="credv" placeholder="Creditcard nr" value="%creditcardnummer%"/> </td>
													</tr>				   
													</table>			
													<br/>
													<tr>
														<td><input class="checkbox" name="checkb" type="checkbox">Ik accepteer de <a href="#">Algemene voorwaarden</a></td>
													</tr>
													<br/>
													<br/>
													<input type="submit" class="btn btn-large btn-primary" value="Inschrijven als verkoper">
												</fieldset>			   
											</form>';
	}else{
		header('Location: ?p=start');
		exit();
	}
?>