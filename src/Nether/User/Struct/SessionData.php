<?php

namespace Nether\User\Struct;

use Nether\Common;

use Stringable;

class SessionData
extends Common\Prototype
implements Stringable {

	public int
	$UserID;

	public string
	$UserHash;

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	__ToString():
	string {

		return $this->Encode();
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	public function
	Encode():
	string {
	/*//
	@date 2022-11-11
	base64 encode a json description of this object.
	//*/

		return Common\Filters\Text::Base64Encode(json_encode([
			'UserID'   => $this->UserID,
			'UserHash' => $this->UserHash
		]));
	}

	static public function
	Decode(string $Input):
	?static {
	/*//
	@date 2022-11-11
	base64 decode the json description of an object.
	//*/

		if(!$Input)
		return NULL;

		////////

		// make sure no ducks are trying to get in here the stupid way
		// until php 8.3 adds json_validate.

		$JSON = Common\Filters\Text::Base64Decode($Input);

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

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	New(int $UserID=NULL, string $UserHash=NULL):
	static {

		return new static([
			'UserID'   => $UserID,
			'UserHash' => $UserHash
		]);
	}

}
