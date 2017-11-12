<?php

namespace App\Http\Controllers;

use App\Services\Quickbooks\Contract\QBTokenRefresherInterface;
use GuzzleHttp\ClientInterface;
use Illuminate\Support\Facades\Auth;

class CustomersController extends Controller
{
    /**
     * @var ClientInterface
     */
    private $httpClient;
    /**
     * @var QBTokenRefresherInterface
     */
    private $tokenRefresher;

    public function __construct(ClientInterface $httpClient, QBTokenRefresherInterface $tokenRefresher)
    {
        $this->httpClient = $httpClient;
        $this->tokenRefresher = $tokenRefresher;
    }

    public function index()
    {
        $user = Auth::user();

        //Multi-object read
        $queryString = http_build_query(['query' => 'SELECT * FROM Customer']);

        $res = $this->httpClient->get(
            "https://sandbox-quickbooks.api.intuit.com/v3/company/$user->qb_realm_id/query?$queryString",
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
        $customers = $data->QueryResponse->Customer;

        return view('customers')->with(['customers' => $customers]);
    }
}
