<?php

namespace Nether\User;

use Nether\Database;

use Nether\User\Library;

class EntitySession
extends Entity {

	#[Database\Meta\TypeChar(Size: 64)]
	#[Database\Meta\FieldIndex]
	public ?string
	$AuthAppleID;

	#[Database\Meta\TypeChar(Size: 64)]
	#[Database\Meta\FieldIndex]
	public ?string
	$AuthDiscordID;

	#[Database\Meta\TypeChar(Size: 64)]
	#[Database\Meta\FieldIndex]
	public ?string
	$AuthGoogleID;

	#[Database\Meta\TypeChar(Size: 64)]
	#[Database\Meta\FieldIndex]
	public ?string
	$AuthGitHubID;

	#[Database\Meta\TypeChar(Size: 255)]
	public ?string
	$PHash;

	#[Database\Meta\TypeChar(Size: 128)]
	public ?string
	$PSand;

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

		setcookie($SessionName, $SessionData, [
			'expires'  => $SessionExpire,
			'path'     => $SessionPath,
			'secure'   => TRUE,
			'samesite' => 'Lax'
		]);

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

	public function
	ValidatePassword(string $Password):
	bool {

		if($this->PHash === NULL)
		return FALSE;

		return password_verify($Password, $this->PHash);
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

		if(!$Overshadowed) {
			if($User->HasItBeenSinceSeen()) {
				$User->UpdateTimeSeen();
				$User->UpdateRemoteAddr();
				$User->TransmitSession();
			}
		}

		////////

		return $User;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	GetByGitHubID(string $AuthID):
	?static {

		return static::GetByField('AuthGitHubID', $AuthID);
	}

	static public function
	GetByGitHubEmail(string $Email, string $AuthID):
	?static {

		$User = static::GetByField('Email', $Email);

		if(!$User)
		return NULL;

		if($User->AuthGitHubID && ($User->AuthGitHubID !== $AuthID))
		throw new Error\AuthMismatch;

		return $User;
	}

	static public function
	GetByAppleID(string $AuthID):
	?static {

		return static::GetByField('AuthAppleID', $AuthID);
	}

	static public function
	GetByAppleEmail(string $Email, string $AuthID):
	?static {

		$User = static::GetByField('Email', $Email);

		if(!$User)
		return NULL;

		if($User->AuthAppleID && ($User->AuthAppleID !== $AuthID))
		throw new Error\AuthMismatch;

		return $User;
	}

	static public function
	GetByDiscordID(string $AuthID):
	?static {

		return static::GetByField('AuthDiscordID', $AuthID);
	}

	static public function
	GetByDiscordEmail(string $Email, string $AuthID):
	?static {

		$User = static::GetByField('Email', $Email);

		if(!$User)
		return NULL;

		if($User->AuthDiscordID && ($User->AuthDiscordID !== $AuthID))
		throw new Error\AuthMismatch;

		return $User;
	}

	static public function
	GetByGoogleID(string $AuthID):
	?static {

		return static::GetByField('AuthGoogleID', $AuthID);
	}

	static public function
	GetByGoogleEmail(string $Email, string $AuthID):
	?static {

		$User = static::GetByField('Email', $Email);

		if(!$User)
		return NULL;

		if($User->AuthGoogleID && ($User->AuthGoogleID !== $AuthID))
		throw new Error\AuthMismatch;

		return $User;
	}

}

