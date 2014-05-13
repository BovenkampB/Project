<?php
	GLOBAL $_CONFIG;

	if(isset($_POST))
	{
		if(isset($_POST['loginHeader']) && !isset($_SESSION['user']))
		{
			userLogin($_POST);
		}
	}

	if(isset($_SESSION['user']['loggedin'])){
		$_CONFIG['params']['loginPart'] = '<ul class="nav pull-right">
											<form class="navbar-search pull-left" role="search"> 
												<input class="search" type="text" placeholder="Zoek Product">
												<button type="submit" class="btn">Zoek</button>
											</form>
											<li class="nav dropdown pull-right">
												<a href="#" class="dropdown-toggle" data-toggle="dropdown">Ingelogd: %voornaam% %achternaam% <b class="caret"></b></a>
												<ul class="dropdown-menu pull-right">
													<li><a href="?p=settings">Instellingen</a></li>
													<li class="divider"></li>
													<li><a href="?p=logout">Log uit</a></li>
												</ul>
											</li>
										</ul> ';
	}else{
		$_CONFIG['params']['loginPart'] = '<form method="post" class="navbar-form pull-right">								
												<input class="span2" type="text" placeholder="Email" name="email">								 
												<input class="span2" type="password" placeholder="Password" name="password">								 
												<button type="submit" name="loginHeader" value="Login" class="btn">Login</button>
												<br/>
												 <a href=#>Registreer</a>
												 <a href=#>Wachtwoord vergeten</a>
											</form>';
	}
?>