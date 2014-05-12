<?php
	GLOBAL $DB, $_CONFIG;

	/*
		Definieer Constanten. 
		Uitleg: Scheelt later typewerk als er iets gewijzigd moet worden!
	*/ 
	DEFINE('inc', 'inc/includes.');
	DEFINE('tpl', 'tpl/template.');
	DEFINE('fnc', 'fnc/functions.');

	/*
		Include De Bestanden
		Uitleg: Deze bestanden bevatten alle functies die wij nodig gaan hebben.
	*/
	include(inc . 'config.php');
	include(inc . 'template.php');
	include(inc . 'database.php');
	include(fnc . 'global.php');
	
	/*
		Verbinden Met De Database.
		Uitleg: We gebruiken een PDO klasse. Deze heeft prepared statements en
				blijkt in de praktijk een veilig systeem te zijn.
	*/
	$DB = connectPDO($_CONFIG['mysql']['host'], $_CONFIG['mysql']['user'],$_CONFIG['mysql']['database'], $_CONFIG['mysql']['password']);

	/*
		Haal Alle Site Paramaters Op Uit De Database
	*/
	$site_settings = $DB->prepare("SELECT * FROM site_settings");
	$site_settings->execute();
	while($row = $site_settings->fetch(PDO::FETCH_ASSOC))
	{
		$_CONFIG['params'][$row['key']] = $row['value'];
	}

	/*
		Lees Pagina Uit De Link ?p=
		Uitleg: Hierdoor is het eenvoudig nieuwe links toe te voegen en te wijzigen.
	*/
	if(!isset($_GET['p'])){
		$page = 'start';
	}else{
		$page = $_GET['p'];
	}

	/*
		Laad De Pagina
	*/
	loadPage($page);

	/*
		Destroy De Database Verbinding.
		Uitleg: Deze hebben we niet meer nodig.
	*/
	$DB = null;
?>