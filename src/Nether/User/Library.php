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
	ConfSessionExpire      = 'Nether.User.SessionExpire';

	////////////////////////////////////////////////////////////////
	// configuration keys for apple signin /////////////////////////

	const
	ConfAppleEnabled       = 'Nether.User.Apple.Enabled',
	ConfAppleNewUsers      = 'Nether.User.Apple.NewUsers',
	ConfAppleID            = 'Nether.User.Apple.ClientID',
	ConfAppleTeamID        = 'Nether.User.Apple.TeamID',
	ConfAppleKeyFileID     = 'Nether.User.Apple.KeyFileID',
	ConfAppleKeyFilePath   = 'Nether.User.Apple.KeyFilePath';

	////////////////////////////////////////////////////////////////
	// configuration keys for discord signin ///////////////////////

	const
	ConfDiscordEnabled      = 'Nether.User.Discord.Enabled',
	ConfDiscordNewUsers     = 'Nether.User.Discord.NewUsers',
	ConfDiscordID           = 'Nether.User.Discord.ClientID',
	ConfDiscordSecret       = 'Nether.User.Discord.ClientSecret';

	////////////////////////////////////////////////////////////////
	// configuration keys for github signin ////////////////////////

	const
	ConfGitHubEnabled      = 'Nether.User.GitHub.Enabled',
	ConfGitHubNewUsers     = 'Nether.User.GitHub.NewUsers',
	ConfGitHubID           = 'Nether.User.GitHub.ClientID',
	ConfGitHubSecret       = 'Nether.User.GitHub.ClientSecret';

	////////////////////////////////////////////////////////////////
	// configuration keys for google signin ////////////////////////

	const
	ConfGoogleEnabled      = 'Nether.User.Google.Enabled',
	ConfGoogleNewUsers     = 'Nether.User.Google.NewUsers',
	ConfGoogleID           = 'Nether.User.Google.ClientID',
	ConfGoogleSecret       = 'Nether.User.Google.ClientSecret';

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

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

			static::ConfAppleEnabled    => TRUE,
			static::ConfAppleNewUsers   => TRUE,

			static::ConfGitHubEnabled   => TRUE,
			static::ConfGitHubNewUsers  => TRUE,

			static::ConfGoogleEnabled   => TRUE,
			static::ConfGoogleNewUsers  => TRUE,

			static::ConfDiscordEnabled  => TRUE,
			static::ConfDiscordNewUsers => TRUE
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

		return (
			TRUE
			&& static::$Config[static::ConfAppleEnabled]
			&& static::$Config[static::ConfAppleID]
			&& static::$Config[static::ConfAppleTeamID]
			&& static::$Config[static::ConfAppleKeyFileID]
			&& static::$Config[static::ConfAppleKeyFilePath]
		);
	}

	static public function
	IsDiscordEnabled():
	bool {

		return (
			TRUE
			&& static::$Config[static::ConfDiscordEnabled]
			&& static::$Config[static::ConfDiscordID]
			&& static::$Config[static::ConfDiscordSecret]
		);
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
	IsGoogleEnabled():
	bool {

		return (
			TRUE
			&& static::$Config[static::ConfGoogleEnabled]
			&& static::$Config[static::ConfGoogleID]
			&& static::$Config[static::ConfGoogleSecret]
		);
	}

	static public function
	HasAnyThirdPartyAuth():
	bool {

		return (
			FALSE
			|| static::IsAppleEnabled()
			|| static::IsDiscordEnabled()
			|| static::IsGitHubEnabled()
			|| static::IsGoogleEnabled()
		);
	}

}
