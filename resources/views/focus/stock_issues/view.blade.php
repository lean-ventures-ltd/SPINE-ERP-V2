@extends ('core.layouts.app')

@section('title', 'Stock Issuing')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Stock Issuing</h4>
        </div>
        <div class="col-6">
            <div class="btn-group float-right">
                @include('focus.stock_issues.partials.stockissue-header-buttons')
            </div>
        </div>
    </div>

    <div class="content-body">
        <div class="card">
            <div class="card-content">
                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        @php
                            $details = [
                                'Date' => dateFormat($stock_issue->date, 'd-M-Y'),
                                'Issue To' => @$stock_issue->employee->full_name,
                                'Reference No' => $stock_issue->ref_no,
                                'Note' => $stock_issue->note,
                            ];
                        @endphp
                        @foreach ($details as $key => $val)
                            <tr>
                                <th width="30%">{{ $key }}</th>
                                <td>{{ $val }}</td>
                            </tr>
                        @endforeach
                    </table>

                    <div class="table-responsive">
                        <table class="table table-sm tfr my_stripe_single" id="invoiceTbl">
                            <thead>
                                <tr class="bg-gradient-directional-blue white">
                                    <th>#</th>
                                    <th width="25%">Stock Item</th>
                                    <th>Unit</th>
                                    <th>Qty On-Hand</th>
                                    <th>Qty Rem</th>
                                    <th>Issue Qty</th>
                                    <th>Location</th>
                                    <th>Assigned To</th>
                                </tr>
                            </thead>
                            <tbody>   
                                @foreach ($stock_issue->items as $i => $item)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $item->productvar->name }}</td>
                                        <td>{{ @$item->productvar->product->unit->code }}</td>
                                        <td>{{ +$item->qty_onhand }}</td>
                                        <td>{{ +$item->qty_rem }}</td>
                                        <td>{{ +$item->issue_qty }}</td>
                                        <td>{{ @$item->warehouse->title }}</td>
                                        <td>{{ @$item->assignee->full_name }}</td>
                                    </tr>
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
