<?php

namespace Nether\User\Routes;
use Nether;
use League;

use Nether\User\Library;
use Nether\Atlantis\Routes\Api;
use Nether\Avenue\Meta\RouteHandler;
use Nether\Common\Datafilters;

class UserSessionAPI
extends Api {

	#[RouteHandler('/api/user/session/login', Verb: 'POST')]
	public function
	HandleLogin():
	void {

		($this->Request->Data)
		->Username(Datafilters::TrimmedTextNullable(...))
		->Password(Datafilters::TypeStringNullable(...));

		////////

		if(!$this->Request->Data->Username)
		$this->Quit(1, 'missing Username field');

		if(!$this->Request->Data->Password)
		$this->Quit(2, 'missing Password field');

		////////

		$User = Nether\User\EntitySession::GetByAlias(
			$this->Request->Data->Username
		);

		if(!$User)
		$this->Quit(3, 'user not found');

		if(!$User->ValidatePassword($this->Request->Data->Password))
		$this->Quit(4, 'invalid password');

		if($User->TimeBanned)
		$this->Quit(5, 'account is banned');

		////////

		$User->TransmitSession();
		$User->UpdateTimeSeen();

		$this
		->SetPayload([
			'ID'    => $User->ID,
			'Alias' => $User->Alias,
			'CData' => $User->GenerateSessionData()
		]);

		return;
	}

	#[RouteHandler('/api/user/session/logout', Verb: 'POST')]
	public function
	HandleLogout():
	void {

		$User = Nether\User\EntitySession::Get();
		$Payload = [ 'ID' => NULL, 'Alias' => NULL, 'CData' => NULL ];

		if($User) {
			$User->DestroySession();
			$Payload['ID'] = $User->ID;
			$Payload['Alias'] = $User->Alias;
		}

		$this
		->SetPayload($Payload);

		return;
	}

	#[RouteHandler('/api/user/session/status')]
	public function
	HandleStatus():
	void {

		$User = Nether\User\EntitySession::Get();
		$Payload = [ 'ID' => NULL, 'Alias' => NULL, 'CData' => NULL ];

		if($User) {
			$Payload['ID'] = $User->ID;
			$Payload['Alias'] = $User->Alias;
			$Payload['CData'] = $User->GenerateSessionData();
		}

		$this
		->SetPayload($Payload);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////



}
