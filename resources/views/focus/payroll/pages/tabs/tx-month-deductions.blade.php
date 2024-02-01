<form action="{{ route('biller.payroll.store_deduction') }}" method="post">
    @csrf
    <div class="card-content">
        <div class="card-body">
            <table id="deductionTbl" class="table table-striped table-responsive table-bordered zero-configuration" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>Employee Id</th>
                        <th>Employee Name</th>
                        <th>Gross Pay</th>
                        <th>NSSF</th>
                        <th>Taxable Deductions</th>
                        <th>Narration</th>
                        <th>Pay</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                    @endphp
                    @foreach ($payrollItems as $item)
                        <tr>
                            <td>{{ gen4tid('EMP-', $item->employee_id) }}</td>
                            <td>{{ $item->name }}</td>
                            <td class="editable-cell">{{ amountFormat($item->basic_plus_allowance) }}</td>
                            <td>{{ amountFormat($item->nssf) }}</td>
{{--                            <td>{{ amountFormat($item->taxable_gross) }}</td>--}}
                            <td>
                                <input type="number" step="0.01" class="form-control deduction" value="{{ $item->taxable_deductions }}" name="taxable_deductions[]"
                                       @if($item->netpay != 0.00) readonly @endif
                                >
                            </td>
                            <td>
                                <textarea class="form-control deduction" name="deduction_narration[]" @if($item->netpay != 0.00) readonly @endif style="min-width: 400px">{{ $item->deduction_narration }}</textarea>
                            </td>
                            <td>
                                <input type="text"  id="net_post_tx_deduction" class="form-control" value="{{ $item->taxable_gross == 0.00 ? $item->basic_plus_allowance : $item->taxable_gross }}" readonly>
                            </td>

                            @if ($total_tx_deduction > 0)
{{--                                <td>--}}
{{--                                    <a href="#" class="btn btn-danger btn-sm my-1 edit-deduction" data-toggle="modal" data-target="#deductionModal">--}}
{{--                                        <i class="fa fa-pencil" aria-hidden="true"></i> Edit--}}
{{--                                    </a>--}}
{{--                                </td>--}}
                            @endif


                            <input type="hidden" name="id[]" class="id" value="{{ $item->id }}">
                            <input type="hidden" name="payroll_id" value="{{ $item->payroll_id }}">
                            <input type="hidden" id="basic_plus_allowance" value="{{ $item->basic_plus_allowance }}">
                            <input type="hidden" name="nssf[]" value="{{ $item->nssf }}" id="">
                            <input type="hidden" name="nhif[]" value="{{ $item->nhif }}" id="">
                            <input type="hidden" name="housing_levy[]" value="{{ $item->housing_levy }}" id="">
{{--                            name="total_sat_deduction[]"--}}
                            <input type="hidden" value="{{ $item->nhif + $item->nhif }}" id="">
{{--                            name="gross_pay[]"--}}
                            <input type="hidden" value="{{ $item->gross_pay }}" id="">


                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        <div class="form-group row">
            <div class="col-3">
                <label for="total">Total NSSF</label>
                <input type="text" value="{{ amountFormat(bcmul($total_nssf, 2, 2)) }}" class="form-control" readonly>
                <input type="hidden" name="total_nssf" value="{{ bcmul($total_nssf, 2, 2) }}" class="form-control" id="total_nssf" readonly>
            </div>
            <div class="col-3">
                <label for="total">Total Deductions</label>
                <input type="text" value="{{ amountFormat($total_tx_deduction) }}"
                    class="form-control" id="deduct_total" readonly>
                <input type="hidden" name="deduction_total" value="{{ $total_tx_deduction }}"
                    class="form-control" id="deduction_total" readonly>
            </div>
        </div>
        <div class="float-right">
            <button type="submit" class="btn btn-primary submit-deduction">Save Deductions</button>
        </div>
    </div>
</form>