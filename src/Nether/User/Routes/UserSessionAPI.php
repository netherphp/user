<?php

namespace Nether\User\Routes;

use Nether\Atlantis\Routes\Api;
use Nether\Avenue\Meta\RouteHandler;

class UserSessionAPI
extends Api {

	#[RouteHandler('/api/user/session/login')]
	public function
	HandleLogin():
	void {

		echo $this->App->Router->GetSource();

		$this->SetMessage('TODO');

		return;
	}

	#[RouteHandler('/api/user/session/logout')]
	public function
	HandleLogout():
	void {

		$this->SetMessage('TODO');

		return;
	}

}
