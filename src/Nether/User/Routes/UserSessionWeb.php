<?php

namespace Nether\User\Routes;
use Nether;
use League;

use Throwable;
use Nether\User\Library;
use Nether\Atlantis\Routes\Web;
use Nether\Avenue\Meta\RouteHandler;
use Nether\Object\Datastore;

use League\OAuth2\Client\Provider\Github as GitHubProvider;
use League\OAuth2\Client\Token\AccessToken;

class UserSessionWeb
extends Web {

	#[RouteHandler('/login')]
	public function
	PageLogin():
	void {

		($this->App->Surface)
		->Wrap('user/login');

		return;
	}

	#[RouteHandler('/logout')]
	public function
	PageLogout():
	void {

		($this->App->Surface)
		->Wrap('user/logout');

		return;
	}

	////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////

	#[RouteHandler('/auth/github')]
	public function
	HandleGitHub():
	void {

		$AllowNewUsers = Library::Get(Library::ConfGitHubNewUsers);
		$ClientID = Library::Get(Library::ConfGitHubID);
		$ClientSecret = Library::Get(Library::ConfGitHubSecret);

		$AuthCode = $this->Request->Query->Code;
		$Token = NULL;
		$User = NULL;

		////////

		if(!Library::IsGitHubEnabled())
		$this->Quit(1, 'GitHub Auth has not been enabled.');

		////////

		// kick off an auth flow with redirect to the remote app. this will
		// end the current request, sending them elsewhere. when the
		// remote is done they will be sent back with an auth code in tow.

		$Client = new GitHubProvider([
			'clientId'     => $ClientID,
			'clientSecret' => $ClientSecret
		]);

		if(!$AuthCode)
		$this->Goto($Client->GetAuthorizationUrl([
			'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
			'scope' => [ 'user:email' ]
		]));

		////////

		// finish an in-process auth flow by using the auth code returned
		// to get a full auth token.

		$Token = $this->HandleGitHub_FetchAuthToken($Client, $AuthCode);

		if(!$Token)
		$this->Quit(2, 'Unable to process GitHub Auth Code.');

		// at this point we should have basic access to the 	user info
		// on the remote host so find what we want to fill in an account
		// here locally.

		$Info = $this->HandleGitHub_FetchRemoteUserInfo($Client, $Token);

		if(!$Info->Alias || !$Info->Email || !$Info->AuthID)
		$this->Quit(3, 'Github did not give us an Alias, Email, or AuthID.');

		////////

		// check our local database to see if we have matching local user
		// already in the database using the github info.

		try {
			$User = Nether\User\EntitySession::GetByGitHubID($Info->AuthID);

			// if we have not found a user yet check for an account with the
			// same email address.

			if(!$User)
			$User = Nether\User\EntitySession::GetByGitHubEmail($Info->Email, $Info->AuthID);

			// if we have not found a user yet and we allow new users to be
			// created on the fly then go ahead and insert them now.

			if(!$User && $AllowNewUsers)
			$User = Nether\User\EntitySession::Insert([
				'Alias'        => $Info->Alias,
				'Email'        => $Info->Email,
				'AuthGitHubID' => $Info->AuthID
			]);
		}

		catch(Nether\User\Error\GitHubAuthMismatch $Error) {
			$this->Quit(4, 'This account is already bound to a different GitHub identity.');
		}

		catch(Throwable $Error) {
			$this->Quit(5, "Unexpected error occured ({$Error->GetMessage()}).");
		}

		////////

		if(!$User)
		$this->Quit(6, 'There are no accounts linked with this GitHub identity.');

		////////

		$User->Update([ 'AuthGitHubID'=> $Info->AuthID ]);
		$User->TransmitSession();
		$this->Goto('/');

		return;
	}

	protected function
	HandleGitHub_FetchAuthToken(GitHubProvider $Client, string $AuthCode):
	?AccessToken {

		$Token = NULL;

		try {
			$Token = $Client->GetAccessToken(
				'authorization_code',
				[ 'code' => $AuthCode ]
			);
		}

		catch(Throwable $Error) { }

		return $Token;
	}

	protected function
	HandleGitHub_FetchRemoteUserInfo(GitHubProvider $Client, AccessToken $Token):
	object {

		$Alias = NULL;
		$Email = NULL;
		$AuthID = NULL;
		$Error = NULL;

		////////

		try {
			/** @var League\OAuth2\Client\Provider\GithubResourceOwner $Account */

			$Account = $Client->GetResourceOwner($Token);
			$AuthID = $Account->GetID();
			$Alias = Nether\Avenue\Util::MakePathableKey($Account->GetNickname());
			$Email = Nether\Common\Datafilters::Email($Account->GetEmail());

			// github tends to not return an email address even if
			// you have one set as public, so, here we go.

			if(!$Email) {
				$Request = $Client->GetAuthenticatedRequest(
					'GET', 'https://api.github.com/user/emails',
					$Token
				);

				$Emails = (
					(new Datastore((array)$Client->GetParsedResponse($Request)))
					->Filter(function($Val){ return $Val['primary'] === TRUE; })
					->Remap(function($Val){ return $Val['email']; })
				);

				if($Emails->Count() >= 1)
				$Email = $Emails->Shift();
			}
		}

		catch(Throwable $Error) { }

		return (object)[
			'Alias'  => $Alias,
			'Email'  => $Email,
			'AuthID' => $AuthID
		];
	}

}
