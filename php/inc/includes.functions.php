<?php

	function loadMenu()
	{

		$response = "";
		/*
		$query = "SELECT * FROM Rubriek";
		$source = runPreparedQuery($query, array());
		while($row = fetchArray($source))
		{
			$response .= '<li class="dropdown-submenu">
														<a href="?p=overzicht&amp;id='.$row['rubrieknummer'].'">'.$row['rubrieknaam'].'</a>';						 
														$query = 'SELECT * FROM Rubriek WHERE rubriek = ? ORDER BY volgnummer';
														$source = runPreparedQuery($query, array($row['rubrieknummer']), array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
														if($source != false)
														{
															if(numRows($source) > 0)
															{
																$added = false;
																while($dump = fetchArray($source))
																{
																	if(!$added)
																	{
																		$added = true;
																		$response .= '<ul class="dropdown-menu">';
																	}
																	$response .= '<li><a href="?p=overzicht&amp;id='.
																	$dump['rubrieknummer'].'">'
																	.$dump['rubrieknaam']/'</a></li>';
																}
																
																$response .= '</ul>';
															}
														}
														$response .= '</li>';
		}
	*/
	return $response;
	}
			
?>