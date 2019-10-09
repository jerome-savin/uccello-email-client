<?php

namespace JeromeSavin\UccelloEmailClient\Http\Controllers;

use Illuminate\Contracts\Container\Container;
use \League\OAuth2\Client\Provider\GenericProvider as GenericProvider;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Illuminate\Routing\Controller;


class Office365Controller extends Controller
{
    private $client;

    private $graph;

    public function __construct()
    {
        $this->graph = new Graph();

        $this->client = new GenericProvider([
            'clientId'                => config('office365.appId'),
            'clientSecret'            => config('office365.secret'),
            'redirectUri'             => config('office365.redirect_url'),
            'urlAuthorize'            => config('office365.authority') . config('office365.authority_endpoint'),
            'urlAccessToken'          => config('office365.authority') . config('office365.authority_token'),
            'urlResourceOwnerDetails' => '',
            'scopes'                  => config('office365.scopes'),
        ]);


    }

    public function login($session)
    {
        $url = $this->client->getAuthorizationUrl();
        // Save client state so we can validate in response
        $session->put('oauth_state', $this->client->getState());

        return $url;
    }

    public function getAccessToken($code, $type = 'authorization_code')
    {
        if($type=='authorization_code')
            $accessToken = $this->client->getAccessToken($type, [
                'code' => $code,
            ]);
        else if($type=='refresh_token')
            $accessToken = $this->client->getAccessToken('refresh_token', [
                'refresh_token' => $code
            ]);

        return (object)[
            'token'        => $accessToken->getToken(),
            'refreshToken' => $accessToken->getRefreshToken(),
            'expires'      => $accessToken->getExpires(),
        ];

    }

    public function getUser($user_access_token)
    {
        $this->graph->setAccessToken($user_access_token);

        return $this->graph->createRequest('GET', '/me')
                    ->setReturnType(Model\User::class)
                    ->execute();
    }

    public function getEmails($user_access_token, $limit = 10, $folder = 'inbox')
    {

        $this->graph->setAccessToken($user_access_token);

        $messageQueryParams = [
            "\$orderby" => "receivedDateTime DESC",
            "\$top"     => $limit,
        ];

        return $this->graph->createRequest('GET', '/me/mailfolders/'.$folder.'/messages?' . http_build_query($messageQueryParams))
            ->setReturnType(Model\Message::class)
            ->execute();
    }

    public function mailsFromTo($user_access_token, $address)
    {
        $this->graph->setAccessToken($user_access_token); 
        
        $filter = '$search="participants:'.$address.'"';

        return $this->graph->createRequest('GET', '/me/messages?' .$filter)
            ->setReturnType(Model\Message::class)
            ->execute();
    }

    public function getFolders($user_access_token, $folder = null)
    {
        $this->graph->setAccessToken($user_access_token);

        $url = '/me/mailFolders';
        if($folder)
            $url.='/'.$folder;

        return $this->graph->createRequest('GET', $url)
                    ->setReturnType(Model\MailFolder::class)
                    ->execute();
    }

    public function getSubFolders($id, $user_access_token)
    {
        $this->graph->setAccessToken($user_access_token);

        return $this->graph->createRequest('GET', '/me/mailFolders/'.$id.'/childFolders')
                    ->setReturnType(Model\MailFolder::class)
                    ->execute();
    }

}