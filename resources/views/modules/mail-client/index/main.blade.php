@extends('uccello::modules.default.index.main')

@section('content')
    <div class="row">
        <div class="col s12">
            <h4>{{$user['displayName']}} - {{ $user['mail'] }}</h4>
            <div class="card">
                <div class="body">
                    <table class="striped responsive-table">
                        <thead>
                            <tr>
                                <th>Sujet</th>
                                <th>Aperçu</th>
                                <th><i class="material-icons">attach_file</i></th>
                                <th>Expediteur</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $message)
                            <tr class="email-row" data-mailid={{ $message->getId() }}>
                                <td @if(!$message->getIsRead()) style="font-weight: bold;" @endif>{{ $message->getSubject() }}</td>
                                <td @if(!$message->getIsRead()) style="font-weight: bold;" @endif>{{ $message->getBodyPreview() }}</td>
                                <td><i class="material-icons @if(!$message->getHasAttachments()) transparent-text @endif">attach_file</i></td>
                                <td class="tooltipped" data-position="bottom" data-tooltip="{{ $message->getFrom()->getEmailAddress()->getAddress() }}">
                                    {{ $message->getFrom()->getEmailAddress()->getName() }}
                                </td>
                                <td style="min-width: 13rem;">{{ $message->getReceivedDateTime()->format("d/m/Y - H:i") }}</td>
                                <td style="min-width: 6rem;">
                                    <a href="{{ $message->getWebLink() }}" target="_blank"><i class="material-icons">launch
                                        </i></a>
                                    <a href="mailto:{{ $message->getFrom()->getEmailAddress()->getAddress() }}?subject=TR:{{ $message->getSubject() }}">
                                        <i class="material-icons">edit</i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra-meta')
    <meta http-equiv="refresh" content="60"/>
@append


{{-- @section('script')
    {{ Html::script(mix('js/mail-client/app.js')) }}
@append --}}

@section('sidebar-main-menu-after')
    @include('uccello-email-client::modules.mail-client.index.accounts')
@append