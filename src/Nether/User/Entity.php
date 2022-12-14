<?php

namespace Nether\User;
use Nether;

use Nether\Common;
use Nether\Database;
use Nether\User;

use Exception;
use Stringable;
use Nether\Object\Datastore;
use Nether\Object\Prototype\ConstructArgs;
use Nether\Object\Meta\PropertyFactory;

#[Database\Meta\TableClass('Users')]
class Entity
extends Nether\Database\Prototype
implements Stringable {

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeChar(Size: 24, Variable: TRUE, Default: NULL)]
	#[Database\Meta\FieldIndex]
	public ?string
	$Alias;

	#[Database\Meta\TypeChar(Size: 255, Nullable: FALSE, Variable: TRUE)]
	#[Database\Meta\FieldIndex]
	public string
	$Email;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	#[Database\Meta\FieldIndex]
	public int
	$TimeCreated;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	#[Database\Meta\FieldIndex]
	public int
	$TimeSeen;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	#[Database\Meta\FieldIndex]
	public int
	$TimeBanned;

	#[Database\Meta\TypeIntSmall(Unsigned: TRUE, Default: 0)]
	public int
	$Admin;

	#[Database\Meta\TypeIntSmall(Unsigned: TRUE)]
	public bool
	$Activated;

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

	#[Database\Meta\TypeVarChar(Size: 64)]
	public string
	$RemoteAddr;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[PropertyFactory('FromTime', 'TimeCreated')]
	public Common\Date
	$DateCreated;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected Datastore
	$AccessTypes;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__ToString():
	string {

		$Alias = $this->Alias ?? $this->Email;

		return "User({$this->ID}, {$Alias})";
	}

	protected function
	OnReady(ConstructArgs $Args):
	void {

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	IsAdmin(int $MinLevel=1):
	bool {

		return (
			TRUE
			&& $this->Admin
			&& ($this->Admin >= $MinLevel)
		);
	}

	public function
	GetAccessTypes(bool $Force=FALSE):
	Datastore {

		if(isset($this->AccessTypes) && !$Force)
		return $this->AccessTypes;

		////////

		$this->AccessTypes = new Datastore;
		$Val = NULL;

		////////

		$Result = Nether\User\EntityAccessType::Find([
			'EntityID' => $this->ID
		]);

		foreach($Result as $Val)
		$this->AccessTypes->Shove($Val->Key, $Val);

		////////

		return $this->AccessTypes;
	}

	public function
	HasItBeenSinceSeen(?int $Diff=NULL):
	bool {

		if($Diff === NULL)
		$Diff = Library::$Config[Library::ConfUpdateSeenAfter];

		return (time() - $this->TimeSeen) >= $Diff;
	}

	public function
	DisablePassword():
	static {

		$this->Update([
			'PHash' => NULL
		]);

		return $this;
	}

	public function
	UpdatePassword(string $Password):
	static {

		$this->Update([
			'PHash' => static::GeneratePasswordHash($Password)
		]);

		return $this;
	}

	public function
	UpdateSandShift():
	static {

		$this->Update([
			'PSand' => static::GeneratePocketSand()
		]);

		return $this;
	}

	public function
	UpdateTimeBanned(int $When=-1):
	static {

		if($When === -1)
		$When = time();

		$this->Update([
			'TimeBanned' => $When
		]);

		return $this;
	}

	public function
	UpdateTimeSeen(int $When=-1):
	static {

		if($When === -1)
		$When = time();

		$this->Update([
			'TimeSeen' => $When
		]);

		return $this;
	}

	public function
	UpdateRemoteAddr(?string $RemoteAddr=NULL):
	static {

		if($RemoteAddr === NULL)
		$RemoteAddr = (
			isset($_SERVER['REMOTE_ADDR'])
			? $_SERVER['REMOTE_ADDR']
			: NULL
		);

		$this->Update([
			'RemoteAddr' => $RemoteAddr
		]);

		return $this;
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
	Config(string $Key):
	mixed {

		return Library::$Config[$Key];
	}

	static public function
	GetBy(int|string $Input):
	?static {

		if(is_string($Input)) {
			if(is_numeric($Input))
			return static::GetByID((int)$Input);

			elseif(str_contains($Input, '@'))
			return static::GetByEmail($Input);

			else
			return static::GetByAlias($Input);
		}

		return static::GetByID((int)$Input);
	}

	static public function
	GetByAlias(string $Alias):
	?static {

		return static::GetByField('Alias', $Alias);
	}

	static public function
	GetByEmail(string $Email):
	?static {

		return static::GetByField('Email', $Email);
	}

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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	GeneratePasswordHash(string $Password):
	string {

		return password_hash($Password, PASSWORD_DEFAULT);
	}

	static public function
	GeneratePocketSand():
	string {

		return hash('sha512', random_bytes(128));
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Dataset = new Datastore([
			'TimeCreated' => time(),
			'PHash'       => NULL,
			'PSand'       => static::GeneratePocketSand(),
			'Activated'   => 0
		]);

		$Dataset->MergeRight($Input);

		return parent::Insert($Dataset);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Datastore $Opt):
	void {

		$Opt['Alias'] ??= NULL;
		$Opt['Email'] ??= NULL;

		$Opt['Search'] ??= NULL;
		$Opt['SearchAlias'] ??= NULL;
		$Opt['SearchEmail'] ??= NULL;

		$Opt['WithAccessType'] ??= NULL;

		$Opt['Sort'] ??= 'alias-az';

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Datastore $Opt):
	void {

		$Searches = [];
		$TableMain = static::GetTableInfo();
		$TableAT = EntityAccessType::GetTableInfo();

		////////

		if($Opt['Alias'] !== NULL) {
			$SQL->Where('Main.Alias LIKE :Alias');
		}

		if($Opt['Email'] !== NULL) {
			$SQL->Where('Main.Email LIKE :Email');
		}

		if($Opt['Search'] !== NULL) {
			if($Opt['SearchAlias']) {
				$Opt['SearchAlias'] = "%{$Opt['Search']}%";
				$Searches[] = 'Main.Alias LIKE :SearchAlias';
			}

			if($Opt['SearchEmail']) {
				$Opt['SearchEmail'] = "%{$Opt['Search']}%";
				$Searches[] = 'Main.Email LIKE :SearchEmail';
			}

			if(count($Searches))
			$SQL->Where(join(' OR ', $Searches));
		}

		if($Opt['WithAccessType'] !== NULL) {
			$SQL
			->Join(sprintf(
				'%s ON %s=%s',
				$TableAT->GetAliasedTable('QUAT'),
				$TableAT->GetPrefixedField('QUAT', 'EntityID'),
				$TableMain->GetPrefixedKey('Main')
			))
			->Where(sprintf(
				'%s=:WithAccessType',
				$TableAT->GetPrefixedKey('QUAT')
			))
			->Group($TableMain->GetPrefixedKey('Main'));
		}

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Datastore $Input):
	void {

		switch($Input['Sort']) {
			case 'alias-az':
				$SQL->Sort('Main.Alias', $SQL::SortAsc);
			break;
			case 'alias-za':
				$SQL->Sort('Main.Alias', $SQL::SortDesc);
			break;
			case 'newest':
				$SQL->Sort('Main.TimeCreated', $SQL::SortDesc);
			break;
			case 'oldest':
				$SQL->Sort('Main.TimeCreated', $SQL::SortAsc);
			break;
			case 'recent-seen':
				$SQL->Sort('Main.TimeSeen', $SQL::SortDesc);
			break;
			case 'recent-banned':
				$SQL->Sort('Main.TimeSeen', $SQL::SortDesc);
			break;
		}

		return;
	}

}
