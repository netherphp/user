<?php

namespace Nether\User;

use Nether\Common;
use Nether\Database;

#[Database\Meta\TableClass('UserAccessTypes')]
#[Database\Meta\MultiFieldIndex([ 'EntityID', 'Key' ], Unique: TRUE)]
#[Database\Meta\InsertReuseUnique]
#[Database\Meta\InsertUpdate]
class EntityAccessType
extends Database\Prototype {

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[Database\Meta\TypeIntBig(Unsigned: TRUE, AutoInc: TRUE)]
	#[Database\Meta\PrimaryKey]
	public int
	$ID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	#[Database\Meta\ForeignKey('Users', 'ID')]
	public int
	$EntityID;

	#[Database\Meta\TypeIntBig(Unsigned: TRUE)]
	public int
	$TimeCreated;

	#[Database\Meta\TypeVarChar(Size: 64)]
	public string
	$Key;

	#[Database\Meta\TypeInt]
	public int
	$Value;

	////////

	#[Database\Meta\TableJoin('EntityID')]
	public Entity
	$Entity;


	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	protected function
	OnReady(Common\Prototype\ConstructArgs $Args):
	void {

		if($Args->InputHas('U_ID'))
		$this->Entity = Entity::FromPrefixedDataset($Args->Input, 'U_');

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	DescribeForPublicAPI():
	array {

		$Output = [
			'ID'          => $this->ID,
			'EntityID'    => $this->EntityID,
			'TimeCreated' => $this->TimeCreated,
			'Key'         => $this->Key,
			'Value'       => $this->Value
		];

		return $Output;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	IsKey(string $Test):
	bool {

		return ($this->Key === $Test);
	}

	public function
	IsNotKey(string $Test):
	bool {

		return ($this->Key !== $Test);
	}

	public function
	IsEq(int $Test):
	bool {

		return ($this->Value === $Test);
	}

	public function
	IsNot(int $Test):
	bool {

		return ($this->Value !== $Test);
	}

	public function
	IsGT(int $Test):
	bool {

		return ($this->Value > $Test);
	}

	public function
	IsGTE(int $Test):
	bool {

		return ($this->Value >= $Test);
	}

	public function
	IsLT(int $Test):
	bool {

		return ($this->Value < $Test);
	}

	public function
	IsLTE(int $Test):
	bool {

		return ($this->Value < $Test);
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	DropByEntityKey(int $EntityID, string $Key):
	void {

		$Table = static::GetTableInfo();
		$DBM = new Database\Manager;
		$DBC = $DBM->Get('Default');

		($DBC)
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
	FindExtendOptions(Common\Datastore $Input):
	void {

		$Input['EntityID'] ??= NULL;
		$Input['Key'] ??= NULL;
		$Input['Index'] ??= FALSE;

		return;
	}

	static protected function
	FindExtendFilters(Database\Verse $SQL, Common\Datastore $Input):
	void {

		$Table = static::GetTableInfo();

		if($Input['EntityID'] !== NULL)
		$SQL->Where(sprintf(
			'%s=:EntityID',
			$Table->GetPrefixedField('Main', 'EntityID')
		));

		if($Input['Key'] !== NULL)
		$SQL->Where(sprintf(
			'%s=:Key',
			$Table->GetPrefixedField('Main', 'Key')
		));

		return;
	}

	static protected function
	FindExtendSorts(Database\Verse $SQL, Common\Datastore $Input):
	void {

		$Table = static::GetTableInfo();

		if($Input['Index'] !== NULL) {
			$SQL
			->Group($Table->GetAliasedPK('Main'))
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

		$Input = new Common\Datastore($Input);

		$Input->BlendRight([
			'TimeCreated' => time()
		]);

		return parent::Insert($Input);
	}

}
