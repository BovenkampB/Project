<?php
	GLOBAL $DB, $_CONFIG;

	if(isset($_SESSION['user']))
	{
		header('Location: ?p=start');
		exit();
	}
	if(!isset($_GET['page']))
	{
		header('Location: ?p=register&page=1');
		exit();
	}

	$_CONFIG['params']['contentType'] = 'class="container"';
	$_CONFIG['params']['css'] .= '<link href="../css/registreren.css" rel="stylesheet">';
	$_CONFIG['params']['errorMessage'] = "";

	if(!isset($_SESSION['register']['email']))
		$_SESSION['register']['email'] = "";

	$postValue = "register" . $_GET['page'];

	if(isset($_POST['submit']))
		$postValue = $_POST['submit'];


	switch($postValue)
	{
		default:
		case "register1":
			$placeholder = (isset($_SESSION['register']['email']) ? $_SESSION['register']['email'] : "E-mail adres");
			$_CONFIG['params']['registerContent'] = '<form id="registratie" method="post" action="?p=register&amp;page=2">
														<fieldset>
															<legend>E-mail controle:</legend>
																<table>
																	<tr> 
																		<td>E-mail adres:</td>
																		<td><input class="input" type="text" name="email" placeholder="E-mail adres" value="' . $_SESSION['register']['email'] . '" required/> </td>
																	</tr>									   
																</table>
																<button class="btn btn-large btn-primary" name="submit" value="register2">Verstuur bevestigingscode</button>
																<button class="btn btn-large" name="submit" value="register3">Ik heb al een code!</button>
														</fieldset>			   
													</form>';
			break;
		
		case "register2":
			if(!isset($_SESSION['register']['email']) || strlen($_SESSION['register']['email']) == 0)
				$_SESSION['register']['email'] = $_POST['email'];

			//TODO: Geldige Email.
			$code = rand(10, 99) . rand(10, 99) . rand(10, 99) . rand(10, 99) . rand(10, 99);

			$query = "MERGE INTO Registratiecode AS tab
						USING (VALUES
								(?, ?)
							) AS a (email, code)
							ON tab.email = a.email
							WHEN MATCHED THEN
								UPDATE SET tab.email = a.email, tab.code = a.code
							WHEN NOT MATCHED THEN
								INSERT (email, code)
								VALUES (?, ?);";
			runPreparedQuery($query, array($_SESSION['register']['email'], $code, $_SESSION['register']['email'], $code));
			sendEmail($_SESSION['register']['email'], $code);
			header('Location: ?p=register&page=3');
			exit();
			break;

		case "register3":
			if(strlen($_SESSION['register']['email']) < 1)
				$_SESSION['register']['email'] = $_POST['email'];

			$_CONFIG['params']['registerContent'] = '<form method="post" action="?p=register&amp;page=3">
														<fieldset>
															<legend>E-mail controle - Code besvestiging:</legend>
															 '.$_CONFIG['params']['errorMessage'].' Code is verstuurt naar: '. $_SESSION['register']['email'] .'
																<table>
																	<tbody><tr> 
																		<td>Code:</td>
																		<td><input class="input" type="text" name="code" placeholder="Code" /><input type="text" class="hidden" value="'.$_SESSION['register']['email'].'" /></td>
																	</tr>									   
																</tbody></table>
																<button class="btn btn-large btn-primary" name="submit" value="register1" href="?p=register&amp;page=1">Vorige</button>
																<button class="btn btn-large btn-primary" name="submit" value="register2" href="?p=register&amp;page=2">Hestuurcode</button>
																<button class="btn btn-large btn-primary" name="submit" value="register4" href="?p=register&amp;page=4">Volgende</button>
																
														</fieldset>
													</form>';
			break;
			

		case "register4":
				if(!isset($_POST['code']))
				{
					header('Location: ?p=register&page=3');
					exit();
				}

				$query = "SELECT * FROM Registratiecode WHERE code = ? AND email = ?";
				$source = runPreparedQuery($query, array($_POST['code'], $_SESSION['register']['email']), array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
				if(numRows($source) == 0){
					$_CONFIG['params']['errorMessage'] = '<b>Code niet gevonden! Probeer het opnieuw!</b>';
					header('Location: ?p=register&page=3');
				}
				
				registerParams();
				$_CONFIG['params']['registerContent'] = getRegisterForm();
				$_CONFIG['register']['super_secret_code'] = $_POST['code'];
			break;

		case "register5":
			if(isset($_POST) && $_POST['gebruikersnaam'] != $_SESSION['register']['gebruikersnaam'])
			{
				$_SESSION['gebruikersnaam'] = $_POST['gebruikersnaam'];
			}
			foreach($_POST as $key => $value)
			{
				$_SESSION['register'][$key] = $value;
				if(empty($_POST[$key]) && $key != "adres2" && $key != "telefoon2")
					return;
			}

			$error = false;

			$query = "SELECT * FROM Gebruiker WHERE Gebruikersnaam = ?";
			$source = runPreparedQuery($query, array(), array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
			if(numRows($source) > 0)
			{
				die("Gebruikersnaam bestaat al.");
				$_CONFIG['params']['error']['username'] = '<b>Gebruikersnaam is al bezet</b>';
				$error = true;
			}

			if($_POST['wachtwoord'] != $_POST['h_wachtwoord'])
			{
				die("Wachtwoorden kloppen niet.");
				$_CONFIG['params']['error']['wachtwoord'] = '<b>Wachtwoorden komen niet overeen</b>';
				$error = true;
			}

			if($error)
			{
				//TODO: Set session variable.
			}
				$_CONFIG['params']['registerContent'] = getRegisterForm();
			
			$query = "INSERT INTO Gebruiker VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
			$source = runPreparedQuery(
						$query, 
						array(
							$_POST['gebruikersnaam'],
							$_POST['voornaam'],
							$_POST['achternaam'],
							$_POST['adres'],
							$_POST['adres2'],
							$_POST['postcode'],
							$_POST['plaats'],
							$_POST['land'],
							$_POST['geboortedatum'],
							$_SESSION['register']['email'],
							$_POST['wachtwoord'],
							$_POST['geheimevraag'],
							$_POST['h_wachtwoord'],
							0,
							0)
					);

			runPreparedQuery("UPDATE registratiecode SET gebruikt = 1 WHERE code = ?", array($_SESSION['register']['super_secret_code']));

			$_SESSION['register'] = null;
			$_SESSION['user']['voornaam'] = $_POST['gebruikersnaam'];
			$_SESSION['user']['achternaam'] = $_POST['achternaam'];
			$_SESSION['user']['email'] = $_POST['email'];
			$_SESSION['user']['loggedin'] = true;
			header('Location: ?p=start');
			break;
		
	}

	function sendEmail($email, $code)
	{
		GLOBAL $_CONFIG;
		require_once('mailserver/class.phpmailer.php');
		$mail			 = new PHPMailer();
		$body			 = "Hallo, Hierbij de code om verder te kunnen met het registreren op EenmaalAndermaal. Mocht u geen code hebben aangevraagd dan kunt u deze email negeren. U code is: " . $code;
		$mail = new PHPMailer();  // create a new object
		$mail->IsSMTP(); // enable SMTP
		$mail->SMTPDebug = 0;  // debugging: 1 = errors and messages, 2 = messages only
		$mail->SMTPAuth = true;  // authentication enabled
		$mail->SMTPSecure = 'ssl'; // secure transfer enabled REQUIRED for GMail
		$mail->Host = 'smtp.gmail.com';
		$mail->Port = 465; 
		$mail->Username = "eenmaalandermaal15@gmail.com";  
		$mail->Password = "Bbrlweep1";		   
		$mail->SetFrom("eenmaalandermaal15@gmail.com", "EenmaalAndermaal");
		$mail->Subject = "EenmaalAndermaal Registratie Code";
		$mail->Body = $body;
		$mail->AddAddress($email);
		if(!$mail->Send()) {
			$_CONFIG['params']['errorMessage'] .= 'Mail error: '.$mail->ErrorInfo;
			die();
			return false;
		} else {
			return true;
		}
	
	}

	function getRegisterForm()
	{
		return '<form id="registratie" method="post" action="?p=register&amp;page=5">
			<fieldset>
				<legend>Inloggegevens:</legend>
					<table>
						<tbody><tr> 
							<td>Gebruikersnaam:</td>
							<td> <input class="input" type="text" name="gebruikersnaam" placeholder="Gebruikersnaam" value="'.$_SESSION['register']['gebruikersnaam'].'" required> </td>
						</tr>
						<tr>
							<td>Wachtwoord:</td>
							<td> <input class="input" type="password" name="wachtwoord" placeholder="Wachtwoord" value="'.$_SESSION['register']['wachtwoord'].'" required> </td>
						</tr>
						<tr>
							<td> Herhaling wachtwoord: </td>
							<td> <input class="input" type="password" name="h_wachtwoord" placeholder="Herhaling" value="'.$_SESSION['register']['h_wachtwoord'].'" required> </td>
						</tr>
					</tbody></table>
			</fieldset>
			<fieldset>
				<legend>Persoonlijke gegevens:</legend>
					<table>
						<tbody><tr>
							<td> Voornaam: </td>
							<td> <input class="input" type="text" name="voornaam" placeholder="Voornaam" value="'.$_SESSION['register']['voornaam'].'" required> </td>
							<td> Plaats: </td>
							<td> <input class="input" type="text" name="plaats" placeholder="Plaats" value="'.$_SESSION['register']['plaats'].'" required> </td>				
						</tr>
						<tr>
							<td> Achternaam: </td>
							<td> <input class="input" type="text" name="achternaam" placeholder="Achternaam" value="'.$_SESSION['register']['achternaam'].'" required> </td>
							<td> Land: </td>
							<td> <input class="input" type="text" name="land" placeholder="Land" value="'.$_SESSION['register']['land'].'" required> </td>
						</tr>
						<tr>
							<td> Adres: </td>
							<td> <input class="input" type="text" name="adres" placeholder="Adres" value="'.$_SESSION['register']['adres'].'" required> </td>
							<td> Geboortedatum: </td>
							<td> <input class="input" type="date" name="geboortedatum" placeholder="Geboortedatum" value="'.$_SESSION['register']['date'].'" required> </td>
						</tr>
						<tr>
							<td> Optioneel Adres:</td>
							<td> <input class="input" type="text" name="adres2" placeholder="Adres" value="'.$_SESSION['register']['adres2'].'"/> </td>
							<td> Geheime&nbsp;vraag: </td>
							<td><select name="geheimevraag" required>
								<!-- TODO: Query -->
								<option value="1">Wat is de meisjesnaam van je moeder?</option>
								<option value="2">In welke straat ben je geboren?</option>
								<option value="3">Wat is je lievelingsgerecht?</option>
								<option value="4">Hoe heet je oudste zusje?</option>
								<option value="5">Hoe heet je huisdier?</option>
							</select></td>
						</tr>
						   
						<tr>
							<td> Postcode: </td>
							<td> <input class="input" type="text" name="postcode" placeholder="Postcode" value="'.$_SESSION['register']['postcode'].'" required/> </td>
							<td> Geheim&nbsp;antwoord: </td>
							<td> <input class="input" type="text" name="g_antwoord" placeholder="Geheim antwoord" value="'.$_SESSION['register']['g_antwoord'].'"required/></td>															   
						</tr>
						<tr>
							<td>Telefoon nr:</td>
							<td> <input class="input" type="text" name="telefoon1" placeholder="Telefoon nr." value="'.$_SESSION['register']['telefoon1'].'" required/> </td>
																																		   
						</tr>
						<tr>
						   
							<td>Extra&nbsp;telefoon&nbsp;nr\'s   :</td>
							<td> <input class="input" type="text" name="telefoon2" placeholder="Telefoon nr." value="'.$_SESSION['register']['telefoon2'].'" /> </td>																										
						</tr>
						<tr>
							<td></td>
							<td><input id="addVar" type="button" class="btn btn-success" value="+"/>
							<input id="remVar" type="button" class="btn btn-danger" value="-" /></td>	
						</tr>
					</tbody>
				</table>
					Let op: Een verkoop account kunt u pas maken zodra u zich heeft geregistreerd als gebruiker. Dit doet u dan bij instellingen van uw account.
				<br />
					<input class="checkbox" name="tos" '.$_SESSION['register']['tos'].' type="checkbox">Ik accepteer de <a href="#">Algemene voorwaarden</a>
				<br />
				<br />
					<table><tbody><tr><td><button class="btn btn-large btn-primary" id="button" type="submit" value="register5" >Voltooien</button> </td></tr></tbody></table>
			</fieldset>
		</form>';
	}

	function registerParams()
	{
		$params = array(
				'gebruikersnaam', 
				'wachtwoord', 
				'h_wachtwoord', 
				'voornaam', 
				'achternaam', 
				'plaats', 
				'land', 
				'geboortedatum', 
				'adres', 
				'adres2', 
				'g_vraag', 
				'telefoon1',
				'telefoon2',
				'date',
				'postcode',
				'g_antwoord',
				'tos');
		foreach($params as $key)
		{
			$_SESSION['register'][$key] = "";
		}
	}
?>