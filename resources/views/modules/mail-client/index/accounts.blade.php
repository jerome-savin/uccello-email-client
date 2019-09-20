<li style="position: relative">
    <a class="subheader">
        {{ uctrans('accounts', $module) }}
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
        </li>
        @empty
        <li class="center-align white-text">{{ uctrans('empty.accounts', $module) }}</li>
        @endforelse
    </ul>
</li>