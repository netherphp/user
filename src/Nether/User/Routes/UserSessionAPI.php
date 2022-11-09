<?php

namespace Nether\User\Routes;

use Nether\Atlantis\Routes\Api;
use Nether\Avenue\Meta\RouteHandler;

class UserSessionAPI
extends Api {

	#[RouteHandler('/api/user/session/login')]
	public function
	SessionLogin():
	void {

		$this->SetMessage('TODO');

		return;
	}

	#[RouteHandler('/api/user/session/logout')]
	public function
	SessionLogout():
	void {

		$this->SetMessage('TODO');

		return;
	}

}
