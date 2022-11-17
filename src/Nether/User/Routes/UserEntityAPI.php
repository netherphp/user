<?php

namespace Nether\User\Routes;
use Nether;
use League;

use Nether\User\Library;
use Nether\Atlantis\Routes\ProtectedAPI;
use Nether\Common\Datafilters;

use Nether\Avenue\Meta\RouteHandler;
use Nether\User\Meta\RouteAccessTypeUser;
use Nether\User\Meta\RouteAccessTypeAdmin;

class UserEntityAPI
extends ProtectedAPI {

	#[RouteHandler('/api/user/entity', Verb: 'GET')]
	#[RouteAccessTypeUser]
	public function
	EntityGet():
	void {

		$Entity = $this->App->User;

		$this->SetPayload([
			'ID'          => $Entity->ID,
			'Alias'       => $Entity->Alias,
			'TimeSeen'    => $Entity->TimeSeen,
			'TimeCreated' => $Entity->TimeCreated
		]);

		return;
	}

	#[RouteHandler('/api/user/entity', Verb: 'PATCH')]
	#[RouteAccessTypeUser]
	public function
	EntityPatch():
	void {

		$Entity = $this->App->User;

		return;
	}

}