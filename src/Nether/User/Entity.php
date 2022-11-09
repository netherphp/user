<?php

namespace Nether\User;
use Nether;

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

}
