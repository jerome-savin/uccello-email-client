<?php

namespace JeromeSavin\UccelloEmailClient\Http\Controllers;
use Moathdev\Office365\Facade\Office365;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Carbon\Carbon;
use Uccello\Core\Http\Controllers\Core\Controller;
use Illuminate\Http\Request;
use JeromeSavin\UccelloEmailClient\EmailAccount;
use Uccello\Core\Models\Domain;
use Uccello\Core\Models\Module;

class MailClientController extends Controller
{

    public function mails(Domain $domain, Module $module, Request $request)
    {
        $this->preProcess($domain, $module, $request);


        $accounts = EmailAccount::where('user_id', auth()->id())->get();
        if ($accounts->count() === 0) {
            return redirect(ucroute('uccello.mail.manage', $domain));
        }

        $messages = Office365::getEmails($this->getAccessToken($accounts->first()), 50);
        $user = Office365::getUserInfo($this->getAccessToken($accounts->first()));

        $this->viewName = 'index.main';
        return $this->autoView([
            'accounts'  => $accounts,
            'user'      => $user,
            'messages'  => $messages
        ]);
    }

    public function mailsFromTo($address)
    {
        $mails = null;
        $accounts = EmailAccount::where('user_id', auth()->id())->get();
        if($accounts->count()>0)
        {
            $graph = new Graph();
            foreach($accounts as $account)
            {
                $mails = [];
                $graph->setAccessToken($this->getAccessToken($account));

                $filter = '$search="participants:'.$address.'"';

                $acc_mails = $graph->createRequest('GET', '/me/messages?' .$filter)
                    ->setReturnType(Model\Message::class)
                    ->execute();
                if(is_array($acc_mails))
                    $mails = array_merge($mails, $acc_mails);
            }
        }   
        return $mails;
    }

    public function manage(?Domain $domain, Module $module, Request $request)
    {
        // Pre-process
        $this->preProcess($domain, $module, $request);

        $accounts = \JeromeSavin\UccelloEmailClient\EmailAccount::where('user_id', auth()->id())->get();

        $this->viewName = 'manage.main';

        return $this->autoView([
            'accounts' => $accounts,
        ]);
    }

    public function signin()
    {
        // Initialize the OAuth client
        $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId'                => config('Office365.appId'),
            'clientSecret'            => config('Office365.secret'),
            'redirectUri'             => config('Office365.redirect_url'),
            'urlAuthorize'            => config('Office365.authority') . config('Office365.authority_endpoint'),
            'urlAccessToken'          => config('Office365.authority') . config('Office365.authority_token'),
            'urlResourceOwnerDetails' => '',
            'scopes'                  => config('Office365.scopes'),
        ]);

        // Generate the auth URL
        $authorizationUrl = $oauthClient->getAuthorizationUrl();

        // Save client state so we can validate in response
        session()->put('oauth_state', $oauthClient->getState());

        // Redirect to authorization endpoint
        return redirect($authorizationUrl);
    }

    public function gettoken(?Domain $domain, Module $module)
    {
        $this->preProcess($domain, $module, request());

        // Authorization code should be in the "code" query param
        if (isset($_GET['code'])) {
            // Check that state matches
            if (empty($_GET['state']) || ($_GET['state'] !== session('oauth_state'))) {
                exit('State provided in redirect does not match expected value.');
            }

            // Clear saved state
            session()->forget('oauth_state');

            // Initialize the OAuth client
            $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId'                => config('Office365.appId'),
                'clientSecret'            => config('Office365.secret'),
                'redirectUri'             => config('Office365.redirect_url'),
                'urlAuthorize'            => config('Office365.authority') . config('Office365.authority_endpoint'),
                'urlAccessToken'          => config('Office365.authority') . config('Office365.authority_token'),
                'urlResourceOwnerDetails' => '',
                'scopes'                  => config('Office365.scopes'),
            ]);

            try {
                // Make the token request
                $accessToken = $oauthClient->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);


                //Graph instanciation to retrieve user email
                $graph = new Graph();
                $graph->setAccessToken($accessToken->getToken());
                $user = $graph->createRequest('GET', '/me')
                                ->setReturnType(Model\User::class)
                                ->execute();

                // Create or retrieve token from database
                $tokenDb = EmailAccount::firstOrNew([
                    'service_name'  => 'o365',
                    'user_id'       => \Auth::id(),
                    'username'      => $user->getUserPrincipalName(),
                ]);


                $tokenDb->token = $accessToken->getToken();
                $tokenDb->refresh_token = $accessToken->getRefreshToken();
                $tokenDb->expiration = $accessToken->getExpires();

                $tokenDb->save();

                // Redirect back to home page
                return redirect(ucroute('uccello.mail.manage', $domain, $module));
            }
            catch (League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                exit('ERROR getting tokens: '.$e->getMessage());
            }
            exit();
        }
        elseif (isset($_GET['error'])) {
            exit('ERROR: '.$_GET['error'].' - '.$_GET['error_description']);
        }
    }

    public static function getAccessToken(EmailAccount $emailAccount){

        $now = Carbon::now()->timestamp + 300; // Add 5 minutes

        if($emailAccount->expiration <= $now)
        // Token is expired (or very close to it) so let's refresh
        {
            // Initialize the OAuth client
            $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId'                => env('OAUTH_APP_ID'),
                'clientSecret'            => env('OAUTH_APP_PASSWORD'),
                'redirectUri'             => env('OAUTH_REDIRECT_URI'),
                'urlAuthorize'            => env('OAUTH_AUTHORITY').env('OAUTH_AUTHORIZE_ENDPOINT'),
                'urlAccessToken'          => env('OAUTH_AUTHORITY').env('OAUTH_TOKEN_ENDPOINT'),
                'urlResourceOwnerDetails' => '',
                'scopes'                  => env('OAUTH_SCOPES')
            ]);

            try {
                $newToken = $oauthClient->getAccessToken('refresh_token', [
                'refresh_token' => $emailAccount->refresh_token
                ]);

                // Store the new values

                $emailAccount->token = $newToken->getToken();
                $emailAccount->refresh_token = $newToken->getRefreshToken();
                $emailAccount->expiration = $newToken->getExpires();

                $emailAccount->save();

                return $emailAccount->token;
            }
            catch (League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                return '';
            }
        }

        return $emailAccount->token;
    }

    public function initClient($accountId)
    {
        $tokenDb = EmailAccount::where([
            'service_name'  => 'o365',
            'user_id'       => auth()->id(),
            'id'            => $accountId,
        ])->first();

        $graph = new Graph();
        $graph->setAccessToken(
            $this->getAccessToken($tokenDb)
        );

        return $graph;
    }
}
