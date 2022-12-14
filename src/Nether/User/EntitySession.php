<?php

namespace Nether\User;
use Nether;

use Nether\Common;
use Nether\User;

use Nether\User\Library;
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

		$SessionName = Library::Get(Library::ConfSessionName);

		setcookie(
			$SessionName,
			$this->GenerateSessionData(),
			strtotime('+15 days'),
			'/'
		);

		return $this;
	}

	public function
	DestroySession():
	static {

		$SessionName = Library::$Config[Library::ConfSessionName];

		setcookie(
			$SessionName,
			'',
			strtotime('-69 days'),
			'/'
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

		$SessionName = Library::$Config[Library::ConfSessionName];

		if(!isset($_COOKIE[$SessionName]))
		return NULL;

		////////

		$Data = Struct\SessionData::Decode($_COOKIE[$SessionName]);

		if(!$Data || !$Data->UserID)
		return NULL;

		////////

		$User = EntitySession::GetByID($Data->UserID);

		if(!$User)
		return NULL;

		//if($User->TimeBanned)
		//return NULL;

		if(!$User->ValidateSessionHash($Data->UserHash))
		return NULL;

		////////

		if($User->HasItBeenSinceSeen())
		$User
		->UpdateTimeSeen()
		->UpdateRemoteAddr();

		////////

		return $User;
	}

}

