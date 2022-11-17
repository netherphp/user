<?php

namespace Nether\User;
use Nether;
use Nether\Database\Verse;
use Nether\Object\Datastore;

#[Nether\Database\Meta\TableClass('UserAccessTypes')]
#[Nether\Database\Meta\MultiFieldIndex([ 'EntityID', 'Key' ], Unique: TRUE)]
#[Nether\Database\Meta\InsertReuseUnique]
#[Nether\Database\Meta\InsertUpdate]
class EntityAccessType
extends Nether\Database\Prototype {

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Nether\Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Nether\Database\Meta\ForeignKey('Users', 'ID')]
	public int
	$EntityID;

	#[Nether\Database\Meta\TypeVarChar(Size: 16)]
	public string
	$Key;

	#[Nether\Database\Meta\TypeInt]
	public int
	$Value;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	DropByEntityKey(int $EntityID, string $Key):
	void {

		$Table = static::GetTableInfo();

		(Nether\Database::Get())
		->NewVerse()
		->Delete($Table->Name)
		->Where('`EntityID`=:EntityID AND `Key`=:Key')
		->Query([
			'EntityID' => $EntityID,
			'Key'      => $Key
		]);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	FindExtendOptions(Datastore $Input):
	void {

		$Input->EntityID ??= NULL;

		return;
	}

	static protected function
	FindExtendFilters(Verse $SQL, Datastore $Input):
	void {

		if($Input->EntityID !== NULL)
		$SQL->Where('Main.EntityID=:EntityID');

		return;
	}

}
