<?php

namespace Nether\User;
use Nether;

use Nether\Common\Values;

class Library
extends Nether\Common\Library {

	const
	ConfUpdateSeenAfter = 'Nether.User.SeenUpdateAfter',
	ConfSessionName     = 'Nether.User.SessionName',
	ConfSessionExpire   = 'Nether.User.SessionExpire';

	static public function
	PrepareDefaultConfig(?Nether\Object\Datastore $Config=NULL):
	Nether\Object\Datastore {

		parent::PrepareDefaultConfig($Config);

		$Config->BlendRight([
			static::ConfUpdateSeenAfter => Values::SecPerMin,
			static::ConfSessionName     => 'NetherUserSession',
			static::ConfSessionExpire   => (Values::SecPerDay * 12)
		]);

		return $Config;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	Init(Nether\Object\Datastore $Config, ...$Argv):
	void {

		static::$Config = static::PrepareDefaultConfig($Config);

		// optional: register urls with atlantis.
		// this stack is making use of some oldschool php fuckery where it
		// won't cry about non-existing classes until that specific line of
		// code gets evaluated so this code can execute without crashing
		// if atlantis engine isn't installed. if you pass something that
		// smells like atlantis we'll check its not a duck first.

		if(isset($Argv['App']) && is_object($Argv['App']))
		if(method_exists($Argv['App'], 'GetProjectEnv'))
		if($Argv['App'] instanceof Nether\Atlantis\Engine)
		static::InitWithAtlantisEngine($Argv['App']);

		return;
	}

	static protected function
	InitWithAtlantisEngine(Nether\Atlantis\Engine $App):
	void {

		// register some data with the framework.

		$App->User = Nether\User\EntitySession::Get();

		// add some routes to the system.

		if($App->Router->GetSource() === 'dirscan') {
			$RouterPath = dirname(__FILE__);
			$Scanner = new Nether\Avenue\RouteScanner("{$RouterPath}/Routes");
			$Map = $Scanner->Generate();

			////////

			$Map['Verbs']->Each(
				fn(Nether\Object\Datastore $Handlers)
				=> $App->Router->AddHandlers($Handlers)
			);

			$Map['Errors']->Each(
				fn(Nether\Avenue\Meta\RouteHandler $Handler, int $Code)
				=> $App->Router->AddErrorHandler($Code, $Handler)
			);
		}

		return;
	}




}
