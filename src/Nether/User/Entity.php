<?php

namespace Nether\User;
use Nether;

#[Nether\Database\Meta\TableClass('Users')]
class Entity
extends Nether\Object\Prototype {

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Nether\Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Nether\Database\Meta\TypeVarChar(Size: 24)]
	public string
	$Alias;

	#[Nether\Database\Meta\TypeVarChar(Size: 255)]
	public string
	$Email;

}
