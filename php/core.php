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
?>