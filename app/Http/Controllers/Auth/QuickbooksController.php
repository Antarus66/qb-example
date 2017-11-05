<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class QuickbooksController extends Controller
{
    /**
     * @var Client
     */
    private $httpClient;

    public function __construct(Client $httpClient) // todo: DI
    {
        $this->httpClient = $httpClient;
    }

    public function makeAuthorizationRequest()
    {
        $appId = config('quickbooks.app_id');
        $secret = config('quickbooks.secret');

        $client = config('quickbooks.client');
        $httpsUrl = config('app.https_url');

        $this->httpClient->get(
            'https://appcenter.intuit.com/connect/oauth2', // todo: all the urls from config
            [
                'query' => [
                    'client_id' => $client,
                    'scope' => 'com.intuit.quickbooks.accounting',
                    'redirect_uri' => $httpsUrl . '/handle-authorization-code', // todo: from helper
                    'response_type' => 'code',
                    'state' => csrf_token()
                ]
            ]
        );
    }

    public function handleAuthorizationCode(Request $request)
    {
        // todo: add loggers

        $code = $request->get('code');
        $realmId = $request->get('realmId');
        $state = $request->get('state'); // todo: check the csrf token
        $error = $request->get('error'); // todo: check error

        $client = config('quickbooks.client');
        $secret = config('quickbooks.secret');
        $httpsUrl = config('app.https_url');
        $authorizationHeader = "Basic " . base64_encode($client . ":" . $secret);

        $this->httpClient->post(
            'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
            [
                'query' => [
                    'code' => $code,
                    'redirect_uri' => $httpsUrl . '/handle-access-token',
                    'grant_type' => 'authorization_code' // OAuth 2.0 specification
                ],
                'headers' => [
                    'Authorization' => $authorizationHeader
                ]
            ]
            );
    }

    public function handleAccessToken(Request $request)
    {
        $data = $request->toArray();

        // todo: add loggers

        // todo: save keys
    }
}
