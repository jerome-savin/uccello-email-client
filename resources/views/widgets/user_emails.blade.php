<div class="card small">
    <div class="card-content" style="overflow-y: auto;">
        <span class="card-title">
            {{-- Icon --}}
            <i class="material-icons left primary-text">forum</i>

            {{-- Label --}}
            {{ trans('Derniers emails reçus') }}

            <div class="right-align right">
                {{-- @include('uccello::modules.default.detail.relatedlists.buttons') --}}
            </div>
        </span>
        <div class="row display-flex">
            {{-- Table --}}
            <div class="">
                <table
                    id="widget_emails"
                    class="striped highlight">
                    @if(count($emails)>0)
                    <thead>
                        <tr>
                            <th class="select-column">&nbsp;</th>
            
                            @foreach ($columns as $column)
                            <th class="sortable">
                                <a href="javascript:void(0)" class="column-label">
                                    
                                    {{-- Label --}}
                                    {{ $column->label }}
            
                                    {{-- Sort icon --}}
                                    <i class="fa fa-sort-amount-up" style="display: none"></i>
                                </a>
                            </th>
                            @endforeach
            
                            <th class="actions-column hide-on-small-only hide-on-med-only right-align">
                                <br>
                                <a href="javascript:void(0)" class="btn-floating btn-small waves-effect red clear-search" data-tooltip="{{ uctrans('button.clear_search', $module) }}" data-position="top" style="display: none">
                                    <i class="material-icons">close</i>
                                </a>
                            </th>
                        </tr>
                    </thead>
                    @endif
            
                    <tbody>
                        {{-- No result --}}
                        <tr class="no-results" @if(count($emails)>0)style="display: none"@endif>
                            <td colspan="100%" class="center-align">{{ uctrans('datatable.no_results', $module) }}</td>
                        </tr>
            
                        {{-- Row template used by the query --}}
                        @foreach($emails as $email)
                        <tr class="record" data-row-url="{{ $email->webLink }}">
                            <td class="select-column">&nbsp;</td>
            
                            @foreach ($columns as $column)
                            <?php $columnName = $column->name ?>
                            @if($columnName=='type')
                                <td data-field="{{ $column->name }}">
                                    <i class="material-icons"
                                        data-tooltip="{{ trans('uccello-email-client::widgets.'.$email->$columnName) }}"
                                        data-position="top"
                                    >
                                        @if($email->$columnName=='sent')arrow_forward
                                        @else arrow_back
                                        @endif
                                    </i>
                                </td>
                            @else
                                <td data-field="{{ $column->name }}">{{ $email->$columnName }}</td>
                            @endif
                            @endforeach
            
                            <td class="actions-column hide-on-small-only hide-on-med-only right-align">            
                                <a href="{{ $email->webLink }}"
                                    data-tooltip="{{ trans('uccello-email-client::widgets.button.view_web') }}"
                                    data-position="left"
                                    target="_blank"
                                    class="delete-btn primary-text">
                                    <i class="material-icons">launch</i>
                                </a>

                            </td>
                        </tr>
                        @endforeach
            
                    </tbody>
                </table>
            
                {{-- Loader --}}
                <div class="loader center-align hide">
                    <div class="preloader-wrapper big active">
                        <div class="spinner-layer spinner-primary-only">
                            <div class="circle-clipper left">
                                <div class="circle"></div>
                            </div>
                            <div class="gap-patch">
                                <div class="circle"></div>
                            </div>
                            <div class="circle-clipper right">
                                <div class="circle"></div>
                            </div>
                        </div>
                    </div>
            
                    <div>
                        {{ uctrans('datatable.loading', $module) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>