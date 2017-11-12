<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    /**
     * @var Client
     */
    private $httpClient;

    public function __construct(Client $httpClient) // todo: DI
    {
        $this->httpClient = $httpClient;
    }

    public function index()
    {
        $user = Auth::user();

        if (!$user->qb_refresh_token) {
            return redirect()->route('connect');
        }

        $res = $this->httpClient->get(
            "https://sandbox-quickbooks.api.intuit.com/v3/company/$user->qb_realm_id/companyinfo/$user->qb_realm_id",
            [
                'headers' => [
                    'Authorization' => "Bearer " . decrypt($user->qb_access_token),
                    'Accept' => 'application/json',
                    'Charset' => 'UTF-8'
                ],
                'http_errors' => false
            ]
        );

        $status = $res->getStatusCode();

        if ($status === 401) {
            // refresh token or return not connected error
            $authorizationHeader = "Basic " . base64_encode(
                    config('quickbooks.client') . ":" . config('quickbooks.secret')
                );

            $res = $this->httpClient->post(
                "https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer",
                [
                    'headers' => [
                        'Authorization' => $authorizationHeader,
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
                $user->qb_access_token = null;
                $user->qb_refresh_token = null;
                $user->qb_realm_id = null;
                $user->qb_refresh_token_updated_at = null;
                $user->save();

                return redirect()->route('connect')->with(['error' => 'Authorization error']);
            }

            // save new access token
            $responseData = json_decode($res->getBody());
            $user->qb_access_token = encrypt($responseData->access_token); // encrypt your tokens
            $user->qb_refresh_token = encrypt($responseData->refresh_token);
            $user->qb_refresh_token_updated_at = Carbon::now(); // will be used for refresh_token exchange

            return $this->index();
        }

        $data = json_decode($res->getBody());

        return view('company')->with([
            'company' => $data->CompanyInfo
        ]);
    }
}
