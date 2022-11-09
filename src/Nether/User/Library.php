<?php

namespace Nether\User;
use Nether;

class Library {

	static public function
	Init(Nether\Object\Datastore $Config, ...$Argv):
	void {

		if(isset($Argv['App']) && is_object($Argv['App']))
		if($Argv['App'] instanceof Nether\Atlantis\Engine)
		static::InitWithAtlantisEngine($Argv['App']);

		return;
	}

	static protected function
	InitWithAtlantisEngine(Nether\Atlantis\Engine $App):
	void {

		$LocalPath = dirname(__FILE__);
		$Scanner = new Nether\Avenue\RouteScanner("{$LocalPath}/Routes");
		$Map = $Scanner->Generate();

		////////

		$Map['Verbs']->Each(
			fn(Nether\Object\Datastore $Handlers)=>
			$App->Router->AddHandlers($Handlers)
		);

		$Map['Errors']->Each(
			fn(Nether\Avenue\Meta\RouteHandler $Handler, int $Code)
			=> $App->Router->AddErrorHandler($Code, $Handler)
		);

		return;
	}

}
