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
			sprintf('%s%s', $this->PHash, $this->PSand)
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
	TransmitSession(bool $Overshadow=FALSE):
	static {

		$SessionName = (
			$Overshadow
			? Library::Get(Library::ConfSessionOvershadow)
			: Library::Get(Library::ConfSessionName)
		);

		$SessionPath = '/';
		$SessionExpire = strtotime(Library::Get(Library::ConfSessionExpire) ?: '+1 week');
		$SessionData = $this->GenerateSessionData();

		setcookie(
			$SessionName,
			$SessionData,
			$SessionExpire,
			$SessionPath
		);

		return $this;
	}

	public function
	DestroySession():
	static {

		// destroy an overshadow session first so that an admin may
		// fall back onto their normal session.

		$SessionName = (
			isset($_COOKIE[Library::Get(Library::ConfSessionOvershadow)])
			? Library::Get(Library::ConfSessionOvershadow)
			: Library::Get(Library::ConfSessionName)
		);

		$SessionPath = '/';
		$SessionExpire = strtotime('-69 days');
		$SessionData = '';

		setcookie(
			$SessionName,
			$SessionData,
			$SessionExpire,
			$SessionPath
		);

		return $this;
	}

	public function
	ValidateSessionHash(string $Hash):
	bool {

		return ($Hash === $this->GenerateSessionHash());
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Get():
	?static {

		$Overshadowed = isset($_COOKIE[Library::Get(Library::ConfSessionOvershadow)]);

		$SessionName = (
			$Overshadowed
			? Library::Get(Library::ConfSessionOvershadow)
			: Library::Get(Library::ConfSessionName)
		);

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

		if(!$User->ValidateSessionHash($Data->UserHash))
		return NULL;

		////////

		if(!$Overshadowed)
		if($User->HasItBeenSinceSeen())
		$User
		->UpdateTimeSeen()
		->UpdateRemoteAddr();

		////////

		return $User;
	}

}

