@extends('admin.index')

@section('title', trans('admin/bookings.page_title') .' / '. $booking->getRefNumber() .' / '. trans('admin/bookings.subtitle.transactions'))
@section('subtitle', /*'<i class="fa fa-credit-card"></i> '*/ '<a href="'. route('admin.bookings.index') .'">'. trans('admin/bookings.page_title') .'</a> / <a href="'. route('admin.bookings.show', $booking->id) .'">'. $booking->getRefNumber() .'</a> / '. trans('admin/bookings.subtitle.transactions') )


@section('subheader')
  <link rel="stylesheet" href="{{ asset_url('plugins','x-editable/css/bootstrap-editable.css') }}">
@endsection


@section('subcontent')
    <div id="transactions">
        @include('partials.alerts.success')
        @include('partials.alerts.errors')

        <table class="table table-hover">
            <thead>
                <tr>
                    <th style="width:50px;"></th>
                    <th>{{ trans('admin/bookings.transaction.name') }}</th>
                    {{--
                    <th>{{ trans('admin/bookings.transaction.description') }}</th>
                    --}}
                    <th>{{ trans('admin/bookings.transaction.amount') }}</th>
                    <th>{{ trans('admin/bookings.transaction.payment_charge') }}</th>
                    <th>{{ trans('admin/bookings.transaction.payment_name') }}</th>
                    <th>{{ trans('admin/bookings.transaction.status') }}</th>
                    <th>{{ trans('admin/bookings.transaction.updated_at') }}</th>

                    @if (request('advance'))
                        <th>{{ trans('admin/bookings.transaction.id') }}</td>
                        <th>{{ trans('admin/bookings.transaction.ip') }}</th>
                        <th>{{ trans('admin/bookings.transaction.response') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
            @permission('admin.transactions.index')
            @forelse ($transactions as $transaction)

                @php
                $urlMap = config('site.site_urls');
                $siteId = 0;
                $siteKey = '';

                if(!empty($urlMap) && !empty($urlMap[$booking->booking->site_id])) {
                    $payUrl = $urlMap[$booking->booking->site_id] .'booking';

                    $site = \App\Models\Site::select('id', 'key')->where('published', '1')->find($booking->booking->site_id);
                    if( !empty($site->id) ) {
                        $siteId = $site->id;
                        $siteKey = $site->key;
                    }
                }
                else {
                    $payUrl = url('/booking');
                }

                $payUrl .= '?finishType=payment&bID='. $booking->booking->unique_key .'&tID='. $transaction->unique_key .'&pMethod='. $transaction->payment_method .'&locale='. app()->getLocale();

                if(!empty($siteKey)) {
                    $payUrl .= '&site_key='. $siteKey;
                }
                @endphp

                <tr>
                    <td>
                        @if (auth()->user()->hasPermission(['admin.transactions.destroy', 'admin.transactions.create']) || !in_array($transaction->payment_method, ['cash', 'account', 'bacs', 'none']) )
                            <span class="row-label">{{ trans('admin/bookings.transaction.actions') }}:</span>

                            <div class="btn-group" role="group" aria-label="...">
                                <div class="btn-group pull-left" role="group">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                        <span class="fa fa-angle-down"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        @permission('admin.transactions.destroy')
                                        <li>
                                            <a href="{{ route('admin.bookings.transactions', $booking->id) }}?action=destroy&tID={{ $transaction->unique_key }}" onclick="return confirm('Are you sure you want to delete this transaction?');" style="padding:3px 8px;">
                                                <span style="display:inline-block; width:20px; text-align:center;">
                                                    <i class="fa fa-trash"></i>
                                                </span>
                                                <span>{{ trans('admin/bookings.transaction.button.delete') }}</span>
                                            </a>
                                        </li>
                                        @endpermission

                                        @permission('admin.transactions.create')
                                        <li>
                                            <a href="{{ route('admin.transactions.copy', $transaction->id) }}" onclick="return confirm('Are you sure you want to copy this transaction?');" style="padding:3px 8px;">
                                                <span style="display:inline-block; width:20px; text-align:center;">
                                                    <i class="fa fa-files-o"></i>
                                                </span>
                                                <span>{{ trans('admin/bookings.transaction.button.copy') }}</span>
                                            </a>
                                        </li>
                                        @endpermission

                                        @if( !in_array($transaction->payment_method, ['cash', 'account', 'bacs', 'none']) )
                                        <li>
                                            <a href="{{ route('admin.bookings.transactions', $booking->id) }}?action=send&tID={{ $transaction->unique_key }}" style="padding:3px 8px;">
                                                <span style="display:inline-block; width:20px; text-align:center;">
                                                    <i class="fa fa-envelope-o"></i>
                                                </span>
                                                <span>{{ trans('admin/bookings.transaction.button.send') }}</span>
                                            </a>
                                        </li>
                                        <li>
                                            {{-- <a href="{{ url('/booking') }}?finishType=payment&bID={{ $booking->booking->unique_key }}&tID={{ $transaction->unique_key }}" target="_blank" style="padding:3px 8px;"> --}}
                                            <a href="{{ $payUrl }}" target="_blank" style="padding:3px 8px;">
                                                <span style="display:inline-block; width:20px; text-align:center;">
                                                    <i class="fa fa-shopping-cart"></i>
                                                </span>
                                                <span>{{ trans('admin/bookings.transaction.button.pay_now') }}</span>
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </td>
                    <td>
                        <span class="row-label">{{ trans('admin/bookings.transaction.name') }}:</span>
                        @permission('admin.transactions.edit')
                        <a href="#" class="inline-editing inline-editing-name"
                            data-type="text"
                            data-name="name"
                            data-pk="{{ $booking->id }}"
                            data-value="{{ $transaction->name }}"
                            data-url="{{ route('admin.bookings.transactions', $booking->id) }}?action=inline&tID={{ $transaction->unique_key or '' }}"
                            title="{{ trans('admin/bookings.transaction.button.edit') }}">
                            {{ $transaction->getName() }}
                        </a>
                        @else
                            {{ $transaction->getName() }}
                        @endpermission
                    </td>
                    {{-- <td>{{ $transaction->description }}</td> --}}
                    <td>
                        <span class="row-label">{{ trans('admin/bookings.transaction.amount') }}:</span>
                        @permission('admin.transactions.edit')
                            <a href="#" class="inline-editing inline-editing-amount"
                                data-type="text"
                                data-name="amount"
                                data-pk="{{ $booking->id }}"
                                data-value="{{ $transaction->amount }}"
                                data-url="{{ route('admin.bookings.transactions', $booking->id) }}?action=inline&tID={{ $transaction->unique_key or '' }}"
                                title="{{ trans('admin/bookings.transaction.button.edit') }}">
                                {{ $transaction->getAmount() }}
                            </a>
                        @else
                            {{ $transaction->getAmount() }}
                        @endpermission
                    </td>
                    <td>
                        <span class="row-label">{{ trans('admin/bookings.transaction.payment_charge') }}:</span>
                        @permission('admin.transactions.edit')
                            <a href="#" class="inline-editing inline-editing-payment-charge"
                                data-type="text"
                                data-name="payment_charge"
                                data-pk="{{ $booking->id }}"
                                data-value="{{ $transaction->payment_charge }}"
                                data-url="{{ route('admin.bookings.transactions', $booking->id) }}?action=inline&tID={{ $transaction->unique_key or '' }}"
                                title="{{ trans('admin/bookings.transaction.button.edit') }}">
                                {{ $transaction->getPaymentCharge() }}
                            </a>
                        @else
                            {{ $transaction->getPaymentCharge() }}
                        @endpermission
                    </td>
                    <td>
                        <span class="row-label">{{ trans('admin/bookings.transaction.payment_name') }}:</span>
                        @permission('admin.transactions.edit')
                            <a href="#" class="inline-editing inline-editing-payment-method"
                                data-type="select"
                                data-name="payment_method"
                                data-pk="{{ $booking->id }}"
                                data-value="{{ $transaction->payment_method }}"
                                data-source="{{ json_encode($payments) }}"
                                data-url="{{ route('admin.bookings.transactions', $booking->id) }}?action=inline&tID={{ $transaction->unique_key or '' }}"
                                title="{{ trans('admin/bookings.transaction.button.edit') }}">
                                {{ $transaction->payment_name }}
                            </a>
                        @else
                            {{ $transaction->payment_name }}
                        @endpermission
                    </td>
                    <td>
                        <span class="row-label">{{ trans('admin/bookings.transaction.status') }}:</span>
                        @permission('admin.transactions.edit')
                            <a href="#" class="inline-editing inline-editing-status"
                                data-type="select"
                                data-name="status"
                                data-pk="{{ $booking->id }}"
                                data-value="{{ $transaction->status }}"
                                data-source="{{ $transaction->getStatusList('json') }}"
                                data-url="{{ route('admin.bookings.transactions', $booking->id) }}?action=inline&tID={{ $transaction->unique_key or '' }}"
                                title="{{ trans('admin/bookings.transaction.button.edit') }}">
                                {!! $transaction->getStatus('color') !!}
                            </a>
                        @else
                            {!! $transaction->getStatus('color') !!}
                        @endpermission
                    </td>
                    <td>
                        <span class="row-label">{{ trans('admin/bookings.transaction.updated_at') }}:</span>
                        {!! App\Helpers\SiteHelper::formatDateTime($transaction->updated_at) !!}
                    </td>

                    @if (request('advance'))
                        <td>{{ $transaction->id }}</td>
                        <td>
                            <span class="row-label">{{ trans('admin/bookings.transaction.ip') }}:</span>
                            @if( $transaction->ip )
                                <a href="http://ipaddress.is/{{ $transaction->ip }}" target="_blank">{{ $transaction->ip }}</a>
                            @endif
                        </td>
                        <td>{{ $transaction->response }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <p class="text-center" style="margin:10px 0;">{{ trans('admin/bookings.transaction.message.no_transactions') }}</p>
                    </td>
                </tr>
            @endforelse
            @endpermission
            </tbody>
        </table>

        @permission('admin.transactions.create')
        <a href="{{ route('admin.transactions.add', $booking->booking->id) }}" class="btn btn-sm btn-default">
            <i class="fa fa-plus"></i>
            <span>{{ trans('admin/bookings.transaction.button.create') }}</span>
        </a>
        @endpermission

        <div style="color:gray; margin-top:10px;">{{ trans('admin/bookings.transaction.bottom_message') }}</div>

    </div>
@stop


@section('subfooter')
    <script src="{{ asset_url('plugins','x-editable/js/bootstrap-editable.min.js') }}"></script>
    <script>
    $(document).ready(function(){
        // Inline editing
        $('#transactions .inline-editing').editable({
            mode: 'inline',
            placement: 'right',
            ajaxOptions: {
                headers: {
                  'X-CSRF-TOKEN': EasyTaxiOffice.csrfToken
                },
                type: 'GET',
                dataType: 'json'
            },
            display: function(value, sourceData, response) {
                var new_value = $(this).html();
                if( response && response.new_value ) {
                    new_value = response.new_value;
                }
                else if( sourceData && sourceData.new_value ) {
                    new_value = sourceData.new_value;
                }
                $(this).html(new_value +'<i class="fa fa-edit"></i>');
            },
            success: function(response, newValue) {
                if(response.status) {
                    window.location.reload();
                }
            }
        });

        // Validate fields
        $('#transactions .inline-editing-name, #transactions .inline-editing-amount, #transactions .inline-editing-payment_charge').editable('option', 'validate', function(v) {
            if(!v) return '{{ trans('admin/bookings.transaction.message.required') }}';
        });
    });
    </script>
@endsection
