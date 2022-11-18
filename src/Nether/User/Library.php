<?php

namespace Nether\User;
use Nether;

use Nether\Common\Values;
use Nether\Object\Datastore;

class Library
extends Nether\Common\Library {

	const
	ConfEnable             = 'Nether.User.Enable',
	ConfUpdateSeenAfter    = 'Nether.User.SeenUpdateAfter',
	ConfConfirmEmailChange = 'Nether.User.ConfirmEmailChange',
	ConfSessionName        = 'Nether.User.SessionName',
	ConfSessionExpire      = 'Nether.User.SessionExpire',
	ConfGitHubEnabled      = 'Nether.User.GitHub.Enabled',
	ConfGitHubNewUsers     = 'Nether.User.GitHub.NewUsers',
	ConfGitHubID           = 'Nether.User.GitHub.ClientID',
	ConfGitHubSecret       = 'Nether.User.GitHub.ClientSecret',
	ConfTwitterEnabled     = 'Nether.User.Twitter.Enabled',
	ConfTwitterNewUsers    = 'Nether.User.Twitter.NewUsers',
	ConfTwitterID          = 'Nether.User.Twitter.ClientID',
	ConfTwitterSecret      = 'Nether.User.Twitter.ClientSecret',
	ConfTwitterToken       = 'Nether.User.Twitter.ClientToken';

	static public function
	Init(...$Argv):
	void {

		static::OnInit(...$Argv);
		return;
	}

	static public function
	InitDefaultConfig(?Nether\Object\Datastore $Config=NULL):
	Nether\Object\Datastore {

		parent::InitDefaultConfig($Config);

		$Config->BlendRight([
			static::ConfEnable             => TRUE,
			static::ConfUpdateSeenAfter    => Values::SecPerMin,
			static::ConfConfirmEmailChange => TRUE,
			static::ConfSessionName        => 'NetherUserSession',
			static::ConfSessionExpire      => (Values::SecPerDay * 12),

			static::ConfGitHubEnabled   => TRUE,
			static::ConfGitHubNewUsers  => TRUE,
			static::ConfTwitterEnabled  => TRUE,
			static::ConfTwitterNewUsers => TRUE
		]);

		return $Config;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static protected function
	OnInit(Datastore $Config, ...$Argv):
	void {

		static::InitDefaultConfig($Config);

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	static public function
	IsAppleEnabled():
	bool {

		return FALSE;
	}

	static public function
	IsGitHubEnabled():
	bool {

		return (
			TRUE
			&& static::$Config[static::ConfGitHubEnabled]
			&& static::$Config[static::ConfGitHubID]
			&& static::$Config[static::ConfGitHubSecret]
		);
	}

	static public function
	IsTwitterEnabled():
	bool {

		return (
			TRUE
			&& static::$Config[static::ConfTwitterEnabled]
			&& static::$Config[static::ConfTwitterID]
			&& static::$Config[static::ConfTwitterSecret]
		);
	}

}
