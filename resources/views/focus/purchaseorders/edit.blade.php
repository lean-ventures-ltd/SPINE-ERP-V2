@extends('core.layouts.app')

@section('title', 'Purchase Order | Edit')

@section('content')
<div class="content-wrapper">
    <div class="content-header row mb-1">
        <div class="content-header-left col-6">
            <h4 class="content-header-title">Purchase Orders Management</h4>
        </div>
        <div class="content-header-right col-6">
            <div class="media width-250 float-right">
                <div class="media-body media-right text-right">
                    @include('focus.purchaseorders.partials.purchaseorders-header-buttons')
                </div>
            </div>
        </div>
    </div>    

    <div class="content-body"> 
        <div class="card">
            <div class="card-body">
                {{ Form::model($po, ['route' => ['biller.purchaseorders.update', $po], 'method' => 'PATCH']) }}
                    @include('focus.purchaseorders.form')
                {{ Form::close() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('extra-scripts')
{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});
    function select2Config(url, callback) {
        return {
            ajax: {
                url,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({q: term, keyword: term}),
                processResults: callback
            }
        }
    }

    // defaults
    $('#ref_type').val("{{ $po->doc_ref_type }}");
    $('#tax').val("{{ $po->tax }}")

    // default datepicker values
    $('.datepicker')
    .datepicker({format: "{{ config('core.user_date_format')}}", autoHide: true})
    $('#date').datepicker('setDate', new Date("{{ $po->date }}"));
    $('#due_date').datepicker('setDate', new Date("{{ $po->due_date }}"));

    // On searching supplier
    $('#supplierbox').change(function() {
        const name = $('#supplierbox option:selected').text().split(' : ')[0];
        const [id, taxId] = $(this).val().split('-');
        $('#taxid').val(taxId);
        $('#supplierid').val(id);
        $('#supplier').val(name);

        let priceCustomer = '';
            $('#pricegroup_id option').each(function () {
                if (id == $(this).val())
                priceCustomer = $(this).val();
                // console.log($(this).val());
            });
            
            $('#pricegroup_id').val(priceCustomer);
    });
    const supplierText = "{{ $po->supplier? $po->supplier->name : '' }} : ";
    const supplierVal = "{{ $po->supplier_id }}-{{ $po->supplier? $po->supplier->taxid : '' }}";
    $('#supplierbox').append(new Option(supplierText, supplierVal, true, true)).change();


    // load suppliers
    const supplierUrl = "{{ route('biller.suppliers.select') }}";
    function supplierData(data) {
        return {results: data.map(v => ({id: v.id+'-'+v.taxid, text: v.name+' : '+v.email}))};
    }
    $('#supplierbox').select2(select2Config(supplierUrl, supplierData));
    // load projects dropdown
    const projectUrl = "{{ route('biller.projects.project_search') }}";
    function projectData(data) {
        return {results: data.map(v => ({id: v.id, text: v.name}))};
    }
    $("#project").select2(select2Config(projectUrl, projectData));
    
    // On Tax change
    let taxIndx = 0;
    $('#tax').change(function() {
        if (taxIndx > 0) return;
        const tax = $(this).val();
        $('#rowtax-0').val(tax).change();
        $('#expvat-0').val(tax).change();
        $('#assetvat-0').val(tax).change();
        taxIndx++;
    });

    function getProjectMilestones(projectId){

        let select = $('#project_milestone');

        $.ajax({
            url: "{{ route('biller.getProjectMileStones') }}",
            method: 'GET',
            data: { projectId: projectId },
            dataType: 'json', // Adjust the data type accordingly
            success: function(data) {
                // This function will be called when the AJAX request is successful

                // Clear any existing options
                select.empty();

                if(data.length === 0){

                    select.append($('<option>', {
                        value: null,
                        text: 'No Milestones Created For This Project'
                    }));

                } else {

                    select.append($('<option>', {
                        value: null,
                        text: 'Select a Budget Line'
                    }));

                    // Add new options based on the received data
                    for (var i = 0; i < data.length; i++) {

                        const options = { year: 'numeric', month: 'short', day: 'numeric' };
                        const date = new Date(data[i].due_date);

                        select.append($('<option>', {
                            value: data[i].id,
                            text: data[i].name + ' | Balance: ' +  parseFloat(data[i].balance).toFixed(2) + ' | Due on ' + date.toLocaleDateString('en-US', options)
                        }));
                    }

                    let selectedOptionValue = "{{ @$po->project_milestone }}";
                    if (selectedOptionValue) {
                        select.val(selectedOptionValue);
                    }

                    checkMilestoneBudget(select.find('option:selected').text());

                }

            },
            error: function() {
                // Handle errors here
                console.log('Error loading data');
            }
        });


    }

    function checkMilestoneBudget(milestoneString){

        // Get the value of the input field
        let selectedMilestone = milestoneString;

        // Specify the start and end strings
        let startString = 'Balance: ';
        let endString = ' | Due on';

        // Find the index of the start and end strings
        let startIndex = selectedMilestone.indexOf(startString);
        let endIndex = selectedMilestone.indexOf(endString, startIndex + startString.length);

        // Extract the string between start and end
        let milestoneBudget = parseFloat(selectedMilestone.substring(startIndex + startString.length, endIndex)).toFixed(2);

        // console.log("Milestone Budget is " + milestoneBudget + " and purchase total is " + purchaseGrandTotal);

        if(purchaseGrandTotal > milestoneBudget){

            // console.log( "Milestone Budget is " + milestoneBudget );
            // console.log( "Milestone Budget Exceeded" );
            $("#milestone_warning").text("Milestone Budget of " + milestoneBudget + " Exceeded!");
        }
        else {

            $("#milestone_warning").text("");
        }

    }

    $('#project').change(function() {

        getProjectMilestones($(this).val())
    });

    $('#project_milestone').change(function() {

        checkMilestoneBudget($(this).find('option:selected').text());
    });



    // On project change
    $("#project").change(function() {
        const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
        $('#projectexptext-0').val(projectText);
        $('#projectexpval-0').val($(this).val());
    });
    const projectName = "{{ $po->project? $po->project->name : '' }}";
    const projectId = "{{ $po->project_id }}";
    if (projectId) $('#project').append(new Option(projectName, projectId, true, true)).change();


    let purchaseGrandTotal = 0;
    // Update transaction table
    const sumLine = (...values) => values.reduce((prev, curr) => prev + curr.replace(/,/g, '')*1, 0);
    function transxnCalc() {
        $('#transxnTbl tbody tr').each(function() {
            let total;
            switch ($(this).index()*1) {
                case 0:
                    $(this).find('td:eq(1)').text($('#stock_subttl').val());
                    $(this).find('td:eq(2)').text($('#exp_subttl').val());
                    $(this).find('td:eq(3)').text($('#asset_subttl').val());
                    total = sumLine($('#stock_subttl').val(), $('#exp_subttl').val(), $('#asset_subttl').val());
                    purchaseGrandTotal = total;
                    $('#paidttl').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#paidttl').val());
                    break;
                case 1:
                    $(this).find('td:eq(1)').text($('#stock_tax').val());
                    $(this).find('td:eq(2)').text($('#exp_tax').val());
                    $(this).find('td:eq(3)').text($('#asset_tax').val());
                    total = sumLine($('#stock_tax').val(), $('#exp_tax').val(), $('#asset_tax').val());
                    purchaseGrandTotal = total;
                    $('#grandtax').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#grandtax').val());
                    break;
                case 2:
                    $(this).find('td:eq(1)').text($('#stock_grandttl').val());
                    $(this).find('td:eq(2)').text($('#exp_grandttl').val());
                    $(this).find('td:eq(3)').text($('#asset_grandttl').val());
                    total = sumLine($('#stock_grandttl').val(), $('#exp_grandttl').val(), $('#asset_grandttl').val());
                    purchaseGrandTotal = total;
                    $('#grandttl').val(total.toLocaleString());
                    $(this).find('td:eq(4)').text($('#grandttl').val());
                    break;
            }

            checkMilestoneBudget($('#project_milestone').find('option:selected').text());

        });
    }
    
    // Tax condition
    function taxRule(id, tax) {
        $('#'+ id +' option').each(function() {
            const itemtax = $(this).val();
            $(this).removeClass('d-none');
            if (itemtax != tax && itemtax != 0) $(this).addClass('d-none');
            $(this).attr('selected', false);
            if (itemtax == tax) $(this).attr('selected', true).change();
        }); 
    }


    /**
     * Stock Tab
     */
    let stockRowId = @json(count($po->products));
    const stockHtml = [$('#stockTbl tbody tr:eq(0)').html(), $('#stockTbl tbody tr:eq(1)').html()];
    $('#stockTbl tbody tr:lt(2)').remove(); 
    const stockUrl = "{{ route('biller.products.purchase_search') }}"
    const projectstockUrl = "{{ route('biller.projects.project_search') }}"
    $('.projectstock').autocomplete(prediction(projectstockUrl,projectstockSelect));
    $('.stockname').autocomplete(predict(stockUrl, stockSelect));
    $('#stockTbl').on('click', '#addstock, .remove', function() {
        if ($(this).is('#addstock')) {
            stockRowId++;
            const i = stockRowId;
            const html = stockHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#stockTbl tbody tr:eq(-3)').before(html);
            $('.stockname').autocomplete(predict(stockUrl, stockSelect));
            $('.projectstock').autocomplete(prediction(projectstockUrl,projectstockSelect));
            taxRule('rowtax-'+i, $('#tax').val());

            //Add the previous supplier data            
            let priceCustomer = '';
                $('#pricegroup_id option').each(function () {
                    if ($('#supplierid').val() == $(this).val())
                    priceCustomer = $(this).val();
                    console.log(priceCustomer);
                });
                
                $('#pricegroup_id').val(priceCustomer);
        }

        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
            calcStock();
        }    
    })
    $('#stockTbl').on('change', '.qty, .price, .rowtax, .uom, .warehouse', function() {
        const el = $(this);
        const row = el.parents('tr:first');

        const qty = accounting.unformat(row.find('.qty').val());
        const price = accounting.unformat(row.find('.price').val());

        const rowtax = 1 + row.find('.rowtax').val()/100;
        const amount = qty * price * rowtax;
        const taxable = amount - (qty * price);

        row.find('.price').val(accounting.formatNumber(price));
        row.find('.amount').text(accounting.formatNumber(amount));
        row.find('.taxable').val(accounting.formatNumber(taxable));
        row.find('.stocktaxr').val(accounting.formatNumber(taxable));
        row.find('.stockamountr').val(accounting.formatNumber(amount));
        calcStock();

        if ($(this).is('.price')) {
            row.find('.uom').attr('required', true);
            row.next().find('.descr').attr('required', true);
        } else if ($(this).is('.uom')) {
            const purchasePrice = el.find('option:selected').attr('purchase_price');
            row.find('.price').val(purchasePrice).change();
        }else if(el.is('.warehouse')){
            row.find('.projectstock').val('').attr('disabled', true);
            const projectid = row.find('.stockitemprojectid').attr('id');
            row.find(projectid).val(0);
        }
    });
    $('#qty-0').change();
    function calcStock() {
        let tax = 0;
        let grandTotal = 0;
        $('#stockTbl tbody tr').each(function() {
            if (!$(this).find('.qty').val()) return;
            const qty = accounting.unformat($(this).find('.qty').val());
            const price = accounting.unformat($(this).find('.price').val());
            const rowtax = $(this).find('.rowtax').val()/100 + 1;

            const amount = qty * price * rowtax;
            const taxable = amount - qty * price;
            tax += taxable;
            grandTotal += amount;
        });
        $('#invtax').text(accounting.formatNumber(tax));
        $('#stock_tax').val(accounting.formatNumber(tax));
        $('#stock_grandttl').val(accounting.formatNumber(grandTotal));
        $('#stock_subttl').val(accounting.formatNumber(grandTotal - tax));
        transxnCalc();
    }

    // stock select autocomplete
    let stockNameRowId = 0;
    function stockSelect(event, ui) {
        const {data} = ui.item;
        const i = stockNameRowId;
        $('#stockitemid-'+i).val(data.id);
        $('#stockdescr-'+i).val(data.name);
        $('#product_code-'+i).val(data.product_code);

        const purchasePrice = parseFloat(data.purchase_price);
        $('#price-'+i).val(accounting.formatNumber(purchasePrice)).change();

        $('#uom-'+i).html('');
        if(data.units)
        data.units.forEach(v => {
            const rate = parseFloat(v.base_ratio) * purchasePrice;
            const option = `<option value="${v.code}" purchase_price="${rate}" >${v.code}</option>`;
            $('#uom-'+i).append(option);
        });
        const option = `<option value="${data.uom}" >${data.uom}</option>`;
        $('#uom-'+i).append(option);
    }
    $('#stockTbl').on('mouseup', '.stockname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.stockname')) stockNameRowId = id;
    });    
    
       function prediction(url, callback) {
        return {
            source: function(request, response) {
                $.ajax({
                    url,
                    dataType: "json",
                    method: "POST",
                    data: {keyword: request.term, projectstock: $('#projectstock').val()},
                    success: function(data) {
                        response(data.map(v => ({
                            label: v.name,
                            value: v.name,
                            data: v
                        })));
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: callback
        };
    }
    // stock select autocomplete
    let projectStockRowId = 0;
    function projectstockSelect(event, ui) {
        const {data} = ui.item;
        const i = projectStockRowId;
        $('#projectstockval-'+i).val(data.id);
        $('#warehouseid-'+i).attr('readonly', true);
        $(`#warehouseid-${i}`).val('');
    }
    $('#stockTbl').on('mouseup', '.projectstock', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.projectstock')) projectStockRowId = id;
    });
    
    /**
     * Expense Tab
     */
    let expRowId = @json(count($po->products));
    const expHtml = [$('#expTbl tbody tr:eq(0)').html(), $('#expTbl tbody tr:eq(1)').html()];
    $('#expTbl tbody tr:lt(2)').remove(); 
    const expUrl = "{{ route('biller.accounts.account_search') }}?type=Expense";
    $('.accountname').autocomplete(predict(expUrl, expSelect));
    $('.projectexp').autocomplete(predict(projectUrl, projectExpSelect));
    $('#expTbl').on('click', '#addexp, .remove', function() {
        if ($(this).is('#addexp')) {
            expRowId++;
            const i = expRowId;
            const html = expHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#expTbl tbody tr:eq(-3)').before(html);
            $('.accountname').autocomplete(predict(expUrl, expSelect));
            $('.projectexp').autocomplete(predict(projectUrl, projectExpSelect));
            const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
            $('#projectexptext-'+i).val(projectText);
            $('#projectexpval-'+i).val($("#project option:selected").val());
            taxRule('expvat-'+i, $('#tax').val());
        }
        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
            calcExp();
        }    
    });
    $('#expTbl').on('change', '.exp_qty, .exp_price, .exp_vat', function() {
        const $tr = $(this).parents('tr:first');
        const qty = $tr.find('.exp_qty').val();
        const price = $tr.find('.exp_price').val().replace(/,/g, '') || 0;
        const rowtax = $tr.find('.exp_vat').val()/100 + 1;
        const amount = qty * price * rowtax;
        const taxable = amount - (qty * price);

        $tr.find('.exp_price').val((price*1).toLocaleString());
        $tr.find('.exp_tax').text(taxable.toLocaleString());
        $tr.find('.exp_amount').text(amount.toLocaleString());
        $tr.find('.exptaxr').val(taxable.toLocaleString());
        $tr.find('.expamountr').val(amount.toLocaleString());
        calcExp();

        if ($(this).is('.exp_price')) {
            $tr.next().find('.descr').attr('required', true);
        }
    });
    $('#expqty-0').change();
    function calcExp() {
        let tax = 0;
        let totalInc = 0;
        $('#expTbl tbody tr').each(function() {
            if (!$(this).find('.exp_qty').val()) return;
            const qty = $(this).find('.exp_qty').val();
            const price = $(this).find('.exp_price').val().replace(/,/g, '') || 0;
            const rowtax = $(this).find('.exp_vat').val()/100 + 1;

            const amount = qty * price * rowtax;
            const taxable = amount - qty * price;
            tax += parseFloat(taxable.toFixed(2));
            totalInc += parseFloat(amount.toFixed(2));
        });
        $('#exprow_taxttl').text(tax.toLocaleString());
        $('#exp_tax').val(tax.toLocaleString());
        $('#exp_subttl').val((totalInc - tax).toLocaleString());
        $('#exp_grandttl').val((totalInc).toLocaleString());
        transxnCalc();
    }

    // account and project autocomplete
    let accountRowId = 0;
    let projectExpRowId = 0;
    function expSelect(event, ui) {
        const {data} = ui.item;
        const i = accountRowId;
        $('#expitemid-'+i).val(data.id);
    }
    function projectExpSelect(event, ui) {
        const {data} = ui.item;
        const i = projectExpRowId;
        $('#projectexpval-'+i).val(data.id);
    }
    $('#expTbl').on('mouseup', '.accountname, .projectexp', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.accountname')) accountRowId = id;
        if ($(this).is('.projectexp')) projectExpRowId = id;
    });


    /**
     * Asset tab
     */
    let assetRowId = @json(count($po->products));;
    const assetHtml = [$('#assetTbl tbody tr:eq(0)').html(), $('#assetTbl tbody tr:eq(1)').html()];
    $('#assetTbl tbody tr:lt(2)').remove(); 
    const assetUrl = "{{ route('biller.assetequipments.product_search') }}";
    $('.assetname').autocomplete(predict(assetUrl, assetSelect));
    $('#assetTbl').on('click', '#addasset, .remove', function() {
        if ($(this).is('#addasset')) {
            assetRowId++;
            const i = assetRowId;
            const html = assetHtml.reduce((prev, curr) => {
                const text = curr.replace(/-0/g, '-'+i).replace(/d-none/g, '');
                return prev + '<tr>' + text + '</tr>';
            }, '');

            $('#assetTbl tbody tr:eq(-3)').before(html);
            $('.assetname').autocomplete(predict(assetUrl, assetSelect));
            taxRule('assetvat-'+i, $('#tax').val());
        }
        if ($(this).is('.remove')) {
            const $tr = $(this).parents('tr:first');
            $tr.next().remove();
            $tr.remove();
            calcAsset();
        }    
    });    
    $('#assetTbl').on('change', '.asset_qty, .asset_price, .asset_vat', function() {
        const $tr = $(this).parents('tr:first');
        const qty = $tr.find('.asset_qty').val();
        const price = $tr.find('.asset_price').val().replace(/,/g, '') || 0;
        const rowtax = $tr.find('.asset_vat').val()/100 + 1;
        const amount = qty * price * rowtax;
        const taxable = amount - (qty * price);

        $tr.find('.asset_price').val((price*1).toLocaleString());
        $tr.find('.asset_tax').text(taxable.toLocaleString());
        $tr.find('.asset_amount').text(amount.toLocaleString());
        $tr.find('.assettaxr').val(taxable.toLocaleString());
        $tr.find('.assetamountr').val(amount.toLocaleString());
        calcAsset();

        if ($(this).is('.asset_price')) {
            $tr.next().find('.descr').attr('required', true);
        }
    });
    $('#assetqty-0').change();
    function calcAsset() {
        let tax = 0;
        let totalInc = 0;
        $('#assetTbl tbody tr').each(function() {
            if (!$(this).find('.asset_qty').val()) return;
            const qty = $(this).find('.asset_qty').val();
            const price = $(this).find('.asset_price').val().replace(/,/g, '') || 0;
            const rowtax = $(this).find('.asset_vat').val()/100 + 1;
            
            const amount = qty * price * rowtax;
            const taxable = amount -  qty * price;
            tax += parseFloat(taxable.toFixed(2));
            totalInc += parseFloat(amount.toFixed(2));
        });
        $('#assettaxrow').text(tax.toLocaleString());
        $('#asset_tax').val(tax.toLocaleString());
        $('#asset_subttl').val((totalInc - tax).toLocaleString());
        $('#asset_grandttl').val((totalInc).toLocaleString());
        transxnCalc();
    }

    // asset and project autocomplete
    let assetNameRowId = 0;
    function assetSelect(event, ui) {
        const {data} = ui.item;
        const i = assetNameRowId;
        $('#assetitemid-'+i).val(data.id);
        $('#assetprice-'+i).val(0).change();
    } 
    $('#assetTbl').on('mouseup', '.assetname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.assetname')) assetNameRowId = id;
    });    

    // autocomplete config method
    function predict(url, callback) {
        return {
            source: function(request, response) {
                $.ajax({
                    url,
                    dataType: "json",
                    method: "POST",
                    data: {keyword: request.term, pricegroup_id: $('#pricegroup_id').val()},
                    success: function(data) {
                        response(data.map(v => ({
                            label: v.name,
                            value: v.name,
                            data: v
                        })));
                    }
                });
            },
            autoFocus: true,
            minLength: 0,
            select: callback
        };
    }
</script>
@endsection
