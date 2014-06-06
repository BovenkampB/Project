<?php
	GLOBAL $DB;
	function connectToServer()
	{
		GLOBAL $_CONFIG, $DB;

		$DB = sqlsrv_connect($_CONFIG['mssql']['host'], array('UID'=>$_CONFIG['mssql']['user'], 'PWD'=>$_CONFIG['mssql']['password'], 'Database'=>$_CONFIG['mssql']['database']));
		if(!$DB) {
 			echo "<h1><b>Kon geen verbinding maken met de database *sadpanda*.</b></h1>";
		    die( print_r( sqlsrv_errors(), true));
		}
	}

	function runQuery($query)
	{
		GLOBAL $DB;
		return sqlsrv_query($DB, $query);
	}

	function runPreparedQuery($query, $params, $options = array())
	{
		GLOBAL $DB;
		return sqlsrv_query($DB, $query, $params, $options);
	}

	function numRows($result)
	{
		return sqlsrv_num_rows($result);
	}

	function fetchArray($result)
	{
		return sqlsrv_fetch_array($result);
	}

	function closeConnection()
	{
		sqlsrv_close($DB);
	}

	function prepareQuery($query, $params = array())
	{
		return sqlsrv_prepare($DB, $query, $params);
	}
?>