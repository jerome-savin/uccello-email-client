<h4>{{ $folder->getDisplayName() }} - {{$user->getDisplayName()}} - {{ $user->getMail() }}</h4>
<div class="card">
    <div class="body">
        <table class="striped responsive-table">
            <thead>
                <tr>
                    <th>Sujet</th>
                    <th>Aperçu</th>
                    <th><i class="material-icons">attach_file</i></th>
                    <th>Interlocuteur</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($messages as $message)
                <tr class="email-row" data-mailid={{ $message->getId() }}>
                    <td @if(!$message->getIsRead()) style="font-weight: bold;" @endif>{{ $message->getSubject() }}</td>
                    <td class="toggle_preview preview" style="cursor: pointer; @if(!$message->getIsRead()) font-weight: bold; @endif">{{ $message->getBodyPreview() }}</td>
                    <td><i class="material-icons @if(!$message->getHasAttachments()) transparent-text @endif">attach_file</i></td>
                    @if($message->getFrom()->getEmailAddress()->getAddress()!=$user->getMail())
                    <td class="tooltipped" data-position="bottom" data-tooltip="{{ $message->getFrom()->getEmailAddress()->getAddress() }}">
                        {{ $message->getFrom()->getEmailAddress()->getName() }}
                    </td>
                    @else
                    <?php $to = $message->getToRecipients()[0];?>
                    <td class="tooltipped" data-position="bottom" data-tooltip="{{ $to['emailAddress']['address'] }}">
                        {{ $to['emailAddress']['name'] }}
                    </td>
                    @endif
                    <td style="min-width: 13rem;">{{ \Carbon\Carbon::parse($message->getReceivedDateTime())->timeZone(config('app.timezone', 'Europe/Paris'))->format("d/m/Y - H:i") }}</td>
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