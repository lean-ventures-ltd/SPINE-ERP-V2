@extends ('focus.report.pdf.statement')
@section('statement_body')
    <small style="font-size:.6em">Movement From <i>{{ $lang['from_date'] }}</i> To <i>{{ $lang['to_date'] }}</i></small>
    @php
        $product = $account_details->last();
    @endphp
    <div style="font-size:.7em; text-align:left">
        <b>{{$product->name}}</b> <br> 
        {{$product->location}}
    </div>
    
    <table class="plist" cellpadding="0" cellspacing="0">
        <tr class="heading">
            <td>Date</td>
            <td>Type</td>
            <td>Qty</td>
            <td>On Hand</td>
            <td>Avg Cost</td>
            <td>Asset Value</td>
        </tr>
        @foreach ($account_details as $item)
            <tr class="item">
                <td width="15%">{{ dateFormat($item->date) }}</td>
                <td width="10%">{{ $item->type }}</td>
                <td width="8%">{{ round($item->qty) }}</td>
                <td width="8%">{{ round($item->qty_onhand) }}</td>
                <td width="12%">{{ numberFormat($item->avg_cost) }}</td>
                <td width="15%">{{ numberFormat($item->amount) }}</td>
            </tr>
        @endforeach
        <!-- 20 dynamic empty rows -->
        @for ($i = count($account_details); $i < 10; $i++)
            <tr class="item">
                @for($j = 0; $j < 5; $j++)
                    <td></td>
                @endfor
            </tr>
        @endfor
        <!--  -->
    </table>
    <br>
    <div class="subtotal-container">
        <table class="subtotal">
            <thead></thead>
            <tbody>
                <tr>
                    <td colspan="2" class="summary"><strong>{{trans('general.summary')}}</strong></td>
                </tr>
                <tr>
                    <td>{{trans('general.total')}}:</td>
                    <td style="text-align:right;">{{ numberFormat($product->amount) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection