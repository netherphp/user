<?php

namespace Nether\User;
use Nether;

use Nether\Common\Datafilters;

class EntitySession
extends Entity {

	public function
	GenerateSessionHash():
	string {

		$CData = hash(
			'sha512',
			($this->PHash.$this->PSand)
		);

		return $CData;
	}

	public function
	GenerateSessionData():
	string {

		$CData = Struct\SessionData::New(
			UserID: $this->ID,
			UserHash: $this->GenerateSessionHash()
		);

		return $CData->Encode();
	}

	public function
	TransmitSession():
	static {

		setcookie(
			'nuser',
			$this->GenerateSessionData(),
			strtotime('+15 days')
		);

		return $this;
	}

	public function
	DestroySession():
	static {

		setcookie(
			'nuser', '',
			strtotime('-69 days')
		);

		return $this;
	}

	public function
	ValidateSessionHash(string $Hash):
	bool {

		return ($Hash === $this->GenerateSessionHash());
	}

	static public function
	Get():
	?static {

		if(!isset($_COOKIE['nuser']))
		return NULL;

		////////

		$Data = Struct\SessionData::Decode($_COOKIE['nuser']);

		if(!$Data || !$Data->UserID)
		return NULL;

		////////

		$User = Nether\User\EntitySession::GetByID($Data->UserID);

		if(!$User)
		return NULL;

		if(!$User->ValidateSessionHash($Data->UserHash))
		return NULL;

		return $User;
	}

}

