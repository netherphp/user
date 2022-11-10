<?php

namespace Nether\User;
use Nether;

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

	#[Nether\Database\Meta\TypeChar(Size: 254, Nullable: FALSE, Variable: TRUE)]
	#[Nether\Database\Meta\FieldIndex]
	public string
	$Email;

	#[Nether\Database\Meta\TypeChar(Size: 64)]
	#[Nether\Database\Meta\FieldIndex]
	public ?string
	$AuthAppleID;

	#[Nether\Database\Meta\TypeChar(Size: 64)]
	#[Nether\Database\Meta\FieldIndex]
	public ?string
	$AuthGoogleID;

	#[Nether\Database\Meta\TypeChar(Size: 128)]
	public ?string
	$PHash;

	#[Nether\Database\Meta\TypeChar(Size: 128)]
	public ?string
	$PSand;

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
	FindExtendOptions(Datastore $Opt):
	void {

		$Opt['Alias'] ??= NULL;
		$Opt['Email'] ??= NULL;

		$Opt['Search'] ??= NULL;
		$Opt['SearchAlias'] ??= NULL;
		$Opt['SearchEmail'] ??= NULL;

		return;
	}

	static public function
	FindExtendFilters(Verse $SQL, Datastore $Opt):
	void {

		$Searches = [];

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

}
