<?php

namespace Nether\User\Routes;

use Nether\Atlantis\Routes\Web;
use Nether\Avenue\Meta\RouteHandler;

class UserSessionWeb
extends Web {

	#[RouteHandler('/login')]
	public function
	PageLogin():
	void {

		($this->App->Surface)
		->Wrap('user/login');

		return;
	}

	#[RouteHandler('/logout')]
	public function
	PageLogout():
	void {

		($this->App->Surface)
		->Wrap('user/logout');

		return;
	}

}
