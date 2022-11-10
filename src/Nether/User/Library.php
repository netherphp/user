<?php

namespace Nether\User;
use Nether;

class Library {

	static public function
	Init(Nether\Object\Datastore $Config, ...$Argv):
	void {

		// static::PrepareDefaultConfig($Config);

		// optional: register urls with atlantis.
		// this stack is making use of some oldschool php fuckery where it
		// won't cry about non-existing classes until that specific line of
		// code gets evaluated so this code can execute without crashing
		// if atlantis engine isn't installed. if you pass something that
		// smells like atlantis we'll check its not a duck first.

		if(isset($Argv['App']) && is_object($Argv['App']))
		if(method_exists($Argv['App'], 'GetProjectEnv'))
		if($Argv['App'] instanceof Nether\Atlantis\Engine)
		match($Argv['App']->Router->GetSource()) {
			Nether\Avenue\Library::RouteSourceScan
			=> static::InitWithAtlantisEngine($Argv['App']),
			default
			=> NULL
		};

		return;
	}

	static protected function
	InitWithAtlantisEngine(Nether\Atlantis\Engine $App):
	void {

		$RouterPath = dirname(__FILE__);

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

	static public function
	PrepareDefaultConfig(?Nether\Object\Datastore $Config=NULL):
	Nether\Object\Datastore {

		if($Config === NULL)
		$Config = new Nether\Object\Datastore;

		$Config->BlendRight([

		]);

		return $Config;
	}


}
