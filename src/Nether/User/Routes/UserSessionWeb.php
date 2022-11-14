<?php

namespace Nether\User\Routes;
use Nether;

use Nether\Atlantis\Routes\Web;
use Nether\Avenue\Meta\RouteHandler;
use Nether\Common\Datafilters;

class UserSessionWeb
extends Web {

	#[RouteHandler('/yolo')]
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
