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

		$RouterSource = $App->Router->GetSource();
		$RouterPath = dirname(__FILE__);

		////////

		// if the app is using a compiled route map then skip the dynamic
		// scanning for performance.

		if($RouterSource === Nether\Avenue\Library::RouteSourceFile)
		return;

		////////

		$Scanner = new Nether\Avenue\RouteScanner("{$RouterPath}/Routes");
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
