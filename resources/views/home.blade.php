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
                    @else
                        <a href="{{ route('qb-authorization-request') }}">
                            <img src="img/C2QB_green_btn_lg_default.png" alt="Connect to QB" style="width:292px;height:51px;border:0;">
                        </a>
                    @endif

                    @isset($error)
                        <div class="alert alert-danger">{{ $error }}</div>
                    @endisset
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
