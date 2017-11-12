<?php

namespace App\Services\Quickbooks;

use App\Services\Quickbooks\Exception\InvalidRefreshToken;
use App\Services\Quickbooks\Exception\NotConnectedToACompany;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class TokenRefresher
{
    /**
     * @var Client
     */
    private $httpClient;

    public function __construct(Client $httpClient) // todo: DI
    {
        $this->httpClient = $httpClient;
    }

    public function refreshAccessToken() : string
    {
        $user = Auth::user();

        if (!isset($user->qb_refresh_token)) {
            throw new NotConnectedToACompany();
        }

        $res = $this->httpClient->post(
            "https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer",
            [
                'headers' => [
                    'Authorization' => $this->makeAuthorizationHeader(),
                    'Cache-Control' => 'no-cache'
                ],
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => decrypt($user->qb_refresh_token)
                ],
                'http_errors' => false
            ]
        );

        if ($res->getStatusCode() !== 200) {
            // refresh token is invalid, need to reconnect to the company
            $this->resetTokens();
            throw new InvalidRefreshToken('Cannot refresh the token');
        }

        // save new access token
        $responseData = json_decode($res->getBody());
        $this->saveTokens($responseData->access_token, $responseData->refresh_token);

        return $responseData->access_token;
    }

    protected function makeAuthorizationHeader() : string
    {
        return "Basic " . base64_encode(config('quickbooks.client') . ":" . config('quickbooks.secret'));
    }

    protected function saveTokens($accessToken, $refreshToken) : TokenRefresher
    {
        $user = Auth::user();

        $user->qb_access_token = encrypt($accessToken); // encrypt your tokens
        $user->qb_refresh_token = encrypt($refreshToken);
        $user->qb_refresh_token_updated_at = Carbon::now(); // will be used for refresh_token exchange

        return $this;
    }

    protected function resetTokens() : TokenRefresher
    {
        $user = Auth::user();

        $user->qb_access_token = null;
        $user->qb_refresh_token = null;
        $user->qb_realm_id = null;
        $user->qb_refresh_token_updated_at = null;
        $user->save();

        return $this;
    }
}