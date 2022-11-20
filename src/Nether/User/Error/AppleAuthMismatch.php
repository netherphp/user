<?php

namespace Nether\User\Error;

use Exception;

class AppleAuthMismatch
extends Exception {

	public function
	__Construct() {
		parent::__Construct('apple auth and local auth mismatch');
		return;
	}

}
