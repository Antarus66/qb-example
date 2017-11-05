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
                    {{-- todo: add image--}}
                    <a href="{{ route('qb-authorization-request') }}">Connect to QB</a>

                    @isset($error)
                        <div class="alert alert-danger">{{ $error }}</div>
                    @endisset
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
