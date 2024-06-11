<!-- properties -->
<script>
    // ajax
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}});


    $(document).ready(function () {
        // Handle change event of the main select element
        $('#purchase_class').change(function () {
            // Get the selected value
            var selectedValue = $(this).val();

            // Update the values of all other select elements with class 'item-purchase-class'
            $('.item-purchase-class').val(selectedValue);
            //console.log(selectedValue);
        });


        $("#purchase_class").on('input', function(){

            if($(this).val() !== '') {

                $("#project").attr('disabled', 'disabled');
                $("#project").val('');
            }
            else {

                $("#project").removeAttr('disabled');
            }
        });

        $("#project").on('change', function(){

            if ($(this).val() == null) {

                $("#purchase_class").removeAttr('disabled');
            }
            else {

                $("#purchase_class").attr('disabled', 'disabled');
                $("#purchase_class").val('');
            }
        });


    });


    // select2 config
    function select2Config(url, callback) {
        return {
            ajax: {
                url,
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: ({term}) => ({q: term, keyword: term}),
                processResults: callback
            },
            allowClear: true,
        }
    }
    function select2Config2(url, callback) {
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

    // datepicker
    $('.datepicker').datepicker({format: "{{ config('core.user_date_format')}}", autoHide: true});
        
    // On selecting supplier type
    $("input[name='supplier_type']").change(function() {
        $('#supplierbox').html('').attr('disabled', true);
        $('#taxid').val('').attr('readonly', false);
        $('#supplier').val('').attr('readonly', false);
        $('#supplierid').val(1);
        if ($(this).val() == 'supplier') {
            $('#supplierbox').attr('disabled', false);
            $('#taxid').attr('readonly', true);
            $('#supplier').attr('readonly', true);
        }
    });

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
            });
            
            $('#pricegroup_id').val(priceCustomer);
    });

    // load suppliers
    const supplierUrl = "{{ route('biller.suppliers.select') }}";
    function supplierData(data) {
        return {results: data.map(v => ({id: `${v.id}-${v.taxid || ''}`, text: `${v.name} : ${v.email}`}))};
    }
    $('#supplierbox').select2(select2Config(supplierUrl, supplierData));


    //Select Quote From quotes
    $('#quotebox').change(function() {
        const name = $('#quotebox option:selected').text().split(' : ')[0];
        const [id, quote_no] = $(this).val().split('-');
        $('#quoteid').val(quoteid);
        //$('#quoteid').val(id);
        $('#quote').val(name);
        purchaseorderChange();
    });
     // load suppliers
     const quoteUrl = "{{ route('biller.queuerequisitions.select_queuerequisition') }}";
    function quoteData(data) {
        return {results: data.map(v => ({id: v.id+'-'+v.quote_no, text: 'Qt-'+v.quote_no+' : '+v.client_branch}))};
    }
    $('#quotebox').select2(select2Config2(quoteUrl, quoteData));

    const config = {
        ajaxSetup: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        select2: {
            allowClear: true,
        },
        fetchLpoGoods: (queuerequisition_id, pricelist) => {
            return $.ajax({
                url: "{{ route('biller.queuerequisitions.goods') }}",
                type: 'POST',
                quietMillis: 50,
                data: {queuerequisition_id, pricelist},
            });
        }
    };

    $('#quoteselect').change(function () { 
        const name = $('#quoteselect option:selected').val();
        const pricelist = $('#pricegroup_id').val();
        //console.log(pricelist);
        purchaseorderChange(name, pricelist);
    });

    function purchaseorderChange(value, pricelist) {
        //const [value1, value2] = $('#quotebox option:selected').val().split('-');
            const el = value;
            $('#stockTbl tbody').html('');
            if (!value) return;
            config.fetchLpoGoods(value, pricelist).done(data => {
                data.forEach((v,i) => $('#stockTbl tbody').append(this.productRow(v,i)));
                if(data.length > 0){
                    $('#stockTbl tbody').append(this.addRow());
                }
            });
    }

    function productRow(v,i) {
            return `
            <tr>
                <td><input type="text" class="form-control stockname" value="${v.queuerequisition_supplier.descr}" name="name[]" placeholder="Product Name" id='stockname-0'></td>
                <td><input type="text" class="form-control qty" name="qty[]" id="qty-0" value="${v.qty_balance}"></td>  
                <td><input type="text" name="uom[]" id="uom-0" value="${v.uom}" class="form-control uom" required></td> 
                <td><input type="text" value="${v.queuerequisition_supplier.rate}" class="form-control  price" name="rate[]" id="price-0"></td>
                <td>
                    <select class="form-control rowtax" name="itemtax[]" id="rowtax-0">
                        @foreach ($additionals as $tax)
                            <option value="{{ (int) $tax->value }}" {{ $tax->is_default ? 'selected' : ''}}>
                                {{ $tax->name }}
                            </option>
                        @endforeach                                                    
                    </select>
                </td>
                <td><input type="text" class="form-control taxable" value="0"></td>
                <td class="text-center">{{config('currency.symbol')}} <b><span class='amount' id="result-0">0</span></b></td> 
                <td><button type="button" class="btn btn-danger remove"><i class="fa fa-minus-square" aria-hidden="true"></i></button></td>
                <input type="hidden" id="stockitemid-0" value="${v.id}" name="item_id[]">
                <input type="hidden" class="stocktaxr" name="taxrate[]">
                <input type="hidden" class="stockamountr" name="amount[]">
                <input type="hidden" class="stockitemprojectid" name="itemproject_id[]" value="0">
                <input type="hidden" name="type[]" value="Requisit">
                <input type="hidden" name="id[]" value="0">
            </tr>
            <tr>
                <td colspan="2">
                    <input type="text" id="stockdescr-0" value="${v.system_name}" class="form-control descr" name="description[]" placeholder="Product Description">
                </td>
                <td><input type="text" class="form-control product_code" value="${v.product_code}" name="product_code[]" id="product_code-0" readonly></td>
                <td>
                    <select name="warehouse_id[]" class="form-control warehouse" id="warehouseid">
                        <option value="">-- Warehouse --</option>
                        @foreach ($warehouses as $row)
                            <option value="{{ $row->id }}">{{ $row->title }}</option>
                        @endforeach
                    </select>
                </td>
                <td colspan="3">
                    <input type="text" class="form-control projectstock" value="${v.quote_no}" id="projectstocktext-0" placeholder="Search Project By Name">
                    {{-- <input type="hidden" name="itemproject_id[]" id="projectstockval-0"> --}}
                </td>
                <td colspan="6"></td>
            </tr>
            `;
    }
    function addRow(){
        return `
            <tr class="bg-white">
                <td>
                    <button type="button" class="btn btn-success" aria-label="Left Align" id="addstock">
                        <i class="fa fa-plus-square"></i> {{trans('general.add_row')}}
                    </button>
                </td>
                <td colspan="7"></td>
            </tr>
            <tr class="bg-white">
                <td colspan="6" align="right"><b>{{trans('general.total_tax')}}</b></td>                   
                <td align="left" colspan="2">
                    {{config('currency.symbol')}} <span id="invtax" class="lightMode">0</span>
                </td>
            </tr>
            <tr class="bg-white">
                <td colspan="6" align="right">
                    <b>Inventory Total ({{ config('currency.symbol') }})</b>
                </td>
                <td align="left" colspan="2">
                    <input type="text" class="form-control" name="stock_grandttl" value="0.00" id="stock_grandttl" readonly>
                    <input type="hidden" name="stock_subttl" value="0.00" id="stock_subttl">
                    <input type="hidden" name="stock_tax" value="0.00" id="stock_tax">
                </td>
            </tr>
        `;
    }

    // load projects dropdown
    const projectUrl = "{{ route('biller.projects.project_search') }}";
    function projectData(data) {

        data = [{id: 0, name: 'None'}].concat(data).map(v => ({id: v.id, text: v.name, budget: v.budget ? v.budget.budget_total : 0 }));

        loadedProjectDetails = data;
        return {results: data};
    }
    $("#project").select2(select2Config(projectUrl, projectData));


    function getProjectMilestones(projectId, forItems = false, inputClass = ''){

        //console.log(projectId);


        $.ajax({
            url: "{{ route('biller.getProjectMileStones') }}",
            method: 'GET',
            data: { projectId: projectId},
            dataType: 'json', // Adjust the data type accordingly
            success: function(data) {
                // This function will be called when the AJAX request is successful

                var select = null;

                if(forItems === false) select = $('#project_milestone');
                else if(forItems === true) select = $('.item-milestone');
                else if(forItems === false && inputClass !== ''){

                    select = $(inputClass);
                    //console.log("ITEM CLASS ID NI: " + select.id);
                }

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

                    let selectedOptionValue = "{{ @$purchase->project_milestone }}";
                    console.table(@json(@$purchase) );
                    //console.log("MSTONE VALUE IS:  " + selectedOptionValue);
                    if (selectedOptionValue) {
                        select.val(selectedOptionValue);
                    }

                    checkMilestoneBudget(select.find('option:selected').text());

                }

            },
            error: function() {
                // Handle errors here
                //console.log('Error loading data');
            }
        });

    }

    //Load Milestones
    $('#project').change(function() {

        getProjectMilestones($(this).val())
        getProjectMilestones($(this).val(), true);

    });

    // $('.item-purchase-class').on('input', function() {
    //
    //
    //     var elementId = this.id;
    //     //console.log("INPUT ID IS : " + elementId);
    //
    // });


    // $(document).ready(function () {
    //
    //     if($(#tax).val() != 0){
    //
    //         $('#cu_invoice_no').prop('required', true);
    //         $('#taxid').prop('required', true);
    //         $('#reference_no').prop('required', false);
    //     }
    //     else{
    //
    //         $('#cu_invoice_no').prop('required', false);
    //         $('#taxid').prop('required', false);
    //         $('#reference_no').prop('required', true);
    //
    //     }
    //
    // });


    $('#tax').change(function() {

       if($(this).val() != 0){

           $('#cu_invoice_no').prop('required', true);
           $('#cu_invoice_no').prop('readonly', false);

           $('#taxid').prop('required', true);
           $('#ref_type').prop('required', false);
           $('#reference_no').prop('required', false);
       }
       else{

           $('#cu_invoice_no').prop('required', false);
           $('#cu_invoice_no').prop('readonly', true);

           $('#taxid').prop('required', false);
           $('#ref_type').prop('required', true);
           $('#reference_no').prop('required', true);

       }

        // console.table({
        //     TaxValue : $(this).val(),
        //     CU_req : $('#cu_invoice_no').prop('required'),
        //     CU_readonly : $('#cu_invoice_no').prop('readonly'),
        //     TaxId_req : $('#taxid').prop('required'),
        //     refType_req : $('#ref_type').prop('required'),
        // })
    });


    $('#active2').on('input', '[id^="item_purchase_class-"]', function() {
        // Get the ID of the current input element when its value changes
        var inputId = this.id;
        //console.log("Input ID:", inputId);

        // Extract the number at the end of the ID
        var numberAtEnd = inputId.match(/\d+$/);

        let rowId = null
        // Check if a number was found
        if (numberAtEnd) rowId = parseInt(numberAtEnd[0]);

        $('#projectexptext-' + rowId).val(''); //prop('readonly', true);
        $('#item_milestone-' + rowId).val(''); //prop('readonly', true);
    });

    $('#active2').on('input', '[id^="projectexptext-"]', function() {
        // Get the ID of the current input element when its value changes
        var inputId = this.id;
        //console.log("Input ID:", inputId);

        // Extract the number at the end of the ID
        var numberAtEnd = inputId.match(/\d+$/);

        let rowId = null
        // Check if a number was found
        if (numberAtEnd) rowId = parseInt(numberAtEnd[0]);

        $('#item_purchase_class-' + rowId).val(''); //prop('readonly', true);

        // getProjectMilestones($(this).val(), false, 'item-milestone-' + rowId)
    });


        $('#projectexptext-0').change(function() {


            // getProjectMilestones($(this).val(), false, '.item-milestone-' + this.id);
            console.log("ID is " + 'item-milestone-' + this.id.split('-').pop());

        });



    $('#active2').on('input', '[id^="item_milestone-"]', function() {
        // Get the ID of the current input element when its value changes
        var inputId = this.id;
        //console.log("Input ID:", inputId);

        // Extract the number at the end of the ID
        var numberAtEnd = inputId.match(/\d+$/);

        let rowId = null
        // Check if a number was found
        if (numberAtEnd) rowId = parseInt(numberAtEnd[0]);

        $('#item_purchase_class-' + rowId).val(''); //prop('readonly', true);
    });


    $('#active3').on('input', '[id^="asset_purchase_class-"]', function() {

        var inputId = this.id;
        var numberAtEnd = inputId.match(/\d+$/);

        let rowId = null
        if (numberAtEnd) rowId = parseInt(numberAtEnd[0]);

        $('#projectassettext-' + rowId).val('');
    });

    $('#active3').on('input', '[id^="projectassettext-"]', function() {

        var inputId = this.id;
        var numberAtEnd = inputId.match(/\d+$/);

        let rowId = null
        if (numberAtEnd) rowId = parseInt(numberAtEnd[0]);

        $('#asset_purchase_class-' + rowId).val('');
    });



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

        // //console.log("Milestone Budget is " + milestoneBudget + " and purchase total is " + purchaseGrandTotal);

        if(purchaseGrandTotal > milestoneBudget){

            // //console.log( "Milestone Budget is " + milestoneBudget );
            // //console.log( "Milestone Budget Exceeded" );
            $("#milestone_warning").text("Milestone Budget of " + milestoneBudget + " Exceeded!");
        }
        else {
            $("#milestone_warning").text("");
        }


    }

    $('#project_milestone').change(function() {

        checkMilestoneBudget($(this).find('option:selected').text());

    });


    let loadedProjectDetails = [];
    let selectedProjectDetails = {};
    let selectedProjectBudget = 0;

    function checkProjectBudget(){

        console.log('LOADED PROJECT DETAILS!!!!!!!');
        console.table(loadedProjectDetails);

        let selectedProjectIndex = loadedProjectDetails.findIndex((item) => item.id === parseInt($("#project").val()));
        if(selectedProjectIndex !== -1) {

            selectedProjectDetails = loadedProjectDetails[selectedProjectIndex];
            selectedProjectBudget = parseInt(selectedProjectDetails.budget);
        }

        if(purchaseGrandTotal > selectedProjectBudget) $("#budget_warning").text("Project Budget of " + accounting.formatNumber(selectedProjectBudget) + " Exceeded!");
        else $("#budget_warning").text("");


        console.log('SELECTED PROJECT DETAILS!!!!!!!');
        console.table(selectedProjectDetails);

    }


        // On Tax change
    let taxChangeCount = 0;
    $('#tax').change(function() {
        if (taxChangeCount > 0) return;
        const tax = $(this).val();
        $('#rowtax-0').val(tax).change();
        $('#expvat-0').val(tax).change();
        $('#assetvat-0').val(tax).change();
        taxChangeCount++;
    });

    // On project change
    $("#project").change(function() {

        checkProjectBudget();

        const text = $("#project option:selected").text().replace(/\s+/g, ' ');
        $('#projectexptext-0').val(text);
        $('#projectexpval-0').val($(this).val());
        $('#projectstocktext-0').val(text);
        $('#projectstockval-0').val($(this).val());
        $('#projectassettext-0').val(text);
        $('#projectassetval-0').val($(this).val());
    });

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

    // on Tax on amount change
    $('.is_tax_exc').change(function() {
        $('#qty-0').change();
        $('#expqty-0').change();
        $('#assetqty-0').change();
    });

    // edit mode
    let countPurchaseItems = @json(@$purchase->products? $purchase->products->count() : 0);    


    let purchaseGrandTotal = 0;

        <!-- post transaction totals table-->
    const sumLine = (...values) => values.reduce((prev, curr) => prev + curr.replace(/,/g, '')*1, 0);
    function transxnCalc() {
        $('#transxnTbl tbody tr').each(function() {
            let total;
            switch ($(this).index()) {
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
            checkProjectBudget();
        });
    }
</script>

<!-- autocomplete method -->
<script>
    function predict(url, callback) {
        return {
            source: function(request, response) {
                $.ajax({
                    url,
                    dataType: "json",
                    method: "POST",
                    data: {keyword: request.term, pricegroup_id: $('#pricegroup_id').val()},
                    success: data => {
                        // prepend default project option
                        if (url.includes('projects')) {
                            const projectOpt = {
                                id: null, 
                                name: 'None',
                                client_id: null, 
                                branch_id: null
                            };
                            data.splice(0, 0, projectOpt);
                        }
                        
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

<!-- Stock Tab -->
<script>
    const stockUrl = "{{ route('biller.products.purchase_search') }}";
    const stockHtml = [$('#stockTbl tbody tr:eq(0)').html(), $('#stockTbl tbody tr:eq(1)').html()];

    let stockRowId = 0;
    // edit mode
    if (countPurchaseItems) {
        stockRowId = countPurchaseItems;
        $('#stockTbl tbody tr:lt(2)').remove(); 
    }

    // on change project
    $('#stockTbl').on('change', '.projectstock, .warehouse', function() {
        const row = $(this).parents('tr:first');
        if ($(this).is('.projectstock')) {
            row.find('.warehouse').val('');
        } else if ($(this).is('.warehouse')) {
            if ($(this).val()*1 > 0) row.find('.projectstock').val('').attr('readonly', true);
            else row.find('.projectstock').val('').attr('readonly', false);
        }
    });
    
    $('.stockname').autocomplete(predict(stockUrl, stockSelect));
    $('.projectstock').autocomplete(predict(projectUrl, projectStockSelect));
    $('#rowtax-0').mousedown(function() { taxRule('rowtax-0', $('#tax').val()); });
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
            $('.projectstock').autocomplete(predict(projectUrl, projectStockSelect));
            $('#increment-'+i).val(i+1);
            const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
            $('#projectstocktext-'+i).val(projectText);
            $('#projectstockval-'+i).val($("#project option:selected").val());
            taxRule('rowtax-'+i, $('#tax').val());
            //Add the previous supplier data            
            let priceCustomer = '';
                $('#pricegroup_id option').each(function () {
                    if ($('#supplierid').val() == $(this).val())
                    priceCustomer = $(this).val();
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
    $('#stockTbl').on('change', '.qty, .price, .rowtax, .uom', function() {
        const el = $(this);
        const row = el.parents('tr:first');

        const qty = accounting.unformat(row.find('.qty').val());
        const price = accounting.unformat(row.find('.price').val());
        const rowtax = 1 + row.find('.rowtax').val() / 100;

        let amount = 0;
        let taxable = 0;
        if ($('.is_tax_exc').prop('checked')) {
            amount = qty * price * rowtax;
            taxable = qty * price * (rowtax - 1);
        } else {
            amount = qty * price;
            taxable = (amount / rowtax) * (rowtax - 1);
        }

        row.find('.price').val(accounting.formatNumber(price));
        row.find('.amount').text(accounting.formatNumber(amount));
        row.find('.taxable').val(accounting.formatNumber(taxable));
        row.find('.stocktaxr').val(accounting.formatNumber(taxable));
        row.find('.stockamountr').val(accounting.formatNumber(amount));
        calcStock();

        if (el.is('.price')) {
            row.next().find('.descr').attr('required', true);
        }
        if (el.is('.uom')) {
            const purchasePrice = el.find('option:selected').attr('purchase_price');
            row.find('.price').val(purchasePrice).change();
        }
    });
    function calcStock() {
        let tax = 0;
        let grandTotal = 0;
        $('#stockTbl tbody tr').each(function() {
            if (!$(this).find('.qty').val()) return;
            const qty = $(this).find('.qty').val();
            const price = $(this).find('.price').val().replace(/,/g, '') || 0;
            const rowtax = $(this).find('.rowtax').val()/100 + 1;

            let amount = 0;
            let taxable = 0;
            if ($('.is_tax_exc:checked').val() * 1) {
                amount = qty * price * rowtax;
                taxable = amount - qty * price;
            } else {
                amount = qty * price / rowtax;
                taxable = qty * price - amount;
                amount += taxable;
            } 
            tax += parseFloat(taxable.toFixed(2));
            grandTotal += parseFloat(amount.toFixed(2));
        });

        
        $('#invtax').text(tax.toLocaleString());
        $('#stock_tax').val(tax.toLocaleString());
        $('#stock_grandttl').val(grandTotal.toLocaleString());
        $('#stock_subttl').val((grandTotal - tax).toLocaleString());
        transxnCalc();
    }

    // stock and project autocomplete
    let stockNameRowId = 0;
    let projectStockRowId = 0;
    function stockSelect(event, ui) {
        const {data} = ui.item;
        const i = stockNameRowId;
        $('#stockitemid-'+i).val(data.id);
        $('#stockdescr-'+i).val(data.name);
        
        const purchasePrice = accounting.unformat(data.purchase_price);
        $('#price-'+i).val(accounting.formatNumber(purchasePrice)).change();

        $('#uom-'+i).html('');
        if (data.units)
        data.units.forEach(v => {
            const rate = parseFloat(v.base_ratio) * purchasePrice;
            const option = `<option value="${v.code}" purchase_price="${rate}" >${v.code}</option>`;
            $('#uom-'+i).append(option);
        });
        if(data.uom){
            const option = `<option value="${data.uom}" >${data.uom}</option>`;
            $('#uom-'+i).append(option);
        }
    }
    function projectStockSelect(event, ui) {
        const {data} = ui.item;
        $('#projectstockval-'+projectStockRowId).val(data.id);
    }
    $('#stockTbl').on('mouseup', '.projectstock, .stockname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.projectstock')) projectStockRowId = id;
        if ($(this).is('.stockname')) stockNameRowId = id;
    });    
</script>

<!-- Expense Tab -->
<script>
    const expUrl = "{{ route('biller.accounts.account_search') }}?type=Expense";
    const expHtml = [$('#expTbl tbody tr:eq(0)').html(), $('#expTbl tbody tr:eq(1)').html()];

    let expRowId = 0;
    if (countPurchaseItems) {
        expRowId = countPurchaseItems;
        $('#expTbl tbody tr:lt(2)').remove(); 
    }
    
    $('.accountname').autocomplete(predict(expUrl, expSelect));
    $('.projectexp').autocomplete(predict(projectUrl, projectExpSelect));
    $('#expvat-0').mousedown(function() { taxRule('expvat-0', $('#tax').val()); });
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
            $('#expenseinc-'+i).val(i+1);
            const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
            $('#projectexptext-'+i).val(projectText);
            $('#projectexpval-'+i).val($("#project option:selected").val());
            taxRule('expvat-'+i, $('#tax').val());

            getProjectMilestones($('#project').val(), true);

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

        let amount = 0;
        let taxable = 0;
        if ($('.is_tax_exc:checked').val() * 1) {
            amount = qty * price * rowtax;
            taxable = amount - qty * price;
        } else {
            amount = qty * price / rowtax;
            taxable = qty * price - amount;
            amount += taxable;
        }

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
    function calcExp() {
        let tax = 0;
        let totalInc = 0;
        $('#expTbl tbody tr').each(function() {
            if (!$(this).find('.exp_qty').val()) return;
            const qty = $(this).find('.exp_qty').val();
            const price = $(this).find('.exp_price').val().replace(/,/g, '') || 0;
            const rowtax = $(this).find('.exp_vat').val()/100 + 1;

            let amount = 0;
            let taxable = 0;
            if ($('.is_tax_exc:checked').val() * 1) {
                amount = qty * price * rowtax;
                taxable = amount - qty * price;
            } else {
                amount = qty * price / rowtax;
                taxable = qty * price - amount;
                amount += taxable;
            }
            tax += parseFloat(taxable.toFixed(2));
            totalInc += parseFloat(amount.toFixed(2));         
        });
        $('#exprow_taxttl').text(tax.toLocaleString());
        $('#exp_tax').val(tax.toLocaleString());
        $('#exp_grandttl').val((totalInc).toLocaleString());
        $('#exp_subttl').val((totalInc - tax).toLocaleString());
        transxnCalc();
    }
    
    // account and project autocomplete
    let accountRowId = 0;
    let projectExpRowId = 0;
    function expSelect(event, ui) {
        const {data} = ui.item;
        $('#expitemid-'+accountRowId).val(data.id);
    }
    function projectExpSelect(event, ui) {
        const {data} = ui.item;
        $('#projectexpval-'+projectExpRowId).val(data.id);
    }
    $('#expTbl').on('mouseup', '.projectexp, .accountname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.projectexp')) projectExpRowId = id;
        if ($(this).is('.accountname')) accountRowId = id;
    });
</script>

<!-- Asset Tab -->
<script>
    const assetUrl = "{{ route('biller.assetequipments.product_search') }}";
    const assetHtml = [$('#assetTbl tbody tr:eq(0)').html(), $('#assetTbl tbody tr:eq(1)').html()];

    let assetRowId = 0;
    if (countPurchaseItems) {
        assetRowId = countPurchaseItems;
        $('#assetTbl tbody tr:lt(2)').remove(); 
    }
    
    $('.assetname').autocomplete(predict(assetUrl, assetSelect));
    $('.projectasset').autocomplete(predict(projectUrl, projectAssetSelect));
    $('#assetvat-0').mousedown(function() { taxRule('assetvat-0', $('#tax').val()); });
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
            $('#assetinc-'+i).val(i+1);
            $('.projectasset').autocomplete(predict(projectUrl, projectAssetSelect));
            const projectText = $("#project option:selected").text().replace(/\s+/g, ' ');
            $('#projectassettext-'+i).val(projectText);
            $('#projectassetval-'+i).val($("#project option:selected").val());
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

        let amount = 0;
        let taxable = 0;
        if ($('.is_tax_exc:checked').val() * 1) {
            amount = qty * price * rowtax;
            taxable = amount - qty * price;
        } else {
            amount = qty * price / rowtax;
            taxable = qty * price - amount;
            amount += taxable;
        }

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
    function calcAsset() {
        let tax = 0;
        let totalInc = 0;
        $('#assetTbl tbody tr').each(function() {
            if (!$(this).find('.asset_qty').val()) return;
            const qty = $(this).find('.asset_qty').val();
            const price = $(this).find('.asset_price').val().replace(/,/g, '') || 0;
            const rowtax = $(this).find('.asset_vat').val()/100 + 1;

            let amount = 0;
            let taxable = 0;
            if ($('.is_tax_exc:checked').val() * 1) {
                amount = qty * price * rowtax;
                taxable = amount - qty * price;
            } else {
                amount = qty * price / rowtax;
                taxable = qty * price - amount;
                amount += taxable;
            }
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
    let projectAssetRowId = 0;
    function assetSelect(event, ui) {
        const {data} = ui.item;
        const i = assetNameRowId;
        $('#assetitemid-'+i).val(data.id);
        $('#assetprice-'+i).val(0).change();
    } 
    function projectAssetSelect(event, ui) {
        const {data} = ui.item;
        $('#projectassetval-'+projectAssetRowId).val(data.id);
    }
    $('#assetTbl').on('mouseup', '.projectasset, .assetname', function() {
        const id = $(this).attr('id').split('-')[1];
        if ($(this).is('.projectasset')) projectAssetRowId = id;
        if ($(this).is('.assetname')) assetNameRowId = id;
    });    
</script>
