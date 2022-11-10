<?php

namespace Nether\User;
use Nether;

class EntitySession
extends Entity {

	public function
	TransmitSession():
	static {

		$CData = hash(
			'sha512',
			($this->PHash.$this->PSand)
		);

		setcookie(
			'nuser',
			sprintf('%s-%s', $this->ID, $CData),
			strtotime('+15 days')
		);

		return $this;
	}

	public function
	DestroySession():
	static {

		setcookie(
			'nuser', '',
			strtotime('-69 days')
		);

		return $this;
	}

}

