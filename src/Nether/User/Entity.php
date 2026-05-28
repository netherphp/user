<?php

namespace Nether\User;
use Nether;

use Nether\Common;
use Nether\Database;
use Nether\User;

use Exception;
use Stringable;
use Nether\Common\Datastore;
use Nether\Common\Prototype\ConstructArgs;
use Nether\Common\Meta\PropertyFactory;

#[Database\Meta\TableClass('Users', 'U')]
class Entity
extends Nether\Database\Prototype
implements
	Stringable,
	Common\Interfaces\ToString {

	use
	Common\Package\StringableAsToString;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeChar(Size: 36, Nullable: FALSE)]
	#[Database\Meta\FieldIndex]
	public string
	$UUID;

	#[Database\Meta\TypeChar(Size: 24, Variable: TRUE, Default: NULL)]
	#[Database\Meta\FieldIndex]
	#[Database\Meta\NullifyEmptyValue]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ 'Nether\\User\\Library', 'FilterAlias' ])]
	public ?string
	$Alias;

	#[Database\Meta\TypeChar(Size: 255, Nullable: FALSE, Variable: TRUE)]
	#[Database\Meta\FieldIndex]
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Text::class, 'Email' ])]
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
	#[Common\Meta\PropertyPatchable]
	#[Common\Meta\PropertyFilter([ Common\Filters\Numbers::class, 'IntType' ])]
	public int
	$Admin;

	#[Database\Meta\TypeIntSmall(Unsigned: TRUE, Default: 0)]
	public int
	$Activated;

	#[Database\Meta\TypeVarChar(Size: 64)]
	public string
	$RemoteAddr;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[PropertyFactory('FromTime', 'TimeSeen')]
	public Common\Date
	$DateSeen;

	#[PropertyFactory('FromTime', 'TimeCreated')]
	public Common\Date
	$DateCreated;

	#[PropertyFactory('FromTime', 'TimeBanned')]
	public Common\Date
	$DateBanned;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected Datastore
	$AccessTypes;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	ToString():
	string {

		$Alias = $this->Alias ?: $this->Email;

		return "User\Entity({$this->ID}, {$Alias})";
	}

	protected function
	OnReady(ConstructArgs $Args):
	void {

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetAlias(bool $EmailFallback=TRUE):
	string {

		if($this->Alias)
		return $this->Alias;

		if($EmailFallback) {
			$Email = new Common\Units\EmailAddress($this->Email);
			return $Email->GetLocal();
		}

		return $this->UUID;
	}

	public function
	GetEmail():
	string {

		return $this->Email;
	}

	public function
	GetSinceBanned():
	Common\Units\Timeframe {

		return new Common\Units\Timeframe(
			Start: $this->TimeBanned,
			Precision: 3,
			EmptyString: 'Now'
		);
	}

	public function
	GetSinceCreated():
	Common\Units\Timeframe {

		return new Common\Units\Timeframe(
			Start: $this->TimeCreated,
			Precision: 3,
			EmptyString: 'Now'
		);
	}

	public function
	GetSinceSeen():
	Common\Units\Timeframe {

		return new Common\Units\Timeframe(
			Start: $this->TimeSeen,
			Precision: 3,
			EmptyString: 'Now'
		);
	}

	public function
	IsActivated():
	bool {

		return ($this->Activated > 0);
	}

	public function
	IsAdmin(int $MinLevel=1):
	bool {

		return ($this->Admin >= $MinLevel);
	}

	public function
	IsBanned():
	bool {

		return ($this->TimeBanned > 0);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	GetAccessTypes(bool $Force=FALSE):
	Datastore {

		if(isset($this->AccessTypes) && !$Force)
		return $this->AccessTypes->Copy();

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

		return $this->AccessTypes->Copy();
	}

	public function
	PushAccessType(EntityAccessType $AT):
	static {

		$this->AccessTypes->Shove($AT->Key, $AT);

		return $this;
	}

	public function
	HasAccessType(string $Key, mixed $Val=1):
	bool {

		$Access = $this->GetAccessTypes();

		if(!$Access->HasKey($Key))
		return FALSE;

		return $Access[$Key]->IsEq($Val);
	}

	public function
	HasAccessTypeOrAdmin(string $Key, mixed $Val=1, int $Admin=1):
	bool {

		if($this->IsAdmin($Admin))
		return TRUE;

		if($this->HasAccessType($Key, $Val))
		return TRUE;

		return FALSE;
	}

	public function
	HasItBeenSinceSeen(?int $Diff=NULL):
	bool {

		if($Diff === NULL)
		$Diff = Library::$Config[Library::ConfUpdateSeenAfter];

		return (time() - $this->TimeSeen) >= $Diff;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Common\Meta\Date('2026-05-27')]
	public function
	DescribeForPublicAPI():
	array {

		$Output = [
			'ID'    => $this->ID,
			'UUID'  => $this->UUID,
			'Email' => $this->GetEmail(),
			'Alias' => $this->GetAlias(),

			'TimeCreated' => $this->TimeCreated,
			'TimeSeen'    => $this->TimeSeen,
			'TimeBanned'  => $this->TimeBanned,
			'RemoteAddr'  => $this->RemoteAddr,

			'Activated'   => $this->Activated,
			'Admin'       => $this->Admin
		];

		return $Output;
	}

	#[Common\Meta\Date('2026-05-26')]
	public function
	GetDataAttr(?iterable $More=NULL, bool $Prefix=FALSE):
	Common\Datastore {

		$Output = new Common\Datastore([
			'id'   => $this->ID,
			'uuid' => $this->UUID
		]);

		if(is_iterable($More))
		$Output->MergeRight($More);

		if($Prefix)
		$Output->RemapKeys(
			fn(string $K, string $V)
			=> [ "data-{$K}"=> $V ]
		);

		return $Output;
	}

	#[Common\Meta\Date('2026-05-26')]
	public function
	GetDataAttrForHTML(?iterable $More=NULL, bool $Prefix=TRUE):
	string {

		$Output = $this->GetDataAttr($More, $Prefix);

		$Output->RemapKeys(
			fn(string $K, string $V)
			=> [ $K=> sprintf('%s="%s"', $K, htmlentities($V)) ]
		);

		return $Output->Join(' ');
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
			'UUID'        => Common\UUID::V7(),
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
				$TableMain->GetAliasedPK('Main')
			))
			->Where(sprintf(
				'%s=:WithAccessType',
				$TableAT->GetAliasedPK('QUAT')
			))
			->Group($TableMain->GetAliasedPK('Main'));
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
			case 'email-az':
				$SQL->Sort('Main.Email', $SQL::SortAsc);
			break;
			case 'email-za':
				$SQL->Sort('Main.Email', $SQL::SortDesc);
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
