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

                        @foreach($customers as $customer)
                            <div>
                                {{ $customer->GivenName }}

                                @isset ($customer->FamilyName)
                                    {{ $customer->FamilyName }}
                                @endisset

                                @isset ($customer->PrimaryEmailAddr)
                                    - {{ $customer->PrimaryEmailAddr->Address }}
                                @endisset
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
