<?php

namespace Nether\User\Error;

use Exception;

class GitHubAuthMismatch
extends Exception {

	public function
	__Construct() {
		parent::__Construct('github auth and local auth mismatch');
		return;
	}

}
