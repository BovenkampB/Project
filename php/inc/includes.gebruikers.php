<?php
	GLOBAL $DB, $_CONFIG;

	function userLogin($values)
	{
		GLOBAL $_CONFIG;
		if(!isset($values['email']) || !isset($values['password']) || strlen($values['password']) == 0 || strlen($values['email']) == 0)
		{
			$_CONFIG['params']['loginError'] = "<script> alert('Je heb iets leeg gelaten met het inloggen!'); </script>";
			return;
		}

		$email = $values['email'];
		$password = $values['password'];
		$column = "";
		if(strpos($email, "@") !== false)
		{
			$column = "email";
		}
		else
		{
			$column = "gebruikersnaam";
		}
		
		$query = "SELECT * FROM gebruiker WHERE ".$column." = ? AND wachtwoord = ?";
		$source = runPreparedQuery($query, array($email, $password));
		$row = fetchArray($source);

		if($row['email'] == $values['email'] || $row['gebruikersnaam'] == $values['email']){
			$_SESSION['user']['username']    = $row['gebruikersnaam'];
			$_SESSION['user']['email']       = $row['email'];
			$_SESSION['user']['loggedin']    = true;
			$_SESSION['user']['voornaam']    = $row['voornaam'];
			$_SESSION['user']['achternaam']  = $row['achternaam'];
			$_SESSION['user']['verkoper']    = $row['verkoper'];
			$_SESSION['user']['geblokkeerd'] = $row['geblokkeerd'];
			$_SESSION['user']['creditcardnummer'] = '';
			$_SESSION['user']['ibannummer'] = '';
			if($_SESSION['user']['geblokkeerd'])
			{
				$_CONFIG['params']['loginError'] = "<script>alert('Uw account is geblokkeerd.');</script>";
				$_SESSION['user'] = null;
				session_destroy();
			}
			if($_SESSION['user']['verkoper'] == 1)
			{
				$query = "SELECT * FROM Verkoper WHERE gebruikersnaam = ?";
				$source = runPreparedQuery($query, array($_SESSION['user']['username']),array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
				if(numRows($source) > 0)
				{
					$vInfo = fetchArray($source);
					$_SESSION['user']['creditcardnummer'] = $vInfo['creditcardnummer'];
					$_SESSION['user']['ibannummer'] = $vInfo['IBAN'];
				}
			}
		}
		else{
			$_CONFIG['params']['loginError'] = "<script> alert('Incorrecte login gegevens!'); </script>";
		}
	}
?>