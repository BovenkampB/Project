<?php
	GLOBAL $DB, $_CONFIG;

	/*
		Definieer Constanten. 
		Uitleg: Scheelt later typewerk als er iets gewijzigd moet worden!
	*/ 
	DEFINE('inc', 'inc/includes.');
	DEFINE('tpl', 'tpl/template.');

	/*
		Include De Bestanden
		Uitleg: Deze bestanden bevatten alle functies die wij nodig gaan hebben.
	*/
	include(inc . 'config.php');
	include(inc . 'template.php');
	include(inc . 'database.php');
	include(inc . 'gebruikers.php');
	include(inc . 'functions.php');

	/*
		Verbinden met onze database
	*/
	connectToServer();
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
		Laad Alle Parameters
	*/
	$values = runQuery("SELECT * FROM Settings");
	while($row = fetchArray($values))
	{
		$_CONFIG['params'][$row['string']] = $row['value'];
	}

	$_CONFIG['params']['headerMenu'] = loadMenu();
	/*
		Laad De Pagina
	*/
	loadPage($page);
?>