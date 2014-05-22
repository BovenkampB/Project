<?php
	GLOBAL $DB, $_CONFIG;

	function userLogin($values)
	{
		if(!isset($values['email']) || !isset($values['password']))
		{
			$_CONFIG['params']['errorLogin'] = "<script alert('Je heb iets leeg gelaten!'); </script>";
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
		echo $query;
		$source = runPreparedQuery($query, array($email, $password));
		$row = fetchArray($source);

		if($row['email'] == $values['email'] || $row['gebruikersnaam'] == $values['email']){
			$_SESSION['user']['username']   = $row['gebruikersnaam'];
			$_SESSION['user']['email']      = $row['email'];
			$_SESSION['user']['loggedin']   = true;
			$_SESSION['user']['voornaam']   = $row['voornaam'];
			$_SESSION['user']['achternaam'] = $row['achternaam'];
		}
		else{
			$_CONFIG['params']['errorLogin'] = "<script alert('Incorrecte login gegevens!'); </script>";
		}
	}
?>