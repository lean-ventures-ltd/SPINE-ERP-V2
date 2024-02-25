@extends ('focus.report.pdf.statement')
@section('statement_body')
    <table class="plist" cellpadding="0" cellspacing="0">
        <tr class="heading">
            <td>{{trans('general.date')}}</td>
            <td>{{trans('products.product')}}</td>
            <td>{{ trans('products.stock_transfer_from')}}</td>
            <td>{{trans('products.stock_transfer_to')}}</td>
            <td>{{trans('products.qty')}}</td>
            <td>{{trans('general.total')}} Value</td>
        </tr>
        @php
            $total = 0
        @endphp
        @foreach ($stock_transf_items as $item)
            <tr class="item">
                <td>{{ dateFormat(@$item->stock_transfer->date, 'd/m/Y') }}</td>
                <td>{{ @$item->productvar->name }}</td>
                <td>{{ @$item->stock_transfer->source->title }}</td>
                <td>{{ @$item->stock_transfer->destination->title }}</td>
                <td>{{ +$item->qty_transf }}</td>
                <td>{{ numberFormat($item->amount) }}</td>
            </tr>
            @php
                $total += $item->amount;
            @endphp
        @endforeach
        <!-- 20 dynamic empty rows -->
        @for ($i = count($stock_transf_items); $i < 10; $i++)
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
                    <td style="text-align: right;">{{numberFormat($total)}}</td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection