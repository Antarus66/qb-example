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

    public function __construct(Client $httpClient) // todo: DI
    {
        $this->httpClient = $httpClient;
    }

    public function makeAuthorizationRequest()
    {
        $client = config('quickbooks.client');

        $queryData = [
            'client_id' => $client,
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
            return $request->get('error');
        }

        $client = config('quickbooks.client');
        $secret = config('quickbooks.secret');
        $authorizationHeader = "Basic " . base64_encode($client . ":" . $secret);

        $url = 'https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer'; // todo: get from config

        try {
            $jsonResponse = $this->httpClient->request(
                'POST',
                $url,
                [
                    'form_params' => [
                        'code' => $code,
                        'redirect_uri' => route('qb-handle-authorization-code'),
                        'grant_type' => 'authorization_code' // OAuth 2.0 specification
                    ],
                    'headers' => [
                        'Authorization' => $authorizationHeader
                    ]
                ]
            );
        } catch (\Exception $e) {
            return view('home')->with(['error' => 'Authorization error']);
        }

        $responseData = json_decode($jsonResponse->getBody());

        if (!isset($responseData->access_token) || !isset($responseData->refresh_token)) {
            return view('home')->with(['error' => 'Authorization error']);
        }

        $user = Auth::user();
        $user->qb_access_token = encrypt($responseData->access_token);
        $user->qb_refresh_token = encrypt($responseData->refresh_token);
        $user->qb_refresh_token_updated_at = Carbon::now();
        $user->save();

        return redirect()->route('home');
    }
}
