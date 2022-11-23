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

	#[Nether\Database\Meta\TypeIntBig(Unsigned: TRUE)]
	public int
	$TimeCreated;

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

		$Input['EntityID'] ??= NULL;
		$Input['Index'] ??= FALSE;

		return;
	}

	static protected function
	FindExtendFilters(Verse $SQL, Datastore $Input):
	void {

		$Table = static::GetTableInfo();

		if($Input['EntityID'] !== NULL)
		$SQL->Where(sprintf(
			'%s=:EntityID',
			$Table->GetPrefixedField('Main', 'EntityID')
		));

		return;
	}

	static protected function
	FindExtendSorts(Verse $SQL, Datastore $Input):
	void {

		$Table = static::GetTableInfo();

		if($Input['Index'] !== NULL) {
			$SQL
			->Group($Table->GetPrefixedKey('Main'))
			->Sort($Table->GetPrefixedField('Main', 'Key'), $SQL::SortAsc);

			return;
		}

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Insert(iterable $Input):
	?static {

		$Input = new Nether\Object\Prototype($Input, [
			'TimeCreated' => time()
		]);

		return parent::Insert((array)$Input);
	}

}
