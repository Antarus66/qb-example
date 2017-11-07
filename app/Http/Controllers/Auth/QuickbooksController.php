<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;


class QuickbooksController extends Controller
{
    /**
     * @var Client
     */
    private $httpClient;
    private $clientId;
    private $secret;

    public function __construct(Client $httpClient) // todo: DI
    {
        $this->httpClient = $httpClient;
        $this->clientId = config('quickbooks.client');
        $this->secret = config('quickbooks.secret');

    }

    public function makeAuthorizationRequest()
    {
        $queryData = [
            'client_id' => $this->clientId,
            'scope' => 'com.intuit.quickbooks.accounting',
            'redirect_uri' => route('qb-handle-authorization-code'),
            'response_type' => 'code',
            'state' => csrf_token()
        ];

        $url = 'https://appcenter.intuit.com/connect/oauth2?'
            . http_build_query($queryData, null, '&', PHP_QUERY_RFC1738);

        $redirectResponse = Redirect::to($url);

        return $redirectResponse;
    }

    public function handleAuthorizationCode(Request $request)
    {
        $code = $request->get('code');

        // check state as csrf token
        // realmId represents the company a user is connecting to

        if ($request->has('error')) {
            return view('home')->with(['error' => $request->get('error')]);
        }

        $authorizationHeader = "Basic " . base64_encode($this->clientId . ":" . $this->secret);

        $res = $this->httpClient->request(
            'POST',
            'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
            [
                'form_params' => [
                    'code' => $code,
                    'redirect_uri' => route('qb-handle-authorization-code'),
                    'grant_type' => 'authorization_code' // OAuth 2.0 specification
                ],
                'headers' => [
                    'Authorization' => $authorizationHeader
                ],
                'http_errors' => false
            ]
        );

        if ($res->getStatusCode() !== 200) {
            return view('home')->with(['error' => 'Authorization error']);
        }

        $responseData = json_decode($res->getBody());

        if (!isset($responseData->access_token) || !isset($responseData->refresh_token)) {
            return view('home')->with(['error' => 'Authorization error']);
        }

        $user = Auth::user();
        $user->qb_access_token = encrypt($responseData->access_token); // encrypt your tokens
        $user->qb_refresh_token = encrypt($responseData->refresh_token);
        $user->qb_refresh_token_updated_at = Carbon::now();
        $user->save();

        return redirect()->route('home');
    }

    public function revokeAccess()
    {
        $user = Auth::user();
        $authorizationHeader = "Basic " . base64_encode($this->clientId . ":" . $this->secret);

        $res = $this->httpClient->request(
            'POST',
            'https://developer.api.intuit.com/v2/oauth2/tokens/revoke', // be careful here
            [
                'json' => [
                    'token' => decrypt($user->qb_access_token)
                ],
                'headers' => [
                    'Authorization' => $authorizationHeader
                ],
                'http_errors' => false
            ]
        );

        if ($res->getStatusCode() === 200) {
            $user->qb_access_token = null;
            $user->qb_refresh_token = null;
            $user->qb_refresh_token_updated_at = null;
            $user->save();
        }

        return redirect()->route('home');
    }
}
