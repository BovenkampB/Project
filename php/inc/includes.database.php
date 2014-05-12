<?php
	GLOBAL $DB;

	function connectToServer()
	{
		$DB = mssql_connect(
				$_CONFIG['mssql']['host'],
				$_CONFIG['mssql']['user'],
				$_CONFIG['mssql']['password']
			)or die("Kon geen verbinding maken met de SQL Server *sadpanda*");

		mssql_select_db($_CONFIG['mssql']['database'], $DB)
			or die("Kon geen verbinding maken met de database *sadpanda*");
	}

	/*
	function runQuery($query)
	{
		return mssql_query($query);
	}

	function numRows($result)
	{
		return mssql_num_rows($result);
	}

	function fetchArray($result)
	{
		return mssql_fetch_array($result);
	}*/
	function closeConnection()
	{
		mssql_close($DB);
	}
?>