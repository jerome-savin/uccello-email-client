<?php

namespace JeromeSavin\UccelloEmailClient\Http\Controllers;
// use Moathdev\Office365\Facade\Office365;
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

        $o365 = new Office365Controller();

        $accounts = EmailAccount::where('user_id', auth()->id())->get();
        if ($accounts->count() === 0) {
            return redirect(ucroute('uccello.mail.manage', $domain));
        }

        foreach($accounts as $account)
        {
            $folders = $o365->getFolders($this->getAccessToken($account));
            foreach($folders as $folder)
            {
                if($folder->getChildFolderCount()>0)
                {
                    $folder->setChildFolders($o365->getSubFolders($folder->getId(), $this->getAccessToken($account)));
                }
            }
            $account->folders = $folders;
        }


        $this->viewName = 'index.main';
        return $this->autoView([
            'accounts'  => $accounts,
            'first_account_id' => $accounts->first()->id,
        ]);
    }

    public function folderMails(Domain $domain, $accountId, $folder, Request $request)
    {
        $o365 = new Office365Controller();
        $accounts = EmailAccount::where('user_id', auth()->id())->get();
        $account = EmailAccount::find($accountId);
        $messages = $o365->getEmails($this->getAccessToken($account), 50, $folder);
        $user = $o365->getUser($this->getAccessToken($account));
        $m_folder = $o365->getFolders($this->getAccessToken($account), $folder);

        return view('uccello-email-client::modules.mail-client.index.mails',[
            'accounts'  => $accounts,
            'user'      => $user,
            'messages'  => $messages,
            'folder'    => $m_folder,
        ]);
    }

    public function mailsFromTo($address)
    {
        $mails = null;
        $accounts = EmailAccount::where('user_id', auth()->id())->get();
        if ($accounts->count()>0) {
            $o365 = new Office365Controller();
            $mails = [];
            foreach ($accounts as $account) {
                $acc_mails = $o365->mailsFromTo($this->getAccessToken($account), $address);

                if (is_array($acc_mails)) {
                    $mails = array_merge($mails, $acc_mails);
                }
            }
        }
        return $mails;
    }

    public function mailsKeyword($keyword)
    {
        $mails = null;
        $accounts = EmailAccount::where('user_id', auth()->id())->get();
        if ($accounts->count()>0) {
            $o365 = new Office365Controller();
            $mails = [];
            foreach ($accounts as $account) {
                $acc_mails = $o365->mailsKeyword($this->getAccessToken($account), $keyword);

                if (is_array($acc_mails)) {
                    $mails = array_merge($mails, $acc_mails);
                }
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
        $o365 = new Office365Controller();
        $authorizationUrl = $o365->login(session());
        // dd(session('oauth_state'));

        return redirect($authorizationUrl);
    }

    public function remove(?Domain $domain, Request $request)
    {

        $module = ucmodule('mail-client');

        $this->preProcess($domain, $module, $request);

        $account = EmailAccount::find($request->input('id'));
        $account->delete();

        return redirect(ucroute('uccello.mail.manage', $domain, $module));
    }

    public function gettoken(?Domain $domain, Module $module)
    {
        $this->preProcess($domain, $module, request());

        // Authorization code should be in the "code" query param
        if (isset($_GET['code'])) {
            // Check that state matches
            if (empty($_GET['state']) || ($_GET['state'] !== session('oauth_state'))) {
                // var_dump(session('oauth_state'));
                // dd(session('oauth_state'));
                exit('State provided in redirect does not match expected value.');
            }

            // Clear saved state
            session()->forget('oauth_state');

            $o365 = new Office365Controller;

            try {

                $accessToken = $o365->getAccessToken($_GET['code']);

                $user = $o365->getUser($accessToken->token);

                // Create or retrieve token from database
                $tokenDb = EmailAccount::firstOrNew([
                    'service_name'  => 'o365',
                    'user_id'       => \Auth::id(),
                    'username'      => $user->getUserPrincipalName(),
                ]);


                $tokenDb->token = $accessToken->token;
                $tokenDb->refresh_token = $accessToken->refreshToken;
                $tokenDb->expiration = $accessToken->expires;

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
            $o365 = new Office365Controller;

            try {
                $newToken = $o365->getAccessToken($emailAccount->refresh_token, 'refresh_token');


                $emailAccount->token = $newToken->token;
                $emailAccount->refresh_token = $newToken->refreshToken;
                $emailAccount->expiration = $newToken->expires;

                $emailAccount->save();

                return $emailAccount->token;
            }
            catch (League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
                return '';
            }
        }

        return $emailAccount->token;
    }

    // public function initClient($accountId)
    // {
    //     $tokenDb = EmailAccount::where([
    //         'service_name'  => 'o365',
    //         'user_id'       => auth()->id(),
    //         'id'            => $accountId,
    //     ])->first();

    //     $graph = new Graph();
    //     $graph->setAccessToken(
    //         $this->getAccessToken($tokenDb)
    //     );

    //     return $graph;
    // }
}
