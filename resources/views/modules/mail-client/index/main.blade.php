@extends('uccello::modules.default.index.main')

@section('content')
    <div id="calendar-loader" class="row" style="margin-bottom: 0">
        <div class="col s12">
            <div class="progress transparent" style="margin: 0">
                <div class="indeterminate green"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col s12" id="mail-container">
            
        </div>
    </div>
@endsection

@section('extra-meta')
    {{-- <meta http-equiv="refresh" content="60"/> --}}
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="first_account_id" content="{{$first_account_id}}">
@append


@section('script')
    {{ Html::script(mix('js/app.js', 'vendor/jerome-savin/uccello-email-client')) }}
@append

@section('sidebar-main-menu-before')
    @include('uccello-email-client::modules.mail-client.index.accounts')
@append