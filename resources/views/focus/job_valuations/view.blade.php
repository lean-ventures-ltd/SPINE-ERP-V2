@extends ('core.layouts.app')

@section('title', 'Job Valuation')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Job Valuation</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.job_valuations.partials.jobvaluation-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $quote = $job_valuation->quote;
                            $customer = '';
                            if ($job_valuation->customer) $customer = $job_valuation->customer->company ?: $job_valuation->customer->name;
                            if ($customer && $job_valuation->branch) $customer .= " - {$job_valuation->branch->name}";
                            $details = [
                                '#Serial' => gen4tid('JV-', $job_valuation->tid),
                                '#Quote/PI' => $quote? gen4tid($quote->bank_id? 'PI-' : 'QT-', $quote->tid) : '',
                                'Quote/PI Amount' => $quote? numberFormat($quote->subtotal) : '',
                                'Valuation Date' => dateFormat($job_valuation->date),
                                'Customer' => $customer,
                                'Valuated Amount' => numberFormat($job_valuation->subtotal),
                                'Balance' => numberFormat($job_valuation->balance),
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>{{ $val }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>

        <!-- jobcards / dnotes -->
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 80vh">
                        <table id="jobcardsTbl" class="table pb-2 tfr text-center">
                            <thead class="bg-gradient-directional-blue white pb-1">
                                <tr class="item_header bg-gradient-directional-blue white">
                                    <th>Item Type</th>
                                    <th>Ref No</th>                                                    
                                    <th>Date</th>
                                    <th>Technician</th>
                                    <th>Equipment</th>
                                    <th>Location</th>
                                    <th>Fault</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($job_valuation->job_cards as $item)
                                    <tr>
                                        <td>{{ $item->type == 1? 'JOBCARD' : 'DNOTE' }}</td>
                                        <td>{{ $item->reference }}</td>
                                        <td>{{ dateFormat($item->date) }}</td>
                                        <td>{{ $item->technician }}</td>
                                        <td>{{ @$item->equipment->capacity }} {{ @$item->equipment->make_type }}</textarea>
                                        <td>{{ @$item->location }}</td>
                                        <td>{{ $item->fault }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- products -->
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <div class="table-responsive mb-2 pb-2" style="max-height: 80vh">                            
                        <table id="productsTbl" class="table tfr my_stripe_single pb-2 text-center">
                            <thead>
                                <tr class="item_header bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th>Item Description</th>
                                    <th>UoM</th>
                                    <th>Qty</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                    <th width="15%">VAT</th>
                                    <th width="5%">% Valuated</th>
                                    <th width="5%">Amt Valuated</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($job_valuation->items as $item)
                                    @if ($item->row_type == 1)
                                        <tr>
                                            <td>{{ $item->numbering }}</td>
                                            <td class="text-left">{{ $item->product_name }}</td>
                                            <td>{{ $item->unit }}</td>
                                            <td>{{ +$item->product_qty }}</td>
                                            <td>{{ numberFormat($item->product_subtotal) }}</td>
                                            <td>{{ numberFormat($item->product_amount) }}</td>
                                            <td>{{ numberFormat($item->product_tax) }} ({{ +$item->tax_rate }}%)</td>
                                            <td>{{ +$item->perc_valuated }}</td>
                                            <td>{{ numberFormat($item->total_valuated) }}</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class="font-weight-bold">{{ $item->numbering }}</td>
                                            <td colspan="8" class="text-left font-weight-bold">{{ $item->product_name }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('after-scripts')
<script>
    $('table thead th').css({'paddingBottom': '3px', 'paddingTop': '3px'});
    $('table tbody td').css({paddingLeft: '2px', paddingRight: '2px'});
    $('table thead').css({'position': 'sticky', 'top': 0, 'zIndex': 100});
</script>
@endsection
