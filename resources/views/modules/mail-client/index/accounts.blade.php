<li style="position: relative">
    <a class="subheader">
        {{ uctrans('emails', $module) }}
    </a>
    <a href="{{ ucroute('uccello.mail.manage', $domain, $module) }}" style="position: absolute; right: 0; top: 0;" data-tooltip="{{ uctrans('manage_accounts', $module) }}" data-position="right">
        <i class="material-icons">settings</i>
    </a>
</li>

<li id="calendars-menu">
    <ul class="collapsible collapsible-accordion">
        @forelse ($accounts as $i => $account)
        <li class="submenu">
            <a href="javascript:void(0)" class="collapsible-header truncate" tabindex="0">
                <span>{{ $account->username }}</span>
            </a>
            <div class="collapsible-body">
                <ul class="collapsible collapsible-accordion">
                    @foreach ($account->folders as $folder)
                        <li>
                            <a href="javascript:void(0)" class="truncate folder" data-folder-id="{{ $folder->getId() }}" data-account-id="{{ $account->id }}" style="margin-left: 0" title="{{ uctrans('mails_unread', $module, ['count' => $folder->getUnreadItemCount()]) }}">
                            <div class="collapsible-header">
                                    @if($folder->getUnreadItemCount()>0)                                                    
                                    <span style="font-weight: bold;">{{ $folder->getDisplayName() }} ({{ $folder->getUnreadItemCount() }})</span>
                                @else
                                    <span>{{ $folder->getDisplayName() }}</span>
                                @endif
                                @if($folder->getChildFolderCount()>0)<i class="material-icons right">list</i>@endif
                            </div>
                            </a>
                            @if($folder->getChildFolderCount()>0)
                            <div class="collapsible-body">
                                <ul>
                                    @foreach ($folder->getChildFolders() as $childFolder)
                                        <li>
                                            <a href="javascript:void(0)" class="truncate folder" data-folder-id="{{ $childFolder->getId() }}" data-account-id="{{ $account->id }}" style="margin-left: 0" title="{{ uctrans('mails_unread', $module, ['count' => $childFolder->getUnreadItemCount()]) }}">

                                                <i class="material-icons">subdirectory_arrow_right</i>
                                                @if($childFolder->getUnreadItemCount()>0)                                                    
                                                    <span style="font-weight: bold;">{{ $childFolder->getDisplayName() }} ({{ $childFolder->getUnreadItemCount() }})</span>
                                                @else
                                                    <span>{{ $childFolder->getDisplayName() }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </li>
        @empty
        <li class="center-align white-text">{{ uctrans('empty.accounts', $module) }}</li>
        @endforelse
    </ul>
</li>

<li style="position: relative">
    <a class="subheader">
        {{ uctrans('menu', $module) }}
    </a>
</li>