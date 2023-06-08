<?php

namespace Nether\User\Error;

use Exception;

class AuthMismatch
extends Exception {

	public function
	__Construct() {
		parent::__Construct('auth and local auth mismatch');
		return;
	}

}
