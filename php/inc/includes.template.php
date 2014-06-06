<?php	
	/*
		Haal Alle Paginas Op En Sla Ze Op In De Output Buffer (OB)
		Uitleg: We cachen ze tijdelijk zodat we alle paramaters nog kunnen vervangen.
	*/
	function loadPage($page)
	{
		if(fileExists($page))
		{
			$content = "";
			ob_start();
			include(tpl . 'header.html');
			include(tpl . 'header.php');
			include(tpl . $page . '.php');
			include(tpl . $page . '.html');
			include(tpl . 'footer.html');
			$content = ob_get_clean();
			parsePage($content);
		}
		else{
			header('Location: index.php?p=start');
			exit();
		}
	}

	/*
		Kijk Of Een Pagina Bestaat.
		Uitleg: Voorkom Cross Site Request Forgery. Veiligheid boven alles!
	*/
	function fileExists($page)
	{
		switch($page)
		{
			case "contact":
			case "start":
			case "register":
			case "logout":
			case "product":
			case "zoek":
			case "mveilingen":
			case "settings":
				return true;
			break;

			return false;
		}
	}

	/*
		Display De Pagina
		Uitleg: Parse de pagina en echo zijn contents.
	*/
	function parsePage($content)
	{
		$content = filterParams($content);
		echo $content;
		ob_flush();
	}

	/*
		Verander Alle Paramaters Voor Zijn Waarden
		Uitleg: Dynamische dingen kunnen eenvoudig toegevoegd worden via een parameter.
	*/
	function filterParams($content)
	{
		GLOBAL $_CONFIG;
		foreach ($_CONFIG['params'] as $key => $value) {
			$content = str_replace('%' . $key . '%', $value, $content);
		}
		if(isset($_SESSION['user']))
		{
			foreach($_SESSION['user'] as $key => $value){
				$content = str_replace('%' . $key . '%', $value, $content);
			}
		}
		return $content;
	}
?>