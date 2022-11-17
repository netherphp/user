<?php

namespace Nether\User\Routes;
use Nether;

use Nether\Atlantis\Routes\ProtectedWeb;
use Nether\Avenue\Meta\RouteHandler;
use Nether\User\Meta\RouteAccessTypeUser;

class UserDashboardWeb
extends ProtectedWeb {

	#[RouteHandler('/dashboard')]
	#[RouteAccessTypeUser]
	public function
	PageDashboard():
	void {

		($this->App->Surface)
		->Wrap('user/dashboard/index');

		return;
	}

	#[RouteHandler('/dashboard/settings/email')]
	#[RouteAccessTypeUser]
	public function
	PageEmail():
	void {

		($this->App->Surface)
		->Wrap('user/dashboard/email');

		return;
	}

	#[RouteHandler('/dashboard/settings/password')]
	#[RouteAccessTypeUser]
	public function
	PagePassword():
	void {

		($this->App->Surface)
		->Wrap('user/dashboard/password');

		return;
	}

	#[RouteHandler('/dashboard/settings/auth')]
	#[RouteAccessTypeUser]
	public function
	PageAuth():
	void {

		($this->App->Surface)
		->Wrap('user/dashboard/auth');

		return;
	}

}
