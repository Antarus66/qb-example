<?php

namespace App\Http\Controllers;

use App\Services\Quickbooks\TokenRefresher;
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
    /**
     * @var TokenRefresher
     */
    private $tokenRefresher;

    public function __construct(Client $httpClient, TokenRefresher $tokenRefresher) // todo: DI
    {
        $this->httpClient = $httpClient;
        $this->tokenRefresher = $tokenRefresher;
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

        if ($res->getStatusCode() === 401) {
            try {
                $this->tokenRefresher->refreshAccessToken();
                return $this->index();
            } catch (\Exception $e) {
                return redirect()->route('connect')->with(['error' => 'Authorization error']);
            }
        }

        $data = json_decode($res->getBody());

        return view('company')->with([
            'company' => $data->CompanyInfo
        ]);
    }
}
