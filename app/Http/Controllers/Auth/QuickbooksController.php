<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


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
        $queryData = [
            'client_id' => config('quickbooks.client'),
            'scope' => 'com.intuit.quickbooks.accounting',
            'redirect_uri' => route('qb-handle-authorization-code'), // have to be in the app settings
            'response_type' => 'code',
            'state' => csrf_token()
        ];

        return redirect()->to('https://appcenter.intuit.com/connect/oauth2?' . http_build_query($queryData));
    }

    public function handleAuthorizationCode(Request $request)
    {
        // check 'state' as csrf token

        if ($request->has('error')) {
            return redirect()->route('connect')->with(['error' => $request->get('error')]);
        }

        $authorizationHeader = "Basic " . base64_encode(
            config('quickbooks.client') . ":" . config('quickbooks.secret')
            );

        $res = $this->httpClient->request(
            'POST',
            'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer',
            [
                'headers' => [
                    'Authorization' => $authorizationHeader
                ],
                'form_params' => [
                    'code' => $request->get('code'),
                    'redirect_uri' => route('qb-handle-authorization-code'), // the same
                    'grant_type' => 'authorization_code' // OAuth 2.0 specification
                ],
                'http_errors' => false
            ]
        );

        if ($res->getStatusCode() !== 200) {
            return redirect()->route('connect')->with(['error' => 'Authorization error']);
        }

        $responseData = json_decode($res->getBody());

        $user = Auth::user();
        $user->qb_access_token = encrypt($responseData->access_token); // encrypt your tokens
        $user->qb_refresh_token = encrypt($responseData->refresh_token);
        $user->qb_realm_id = $request->get('realmId'); // represents the company a user is connecting to
        $user->qb_refresh_token_updated_at = Carbon::now(); // will be used for refresh_token exchange
        $user->save();

        return redirect()->route('connect');
    }

    public function revokeAccess()
    {
        $user = Auth::user();
        $authorizationHeader = "Basic " . base64_encode(
                config('quickbooks.client') . ":" . config('quickbooks.secret')
            );

        $res = $this->httpClient->request(
            'POST',
            'https://developer.api.intuit.com/v2/oauth2/tokens/revoke', // be careful here with the documentation
            [
                'headers' => [
                    'Authorization' => $authorizationHeader
                ],
                'json' => [ // form_params also works
                    'token' => decrypt($user->qb_access_token)
                ],
                'http_errors' => false
            ]
        );

        if ($res->getStatusCode() !== 200) {
            return redirect()->route('connect')->with(['error' => 'Authorization error']);
        }

        $user->qb_access_token = null;
        $user->qb_refresh_token = null;
        $user->qb_refresh_token_updated_at = null;
        $user->save();

        return redirect()->route('connect');
    }
}
