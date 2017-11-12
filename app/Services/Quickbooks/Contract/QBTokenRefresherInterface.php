<?php

namespace App\Services\Quickbooks\Contract;

interface QBTokenRefresherInterface
{
    public function refreshAccessToken() : string;
}