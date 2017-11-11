@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">Company info</div>

                    <div class="panel-body">
                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($company)
                            {{ $company->CompanyName }}
                        @else
                            No company data loaded
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
