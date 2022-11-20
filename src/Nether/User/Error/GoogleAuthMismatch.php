<?php

namespace Nether\User\Error;

use Exception;

class GoogleAuthMismatch
extends Exception {

	public function
	__Construct() {
		parent::__Construct('google auth and local auth mismatch');
		return;
	}

}
