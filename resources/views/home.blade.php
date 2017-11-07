@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($hasAccessToken)
                        QuickBooks is connected
                        <div>
                            <a href="{{ route('qb-revoke-access') }}">Disconnect</a>
                        </div>
                    @else
                        <a href="{{ route('qb-authorization-request') }}">
                            <img src="img/C2QB_green_btn_lg_default.png" alt="Connect to QB" style="width:292px;height:51px;border:0;">
                        </a>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
