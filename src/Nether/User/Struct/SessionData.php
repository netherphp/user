<?php

namespace Nether\User\Struct;
use Nether;

use Nether\Common\Datafilters;

class SessionData
extends Nether\Object\Prototype {

	public int
	$UserID;

	public string
	$UserHash;

	public function
	__ToString():
	string {

		return $this->Encode();
	}

	public function
	Encode():
	string {

		return Datafilters::Base64Encode(json_encode([
			'UserID'   => $this->UserID,
			'UserHash' => $this->UserHash
		]));
	}

	static public function
	Decode(string $Input):
	?static {

		if(!$Input)
		return NULL;

		////////

		// make sure no ducks are trying to get in here the stupid way
		// until php 8.3 adds json_validate.

		$JSON = Datafilters::Base64Decode($Input);

		if(!$JSON || !str_contains($JSON, 'UserID'))
		return NULL;

		$Data = json_decode($JSON);

		if(!is_object($Data))
		return NULL;

		if(!isset($Data->UserID) || !isset($Data->UserHash))
		return NULL;

		////////

		return new static($Data);
	}

}
