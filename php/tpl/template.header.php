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
											<form class="navbar-search pull-left" method="post" action="?p=zoek" role="search">
							                    <select name="t" class="selectpicker ">
							                      <option value="1">Product titel</option>
							                      <option value="2">Rubriek</option>
							                    </select>
							                    <input class="search" name="s" type="text" placeholder="Zoek">
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
												 <a href=?p=register>Registreer</a>
												 <a href=?p=lostpassword>Wachtwoord vergeten</a>
											</form>';
	}
?>