<?php

namespace Nether\User;
use Nether;

use Exception;
use Nether\Database\Verse;
use Nether\Object\Datastore;

#[Nether\Database\Meta\TableClass('Users')]
class Entity
extends Nether\Database\Prototype {

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Nether\Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Nether\Database\Meta\TypeChar(Size: 24, Nullable: FALSE, Variable: TRUE)]
	#[Nether\Database\Meta\FieldIndex]
	public string
	$Alias;

	#[Nether\Database\Meta\TypeChar(Size: 255, Nullable: FALSE, Variable: TRUE)]
	#[Nether\Database\Meta\FieldIndex]
	public string
	$Email;

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	#[Nether\Database\Meta\FieldIndex]
	public int
	$TimeCreated;

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	#[Nether\Database\Meta\FieldIndex]
	public int
	$TimeSeen;

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE, Default: 0)]
	#[Nether\Database\Meta\FieldIndex]
	public int
	$TimeBanned;

	#[Nether\Database\Meta\TypeChar(Size: 64)]
	#[Nether\Database\Meta\FieldIndex]
	public ?string
	$AuthAppleID;

	#[Nether\Database\Meta\TypeChar(Size: 64)]
	#[Nether\Database\Meta\FieldIndex]
	public ?string
	$AuthGoogleID;

	#[Nether\Database\Meta\TypeChar(Size: 255)]
	public ?string
	$PHash;

	#[Nether\Database\Meta\TypeChar(Size: 128)]
	public ?string
	$PSand;

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
			'PHash' => password_hash($Password, PASSWORD_DEFAULT)
		]);

		return $this;
	}

	public function
	UpdateSandShift():
	static {

		$this->Update([
			'PSand' => static::GetPocketSand()
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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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
	GetPocketSand():
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
			'PSand'       => static::GetPocketSand()
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

		$Opt['Sort'] ??= 'alias-az';

		return;
	}

	static protected function
	FindExtendFilters(Verse $SQL, Datastore $Opt):
	void {

		$Searches = [];

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

		return;
	}

	static protected function
	FindExtendSorts(Verse $SQL, Datastore $Input):
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
