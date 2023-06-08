<?php

namespace Nether\User\Error;

use Exception;
use Nether\User\Entity;

class AccountBanned
extends Exception {

	public function
	__Construct(?Entity $User=NULL) {
		parent::__Construct();
		return;
	}

}
