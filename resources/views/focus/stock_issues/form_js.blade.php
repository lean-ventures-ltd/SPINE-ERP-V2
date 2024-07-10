{{ Html::script('focus/js/select2.min.js') }}
<script type="text/javascript">
    config = {
        ajax: {headers: {'X-CSRF-TOKEN': "{{ csrf_token() }}"}},
        date: {format: "{{ config('core.user_date_format')}}", autoHide: true},
        autoCompleteCb: () => {
            return {
                source: function(request, response) {
                    $.ajax({
                        url: "{{ route('biller.products.quote_product_search') }}",
                        data: {keyword: request.term},
                        method: 'POST',
                        success: result => response(result.map(v => ({
                            label: `${v.name}`,
                            value: v.name,
                            data: v
                        }))),
                    });
                },
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {
                    const {data} = ui.item;
                    let row = Index.currRow;
                    row.find('.prodvar-id').val(data.id);
                    row.find('.qty-onhand').text(accounting.unformat(data.qty));
                    row.find('.qty-onhand-inp').val(accounting.unformat(data.qty));
                    row.find('.qty-rem').text(accounting.unformat(data.qty));
                    row.find('.qty-rem-inp').val(accounting.unformat(data.qty));
                    row.find('.cost').val(accounting.unformat(data.purchase_price));
                    if (data.units && data.units.length) {
                        const unit = data.units[0];
                        row.find('.unit').text(unit.code);
                    }
                    if (data.warehouses && data.warehouses.length) {
                        row.find('.source option:not(:first)').remove();
                        data.warehouses.forEach(v => {
                            const productsQty = accounting.unformat(v.products_qty);
                            row.find('.source').append(`<option value="${v.id}" products_qty="${productsQty}">${v.title} (${productsQty})</option>`)
                        });
                    }
                    if ($('#issue_to').val() == 'Employee') row.find('.assignee').attr('disabled', true);
                    else row.find('.assignee').attr('disabled', false);
                    // on edit
                    row.find('.issue-qty').attr('readonly', false);
                    row.find('.source-inp').remove();
                    row.find('.source').attr('disabled', false);
                }
            };
        },
        invoiceSelect: {
            allowClear: true,
            ajax: {
                url: "{{ route('biller.stock_issues.select_invoices') }}",
                dataType: 'json',
                type: 'POST',
                data: ({term}) => ({search: term, customer_id: $("#customer").val()}),
                processResults: data => {
                    return { 
                        results: data.map(v => ({
                            text: v.notes,
                            id: v.id
                        }))
                    }
                },
            }
        },
    };

    const Index = {
        currRow: '',
        sourceTd: '',
        assigneeTd: '',
        quoteOpts: '',
        initRow: '',
        rowIndex: 0,

        init() {
            $('#productsTbl tbody td').css({paddingLeft: '5px', paddingRight: '5px', paddingBottom: 0});
            $.ajaxSetup(config.ajax);
            $('.datepicker').datepicker(config.date).datepicker('setDate', new Date());
            $('#account').select2({ allowClear: true });
            $('#invoice').select2(config.invoiceSelect);
            Index.initRow = $('#productsTbl tbody tr:first');
            Index.quoteOpts = $('#quote option:not(:first)').clone();
            ['#employee', '#customer', '#project', '#quote'].forEach(v => $(v).select2({allowClear: true}));

            Index.sourceTd = $('.td-source:first').clone();
            Index.assigneeTd = $('.td-assignee:first').clone();
            $('#name-1').autocomplete(config.autoCompleteCb());
            ['#source-1', '#assignee-1'].forEach(v => $(v).select2({allowClear: true}));

            $('#add-item').click(Index.addItemClick);
            $('#issue_to').change(Index.issueToChange);
            $('#quote').change(Index.quoteChange);
            $('#customer').change(Index.customerChange);
            $('#project').change(Index.projectChange);
            $('#ref').change(Index.referenceChange).change();
            $('#invoice').change(Index.invoiceChange);

            $('#productsTbl').on('keyup', '.issue-qty', Index.qtyCostKeyUp);
            $('#productsTbl').on('change', '.source', Index.qtyCostKeyUp);
            $('#productsTbl').on('keyup', '.name', function() { Index.currRow = $(this).parents('tr') });
            $('#productsTbl').on('click', '.remove', Index.removeRowClick);

            const data = @json(@$stock_issue);
            const data_items = @json(@$stock_issue->items);
            if (data && data_items.length) {
                $('.datepicker').datepicker('setDate', new Date(data.date));
                $('#issue_to').val(data.issue_to);
                $('#productsTbl tbody tr').each(function(i) {
                    const row = $(this);
                    const v = data_items[i];
                    if (i > 0) {
                        row.find('.name').autocomplete(config.autoCompleteCb());
                        row.find('.source').select2({allowClear: true});
                        row.find('.assignee').select2({allowClear: true});
                    }
                    row.find('.qty-onhand-inp').val(v.qty_onhand*1);
                    row.find('.qty-rem-inp').val(v.qty_rem*1);
                    row.find('.cost').val(v.cost*1);
                    row.find('.amount').val(v.amount*1);
                    row.find('.prodvar-id').val(v.productvar_id);
                });
                if (data.issue_to == 'Employee') {
                    $('#employee').parents(".select-col").removeClass('d-none');
                    $('#productsTbl .assignee').attr('disabled', true);
                } else if (data.issue_to == 'Customer') {
                    $('#customer').parents(".select-col").removeClass('d-none');
                    $('#employee').parents(".select-col").addClass('d-none');
                    $('#productsTbl .assignee').attr('disabled', false);
                } else {
                    $('#project').parents(".select-col").removeClass('d-none');
                    $('#employee').parents(".select-col").addClass('d-none');
                    $('#productsTbl .assignee').attr('disabled', false);

                }
                Index.calcTotals();
            }

            getBudgetLinesByQuote($('#quote').val());
        },

        referenceChange() {
            if (this.value == 'invoice') {
                $('.quote-col').addClass('d-none');
                $('.invoice-col').removeClass('d-none');
            } else {
                $('.quote-col').removeClass('d-none');
                $('.invoice-col').addClass('d-none');
            }
            // $('#invoice').val('').change();
            // $('#quote').val('').change();
        },

        quoteChange() {

            // console.table({currQuote: $('#quote').val()})

            $('#productsTbl tbody tr:not(:first)').remove();
            $('#productsTbl .remove').click();
            $('#total').val('');
            $('#productsTbl tbody').html('');
            if (this.value) {
                let quote_id = this.value;

                $.post("{{ route('biller.stock_issues.quote_pi_products') }}", { quote_id: quote_id })
                    .done(data => {
                        try {

                            // console.log("INSIDE AJAX FOR QUOTE ITEMS")
                            // console.table(data.productvars);
                            // console.log(Index.initRow.clone());

                            data.productvars.forEach((v,i) => {
                                $('#productsTbl tbody').append(Index.productRow(v,i));
                                console.log(i+1);
                                const selectElement = $(`#productsTbl tbody tr:last-child #source-${i + 1}`);
                                const selectElement1 = $(`#productsTbl tbody tr:last-child #assignee-${i + 1}`);

                                // Clear existing options except the first one
                                selectElement.find('option:not(:first)').remove();
                                // selectElement1.find('option:not(:first)').remove();
                                if (v.warehouses && v.warehouses.length) {
                                    v.warehouses.forEach(warehouse => {
                                        const productsQty = accounting.unformat(warehouse.products_qty);
                                        selectElement.append(`<option value="${warehouse.id}" products_qty="${productsQty}">${warehouse.title} (${productsQty})</option>`);
                                    });
                                }

                                // Reinitialize plugins if necessary
                                if (selectElement.hasClass('source')) {
                                    selectElement.select2({
                                        placeholder: selectElement.data('placeholder')
                                    });
                                }
                                if (selectElement1.hasClass('assignee')) {
                                    selectElement1.select2({
                                        placeholder: selectElement1.data('placeholder')
                                    });
                                }
                                if ($('#issue_to').val() == 'Employee') $('#productsTbl tbody tr').find('.assignee').attr('disabled', true);
                                else $('#productsTbl tbody tr').find('.assignee').attr('disabled', false);
                                // $('.projectstock').autocomplete(config.prediction(projectstockUrl, Form.projectstockSelect));
                            });

                            // data.productvars.forEach((v, i) => {

                            //     // if (i > 0) $('#add-item').click();
                            //     // let row = $('#productsTbl tbody tr:last');
                            //     const row = Index.initRow.clone(true);
                            //     row.find('.prodvar-id').val(v.id);
                            //     row.find('.name').val(v.name);
                            //     row.find('.product-code').text(v.code);
                            //     // if(data.budgetDetails.length > 0) row.find('.budget').text(parseFloat(data.budgetDetails[i].new_qty).toFixed(2));
                            //     // else row.find('.budget').text(parseFloat('0').toFixed(2));
                            //     row.find('.budget').text(parseFloat(v.budget_qty).toFixed(2));
                            //     row.find('.qty-onhand').text(accounting.unformat(v.qty));
                            //     row.find('.qty-onhand-inp').val(accounting.unformat(v.qty));
                            //     row.find('.qty-rem').text(accounting.unformat(v.qty));
                            //     row.find('.qty-rem-inp').val(accounting.unformat(v.qty));
                            //     row.find('.cost').val(accounting.unformat(v.purchase_price));
                            //     if (v.unit && v.unit.id) row.find('.unit').text(v.unit.code);
                            //     if (v.warehouses && v.warehouses.length) {
                            //         row.find('.source option:not(:first)').remove();
                            //         v.warehouses.forEach(warehouse => {
                            //             const productsQty = accounting.unformat(warehouse.products_qty);
                            //             row.find('.source').append(`<option value="${warehouse.id}" products_qty="${productsQty}">${warehouse.title} (${productsQty})</option>`);
                            //         });
                            //     }
                            //     if ($('#issue_to').val() == 'Employee') row.find('.assignee').attr('disabled', true);
                            //     else row.find('.assignee').attr('disabled', false);
                                
                            //     $('#productsTbl tbody').append(row);
                            //     console.log(row);
                            // });


                        } catch (error) {
                            console.error("An error occurred while processing the data: ", error);
                        }
                    })
                    .fail(function(jqXHR, textStatus, errorThrown) {
                        console.error("Request failed: ", textStatus, errorThrown);
                        console.error("Response details: ", jqXHR.responseText);
                        console.error("Request body: ", this.data);
                    });

            }
        },

        productRow(v,i){
            let unit = v.unit ? v.unit.code : '';
            return `
                 <tr>
                    <td><textarea id="name-${i+1}" class="form-control name" cols="30" rows="1" autocomplete="off" required>${v.name}</textarea></td>
                    <td><span class="product-code">${v.code}</span></td>
                    <td><span class="unit">${unit}</span></td>
                    <td><span class="budget">${parseFloat(v.budget_qty).toFixed(2)}</span></td>
                    <td><span class="qty-onhand">${accounting.unformat(v.qty)}</span></td>
                    <td><span class="qty-rem">${accounting.unformat(v.qty)}</span></td>
                    <td><input type="text" name="issue_qty[]" class="form-control issue-qty" autocomplete="off"></td>
                    <td class="td-source">
                        <select name="warehouse_id[]" id="source-${i+1}" class="form-control source" data-placeholder="Search Location">
                            <option value=""></option>
                        </select>
                    </td>
                    <td class="td-assignee">
                        <div class="row no-gutters">
                            <div class="col-md-10">
                                <select name="assignee_id[]" id="assignee-${i+1}" class="form-control assignee" data-placeholder="Search Employee" disabled>
                                    <option value=""></option>
                                    @foreach ($employees as $row)
                                        <option value="{{ $row->id }}">
                                            {{ $row->first_name }} {{ $row->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <span class="badge badge-danger float-right mt-1 remove" style="cursor:pointer" role="button"><i class="fa fa-trash"></i></span>
                            </div>
                        </div>
                    </td>
                    <input type="hidden" name="qty_onhand[]" class="qty-onhand-inp" value="${accounting.unformat(v.qty)}">
                    <input type="hidden" name="qty_rem[]" class="qty-rem-inp" value="${accounting.unformat(v.qty)}">
                    <input type="hidden" name="cost[]" class="cost" value="${accounting.unformat(v.purchase_price)}">
                    <input type="hidden" name="amount[]" class="amount">
                    <input type="hidden" name="productvar_id[]" class="prodvar-id" value="${v.id}">
                </tr>
            `;
        },
        invoiceRow(v,i){
            let unit = v.unit ? v.unit.code : '';
            return `
                 <tr>
                    <td><textarea id="name-${i+1}" class="form-control name" cols="30" rows="1" autocomplete="off" required>${v.name}</textarea></td>
                    <td><span class="product-code">${v.code}</span></td>
                    <td><span class="unit">${v.uom}</span></td>
                    <td><span class="budget"></span></td>
                    <td><span class="qty-onhand">${accounting.unformat(v.qty)}</span></td>
                    <td><span class="qty-rem">${accounting.unformat(v.qty)}</span></td>
                    <td><input type="text" name="issue_qty[]" class="form-control issue-qty" autocomplete="off"></td>
                    <td class="td-source">
                        <select name="warehouse_id[]" id="source-${i+1}" class="form-control source" data-placeholder="Search Location">
                            <option value=""></option>
                        </select>
                    </td>
                    <td class="td-assignee">
                        <div class="row no-gutters">
                            <div class="col-md-10">
                                <select name="assignee_id[]" id="assignee-${i+1}" class="form-control assignee" data-placeholder="Search Employee" disabled>
                                    <option value=""></option>
                                    @foreach ($employees as $row)
                                        <option value="{{ $row->id }}">
                                            {{ $row->first_name }} {{ $row->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <span class="badge badge-danger float-right mt-1 remove" style="cursor:pointer" role="button"><i class="fa fa-trash"></i></span>
                            </div>
                        </div>
                    </td>
                    <input type="hidden" name="qty_onhand[]" class="qty-onhand-inp" value="${accounting.unformat(v.qty)}">
                    <input type="hidden" name="qty_rem[]" class="qty-rem-inp" value="${accounting.unformat(v.qty)}">
                    <input type="hidden" name="cost[]" class="cost" value="${accounting.unformat(v.purchase_price)}">
                    <input type="hidden" name="amount[]" class="amount">
                    <input type="hidden" name="productvar_id[]" class="prodvar-id" value="${v.id}">
                </tr>
            `;
        },

        invoiceChange() {
            $('#productsTbl tbody').html('');
            const url = "{{ route('biller.stock_issues.issue_invoice_items') }}";
            const params = {invoice_id: $(this).val()};
            $.post(url, params, data => {
                console.log(data);
                data.forEach((v,i) => {
                    $('#productsTbl tbody').append(Index.invoiceRow(v,i));
                    // const row = Index.initRow.clone();
                    // row.find('.serial').text(i+1);
                    // row.find('.name').text(v.name);
                    // row.find('.product-code').text(v.code);
                    // row.find('.unit').text(v.uom);
                    // row.find('.qty-onhand').text(+v.qty);
                    // row.find('.qty-onhand-inp').val(+v.qty);
                    // row.find('.cost').val(+v.purchase_price);
                    // row.find('.prodvar-id').val(v.id);
                    // row.find('.verified-item-id').val(v.verified_item_id);
                    // $('#productsTbl tbody').append(row);
                    const selectElement = $(`#productsTbl tbody tr:last-child #source-${i + 1}`);
                    const selectElement1 = $(`#productsTbl tbody tr:last-child #assignee-${i + 1}`);

                    // Clear existing options except the first one
                    selectElement.find('option:not(:first)').remove();
                    // selectElement1.find('option:not(:first)').remove();
                    if (v.warehouses && v.warehouses.length) {
                        v.warehouses.forEach(warehouse => {
                            const productsQty = accounting.unformat(warehouse.products_qty);
                            selectElement.append(`<option value="${warehouse.id}" products_qty="${productsQty}">${warehouse.title} (${productsQty})</option>`);
                        });
                    }

                    // Reinitialize plugins if necessary
                    if (selectElement.hasClass('source')) {
                        selectElement.select2({
                            placeholder: selectElement.data('placeholder')
                        });
                    }
                    if (selectElement1.hasClass('assignee')) {
                        selectElement1.select2({
                            placeholder: selectElement1.data('placeholder')
                        });
                    }
                    if ($('#issue_to').val() == 'Employee') $('#productsTbl tbody tr').find('.assignee').attr('disabled', true);
                    else $('#productsTbl tbody tr').find('.assignee').attr('disabled', false);
                });                
            });
        },

        customerChange() {
            $('#quote option:not(:first)').remove();
            const value = this.value;
            if (!value) return;
            Index.quoteOpts.each(function() {
                let customerId = $(this).attr('customer_id');
                if (customerId == value) $('#quote').append($(this));
            });
        },

        projectChange() {
            $('#quote option:not(:first)').remove();
            if (!this.value) return;
            let quoteIds = $(this).find(':selected').attr('quote_ids');
            quoteIds = quoteIds.split(',');
            Index.quoteOpts.each(function() {
                let value = $(this).attr('value');
                if (quoteIds.includes(value)) $('#quote').append($(this));
            });
        },

        issueToChange() {
            $('.select-col').addClass('d-none');
            $('#productsTbl .assignee').attr('disabled', false);
            if (this.value == 'Employee') {
                $('#employee').parents(".select-col").removeClass('d-none');
                $('#productsTbl .assignee').attr('disabled', true);
            } else if (this.value == 'Customer') {
                $('#customer').parents(".select-col").removeClass('d-none');
            } else {
                $('#project').parents(".select-col").removeClass('d-none');
            }
        },

        addItemClick() {
            let row = $('#productsTbl tbody tr:last').clone();
            let indx = accounting.unformat(row.find('.name').attr('id').split('-')[1]);
            console.log(indx);
            row.find('input').attr('value', '');
            row.find('textarea').text('');
            row.find('.unit, .qty-onhand, .qty-rem').text('');
            row.find('.name').attr('id', `name-${indx+1}`);

            let sourceTd = Index.sourceTd.clone();
            let assigneeTd = Index.assigneeTd.clone();
            row.find('.td-source').children().remove();
            row.find('.td-assignee').children().remove();
            row.find('.td-source').append(sourceTd.children());
            row.find('.td-assignee').append(assigneeTd.children());
            row.find('.source').attr('id', `source-${indx+1}`);
            row.find('.assignee').attr('id', `assignee-${indx+1}`);

            $('#productsTbl tbody').append(`<tr>${row.html()}</tr>`);
            row = $('#productsTbl tbody tr:last');
            row.find('.name').autocomplete(config.autoCompleteCb());
            row.find('.source').select2({allowClear: true});
            row.find('.assignee').select2({allowClear: true});
            if ($('#issue_to').val() == 'Employee') row.find('.assignee').attr('disabled', true);
            else row.find('.assignee').attr('disabled', false);
        },

        removeRowClick() {
            let row = $(this).parents('tr');
            if (!row.siblings().length) {
                row.find('input, textarea, select').each(function() { $(this).val('').change() });
                row.find('.unit, .qty-onhand, .qty-rem').text('');
                row.find('.source option:not(:first)').remove();
            } else row.remove();
            Index.calcTotals();
        },

        qtyCostKeyUp() {
            const row = $(this).parents('tr');
            const cost = accounting.unformat(row.find('.cost').val());
            const sourceQty = accounting.unformat(row.find('.source option:selected').attr('products_qty'));
            let qtyOnhand = accounting.unformat(row.find('.qty-onhand').text());
            let issueQty = accounting.unformat(row.find('.issue-qty').val());
            if (issueQty < 0) issueQty = 0;
            let qtyRem = 0;

            if (row.find('.source').val()) {
                qtyOnhand = sourceQty;
                qtyRem = sourceQty;
            }
            if (issueQty > qtyOnhand) issueQty = qtyOnhand;
            const amount = issueQty * cost;
            qtyRem = qtyOnhand - issueQty;

            row.find('.issue-qty').val(issueQty);
            row.find('.qty-onhand').text(qtyOnhand);
            row.find('.qty-onhand-inp').val(qtyOnhand);
            row.find('.qty-rem').text(qtyRem);
            row.find('.qty-rem-inp').val(qtyRem);
            row.find('.amount').val(accounting.formatNumber(amount));
            Index.calcTotals();
        },

        calcTotals() {
            let total = 0;
            $('#productsTbl tbody tr').each(function() {
                const amount = accounting.unformat($(this).find('.amount').val());
                total += amount;
            });
            $('#total').val(accounting.formatNumber(total));
        },


    };

    function getBudgetLinesByQuote(quoteId = null){

        if(!quoteId) return;

        //console.log(quoteId);
        $.ajax({
            url: "{{ route('biller.getBudgetLinesByQuote') }}",
            method: 'GET',
            data: { quoteId: quoteId},
            dataType: 'json', // Adjust the data type accordingly
            success: function(data) {
                // This function will be called when the AJAX request is successful

                var select = $('#budget_line');

                // Clear any existing options
                select.empty();

                if(data.length) console.table(data);
                else console.log('No Budget Lines Created For This Project');

                if(data.length === 0){

                    select.append($('<option>', {
                        value: null,
                        text: 'No Budget Lines Created For This Project'
                    }));

                } else {

                    select.append($('<option></option>').attr('value', null).text('Select a Budget Line'));


                    // Add new options based on the received data
                    for (var i = 0; i < data.length; i++) {

                        const options = { year: 'numeric', month: 'short', day: 'numeric' };
                        const date = new Date(data[i].due_date);

                        select.append($('<option>', {
                            value: data[i].id,
                            text: data[i].name + ' | Balance: ' +  parseFloat(data[i].balance).toFixed(2) + ' | Due on ' + date.toLocaleDateString('en-US', options)
                        }));

                    }

                    let selectedOptionValue = "{{ @$stock_issue->budget_line }}";

                    if (selectedOptionValue) {
                        select.val(selectedOptionValue);
                    }

                    checkBudgetLine(select.find('option:selected').text());

                }

            },
            error: function(error) {
                // Handle errors here
                console.log(error);
            }
        });
    }

    function checkBudgetLine(budgetLineString){

        // Get the value of the input field
        let selectedBudgetLine = budgetLineString;

        // console.log("SELECTED MILESTONE IS : " + budgetLineString);

        if(budgetLineString === 'Select a Budget Line') {

            // console.log("NO MILESTONE SELECTED!!")
            return false;
        }

        // Specify the start and end strings
        let startString = 'Balance: ';
        let endString = ' | Due on';

        // Find the index of the start and end strings
        let startIndex = selectedBudgetLine.indexOf(startString);
        let endIndex = selectedBudgetLine.indexOf(endString, startIndex + startString.length);

        // Extract the string between start and end
        let budgetLineBudget = parseFloat(parseFloat(selectedBudgetLine.substring(startIndex + startString.length, endIndex)).toFixed(2));

        // //console.log("Budget Line Budget is " + budgetLineBudget + " and purchase total is " + purchaseGrandTotal);

        const stockIssueTotal = parseFloat($("#total").val().replace(/,/g, ''));

        // console.table({budgetLineBudget, stockIssueTotal});

        if(stockIssueTotal > budgetLineBudget){

            // //console.log( "Budget Line Budget is " + budgetLineBudget );
            // //console.log( "Budget Line Budget Exceeded" );
            $("#budget_line_warning").text("Budget Line of " + budgetLineBudget + " Exceeded!");
        }
        else {
            $("#budget_line_warning").text("");
        }


    }

    $('#budget_line').change(function() {

        checkBudgetLine($(this).find('option:selected').text());
    });

    $('#total').change(function() {

        checkBudgetLine($('#budget_line').find('option:selected').text());
    });

    $('.issue-qty').change(function() {

        checkBudgetLine($('#budget_line').find('option:selected').text());
    });

    $('#quote').change(function() {

        $("#budget_line_warning").text("");
        getBudgetLinesByQuote($(this).val());
    });


    $(Index.init);
</script>
