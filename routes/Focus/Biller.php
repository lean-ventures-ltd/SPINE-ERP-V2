<?php

/**
 * FocusRoutes
 *
 */


// Product Refill
Route::group(['namespace' => 'refill_customer'], function () {
    Route::resource('refill_customers', 'RefillCustomersController');
    Route::post('refill_customers/get', 'RefillCustomersTableController')->name('refill_customers.get');
});
Route::group(['namespace' => 'refill_product'], function () {
    Route::resource('refill_products', 'RefillProductsController');
    Route::post('refill_products/get', 'RefillProductsTableController')->name('refill_products.get');
});
Route::group(['namespace' => 'refill_product_category'], function () {
    Route::resource('refill_product_categories', 'RefillProductCategoriesController');
    Route::post('refills/product_categories/get', 'RefillProductCategoriesTableController')->name('refill_product_categories.get');
});
Route::group(['namespace' => 'product_refill'], function () {
    Route::resource('product_refills', 'ProductRefillsController');
    Route::post('product_refills/get', 'ProductRefillsTableController')->name('product_refills.get');
});

// Utility bills
Route::group(['namespace' => 'utility_bill'], function () {
    Route::get('utility-bills/create-kra', 'UtilityBillController@create_kra_bill')->name('utility-bills.create_kra_bill');

    Route::post('utility-bills/employee_bills', 'UtilityBillController@employee_bills')->name('utility-bills.employee_bills');
    Route::post('utility-bills/goods-receive-note', 'UtilityBillController@goods_receive_note')->name('utility-bills.goods_receive_note');
    Route::post('utility-bills/store-kra', 'UtilityBillController@store_kra_bill')->name('utility-bills.store_kra_bill');
    Route::post('utility-bills/store-kra', 'UtilityBillController@store_kra_bill')->name('utility-bills.store_kra_bill');
    Route::resource('utility-bills', 'UtilityBillController');
    // data table
    Route::post('utility-bills/get', 'UtilityBillTableController')->name('utility-bills.get');
});

// supplier bill payment
Route::group(['namespace' => 'billpayment'], function () {
    Route::resource('billpayments', 'BillPaymentController');
    // data table
    Route::post('billpayments/get', 'BillPaymentTableController')->name('billpayments.get');
  });
  

//  Accounts
Route::group(['namespace' => 'account'], function () {
    Route::get('accounts/profit_and_loss/{type}', 'AccountsController@profit_and_loss')->name('accounts.profit_and_loss');
    Route::get('accounts/balancesheet/{type}', 'AccountsController@balance_sheet')->name('accounts.balance_sheet');
    Route::get('accounts/trialbalance/{type}', 'AccountsController@trial_balance')->name('accounts.trial_balance');
    Route::get('accounts/project_gross_profit', 'AccountsController@project_gross_profit')->name('accounts.project_gross_profit');
    Route::get('accounts/cashbook', 'AccountsController@cashbook')->name('accounts.cashbook');

    Route::post('accounts/search_next_account_no', 'AccountsController@search_next_account_no')->name('accounts.search_next_account_no');
    Route::post('accounts/search', 'AccountsController@account_search')->name('accounts.account_search');
    Route::resource('accounts', 'AccountsController');
    //For Datatable
    Route::post('accounts/cashbook/transactions', 'CashbookTableController')->name('accounts.get_cashbook');
    Route::post('accounts/project_gross_profit/get', 'ProjectGrossProfitTableController')->name('accounts.get_project_gross_profit');
    Route::post('accounts/get', 'AccountsTableController')->name('accounts.get');
});
// Tax Return
Route::group(['namespace' => 'tax_report'], function () {
    Route::get('tax_reports/filed_report', 'TaxReportsController@filed_report')->name('tax_reports.filed_report');
    Route::post('tax_reports/purchases', 'TaxReportsController@get_purchases')->name('tax_reports.get_purchases');
    Route::post('tax_reports/sales', 'TaxReportsController@get_sales')->name('tax_reports.get_sales');
    Route::resource('tax_reports', 'TaxReportsController');
    // data table
    Route::post('tax_reports/get_filed_items', 'FiledTaxReportsTableController')->name('tax_reports.get_filed_items');
    Route::get('/export-to-excel', 'FiledTaxReportsTableController@exportToExcel');
    Route::post('tax_reports/get', 'TaxReportsTableController')->name('tax_reports.get');
});
Route::group(['namespace' => 'tax_prn'], function () {
    Route::resource('tax_prns', 'TaxPrnsController');
    // data table
    Route::post('tax_prns/get', 'TaxPrnsTableController')->name('tax_prns.get');
});
Route::group(['namespace' => 'allowance'], function () {
    Route::resource('allowances', 'AllowancesController');
    //For Datatable
    Route::post('allowances/get', 'AllowancesTableController')->name('allowances.get');
});

Route::group(['namespace' => 'additional'], function () {
    Route::resource('additionals', 'AdditionalsController');
    //For Datatable
    Route::post('additionals/get', 'AdditionalsTableController')->name('additionals.get');
});

Route::group(['namespace' => 'assetequipment'], function () {
    Route::resource('assetequipments', 'AssetequipmentsController');
    Route::post('assetequipments/ledger_load', 'AssetequipmentsController@ledger_load')->name('assetequipments.ledger_load');
    Route::post('assetequipments/search', 'AssetequipmentsController@product_search')->name('assetequipments.product_search');
    //For Datatable
    Route::post('assetequipments/get', 'AssetequipmentsTableController')->name('assetequipments.get');
});

Route::group(['namespace' => 'toolkit'], function () {
    Route::post('toolkits/select', 'ToolkitController@select')->name('toolkits.select');
    Route::post('toolkits/load', 'ToolkitController@load')->name('toolkits.load');
    Route::resource('toolkits', 'ToolkitController');

    //For Datatable
    Route::post('toolkits/get', 'ToolkitTableController')->name('toolkits.get');
});

Route::group(['namespace' => 'workshift'], function () {
    Route::post('workshifts/select', 'WorkshiftController@select')->name('workshifts.select');
    Route::post('workshifts/load', 'WorkshiftController@load')->name('workshifts.load');
    Route::resource('workshifts', 'WorkshiftController');

    //For Datatable
    Route::post('workshifts/get', 'WorkshiftTableController')->name('workshifts.get');
});


Route::group(['namespace' => 'bank'], function () {
    Route::resource('banks', 'BanksController');
    //For Datatable
    Route::post('banks/get', 'BanksTableController')->name('banks.get');
});
Route::group(['namespace' => 'banktransfer'], function () {
    Route::resource('banktransfers', 'BanktransfersController');
    //For Datatable
    Route::post('banktransfers/get', 'BanktransfersTableController')->name('banktransfers.get');
});
Route::group(['namespace' => 'branch'], function () {
    Route::post('branches/select', 'BranchesController@select')->name('branches.select');
    Route::resource('branches', 'BranchesController');
    //For Datatable
    Route::post('branches/get', 'BranchesTableController')->name('branches.get');
});
Route::group(['namespace' => 'charge'], function () {
    Route::resource('charges', 'ChargesController');
    //For Datatable
    Route::post('charges/get', 'ChargesTableController')->name('charges.get');
});

Route::group(['namespace' => 'creditor'], function () {
    Route::resource('creditors', 'CreditorsController');
    //For Datatable
    Route::post('creditors/get', 'CreditorsTableController')->name('creditors.get');
});
Route::group(['namespace' => 'currency'], function () {
    Route::resource('currencies', 'CurrenciesController');
    //For Datatable
    Route::post('currencies/get', 'CurrenciesTableController')->name('currencies.get');
});
Route::group(['namespace' => 'customergroup'], function () {
    Route::resource('customergroups', 'CustomergroupsController');
    //For Datatable
    Route::post('customergroups/get', 'CustomergroupsTableController')->name('customergroups.get');
});

Route::group(['namespace' => 'customfield'], function () {
    Route::resource('customfields', 'CustomfieldsController');
    //For Datatable
    Route::post('customfields/get', 'CustomfieldsTableController')->name('customfields.get');
});
Route::group(['namespace' => 'deptor'], function () {
    Route::resource('deptors', 'DebtorsController');
    //For Datatable
    //Route::post('deptors/get', 'DebtorsTableController')->name('deptors.get');
});
Route::group(['namespace' => 'department'], function () {
    Route::resource('departments', 'DepartmentsController');
    //For Datatable
    Route::post('departments/get', 'DepartmentsTableController')->name('departments.get');
});
Route::group(['namespace' => 'jobtitle'], function () {
    Route::post('jobtitles/select', 'JobTitleController@select')->name('jobtitles.select');
    Route::resource('jobtitles', 'JobTitleController');
    //For Datatable
    Route::post('jobtitles/get', 'JobTitleTableController')->name('jobtitles.get');
});

Route::group(['namespace' => 'fault'], function () {
    Route::post('faults/select', 'FaultController@select')->name('faults.select');
    Route::resource('faults', 'FaultController');
    //For Datatable
    Route::post('faults/get', 'FaultTableController')->name('faults.get');
});
Route::group(['namespace' => 'deduction'], function () {
    Route::post('deductions/select', 'DeductionController@select')->name('deductions.select');
    Route::resource('deductions', 'DeductionController');
    //For Datatable
    Route::post('deductions/get', 'DeductionTableController')->name('deductions.get');
});

Route::group(['namespace' => 'deptor'], function () {
    Route::resource('deptors', 'DeptorsController');
    //For Datatable
    Route::post('deptors/get', 'DeptorsTableController')->name('deptors.get');
});

Route::group(['namespace' => 'deptor'], function () {
    Route::resource('deptors', 'DeptorsController');
    //For Datatable
    Route::post('deptors/get', 'DeptorsTableController')->name('deptors.get');
});

Route::group(['namespace' => 'employeesalary'], function () {
    Route::resource('employeesalaries', 'EmployeeSalariesController');
    //For Datatable
    Route::post('salaries/get', 'SalariesTableController')->name('salaries.get');
    Route::post('employeesalaries/get', 'EmployeeSalariesTableController')->name('employeesalaries.get');
});

Route::group(['namespace' => 'queuerequisition'], function () {
    Route::post('queuerequisitions/status', 'QueueRequisitionController@status')->name('queuerequisitions.status');
    Route::post('queuerequisitions/goods', 'QueueRequisitionController@goods')->name('queuerequisitions.goods');
    Route::post('queuerequisitions/select_queuerequisition', 'QueueRequisitionController@select_queuerequisition')->name('queuerequisitions.select_queuerequisition');
    //update_description
    Route::post('queuerequisitions/update_description', 'QueueRequisitionController@update_description')->name('queuerequisitions.update_description');
    Route::post('queuerequisitions/select', 'QueueRequisitionController@select')->name('queuerequisitions.select');
    Route::resource('queuerequisitions', 'QueueRequisitionController');
    //For Datatable
    Route::post('queuerequisitions/get', 'QueueRequisitionTableController')->name('queuerequisitions.get');
    //Route::post('que/get', 'queTableController')->name('que.get');
});

Route::group(['namespace' => 'equipment'], function () {
    Route::resource('equipments', 'EquipmentsController');
    Route::post('equipments/equipment_load', 'EquipmentsController@equipment_load')->name('equipments.equipment_load');
    Route::post('equipments/search/{id}', 'EquipmentsController@equipment_search')->name('equipments.equipment_search');
    Route::post('equipments/attach', 'EquipmentsController@attach')->name('equipments.attach');
    Route::post('equipments/dettach', 'EquipmentsController@dettach')->name('equipments.dettach');

    //For Datatable
    Route::post('equipments/get', 'EquipmentsTableController')->name('equipments.get');
});

Route::group(['namespace' => 'equipmentcategory'], function () {
    Route::resource('equipmentcategories', 'EquipmentCategoriesController');
    //For Datatable
    Route::post('equipmentcategories/get', 'EquipmentCategoriesTableController')->name('equipmentcategories.get');
});
Route::group(['namespace' => 'event'], function () {
    Route::get('events/load_events', 'EventsController@load_events')->name('events.load_events');
    Route::post('events/update_event', 'EventsController@update_event')->name('events.update_event');
    Route::post('events/delete_event', 'EventsController@delete_event')->name('events.delete_event');

    //For Datatable
    Route::post('events/get', 'EventsTableController')->name('events.get');
    Route::resource('events', 'EventsController');
});

Route::group(['namespace' => 'djc'], function () {
    Route::resource('djcs', 'DjcsController');
    Route::get('ssr/default-inputs', 'DjcsController@getSsrDefaultInputs')->name('djcs-default-inputs');

    //For Datatable
    Route::post('djcs/get', 'DjcsTableController')->name('djcs.get');
});

Route::group(['namespace' => 'rjc'], function () {
    Route::post('rjcs/project_extra_details', 'RjcsController@project_extra_details')->name('rjcs.project_extra_details');
    Route::resource('rjcs', 'RjcsController');
    //For Datatable
    Route::post('rjcs/get', 'RjcsTableController')->name('rjcs.get');
});



Route::group(['namespace' => 'jobschedule'], function () {
    Route::resource('jobschedules', 'JobschedulesController');

    Route::post('products/stock_transfer', 'ProductsController@stock_transfer')->name('products.stock_transfer');
    //For Datatable
    Route::post('jobschedules/get', 'JobschedulesTableController')->name('jobschedules.get');
});

Route::group(['namespace' => 'lead'], function () {
    Route::patch('leads/update_status/{lead}', 'LeadsController@update_status')->name('leads.update_status');
    Route::patch('leads/update_reminder/{lead}', 'LeadsController@update_reminder')->name('leads.update_reminder');
    Route::post('leads/lead_search', 'LeadsController@lead_search')->name('leads.lead_search');
    Route::resource('leads', 'LeadsController');
    Route::resource('lead-sources', 'LeadSourceController');

    //For Datatable
    Route::post('leads/get', 'LeadsTableController')->name('leads.get');
});
//Prospects
Route::group(['namespace' => 'prospect'], function () {
    Route::patch('prospects/update_status/{prospect}', 'ProspectsController@update_status')->name('prospects.update_status');
    Route::resource('prospects', 'ProspectsController');

    //For Datatable
    Route::post('prospects/get', 'ProspectsTableController')->name('prospects.get');
    
    Route::post('prospects/followup', 'ProspectsController@followup')->name('prospects.followup');
    Route::post('prospects/fetchprospect', 'ProspectsController@fetchprospect')->name('prospects.fetchprospect');
});
//ProspectsCallResolved
Route::group(['namespace' => 'prospectcallresolved'], function () {
    Route::patch('prospectcallresolves/update_status/{prospect}', 'ProspectsCallResolvedController@update_status')->name('prospectcallresolves.update_status');
    Route::resource('prospectcallresolves', 'ProspectsCallResolvedController');
    Route::post('prospectcallresolves/notpicked','ProspectsCallResolvedController@notpicked')->name('prospectcallresolves.notpicked');
    Route::post('prospectcallresolves/pickedbusy','ProspectsCallResolvedController@pickedbusy')->name('prospectcallresolves.pickedbusy');
    Route::post('prospectcallresolves/notavailable','ProspectsCallResolvedController@notavailable')->name('prospectcallresolves.notavailable');
    Route::resource('prospectscallresolved', 'ProspectsCallResolvedController');
    //For Datatable
    Route::post('prospectcallresolves/get', 'ProspectsCallResolvedTableController')->name('prospectcallresolves.get');
    Route::post('prospectcallresolves/followup', 'ProspectsCallResolvedController@followup')->name('prospectcallresolves.followup');
    Route::post('prospectcallresolves/fetchprospectrecord', 'ProspectsCallResolvedController@fetchprospectrecord')->name('prospectcallresolves.fetchprospectrecord');
});

//CallList
Route::group(['namespace' => 'calllist'], function () {
    
    Route::get('calllists/mytoday', 'CallListController@mytoday')->name('calllists.mytoday');
    Route::get('calllists/allocationdays/{id}', 'CallListController@allocationdays')->name('calllists.allocationdays');
    Route::patch('calllists/update_status/{calllist}', 'CallListController@update_status')->name('calllists.update_status');
    Route::resource('calllists', 'CallListController');

    //For Datatable
    
    Route::post('calllists/get', 'CallListTableController')->name('calllists.get');
   
    Route::post('calllists/mytoday', 'MyTodayCallListTableController')->name('calllists.fetchtodaycalls');
    Route::post('calllists/prospectscalllist', 'MyTodayCallListTableController')->name('calllists.prospectcalllist');
    Route::post('calllists/prospectviacalllist', 'CallListController@prospectviacalllist')->name('calllists.prospectviacalllist');
    Route::post('calllists/followup', 'CallListController@followup')->name('calllists.followup');
});

//Remarks
Route::group(['namespace' => 'remark'], function () {
    Route::patch('remarks/update_status/{remark}', 'ProspectsController@update_status')->name('remarks.update_status');
    Route::resource('remarks', 'RemarksController');

    //For Datatable
    // Route::post('remarks/get', 'RemarksTableController')->name('remarks.get');

});
Route::group(['namespace' => 'lender'], function () {
    Route::resource('lenders', 'LendersController');

    //For Datatable
    Route::post('lenders/get', 'LendersTableController')->name('lenders.get');
});

Route::group(['namespace' => 'loan'], function () {
    Route::get('loans/lender_loans', 'LoansController@lender_loans')->name('loans.lender_loans');
    Route::post('loans/lenders', 'LoansController@lenders')->name('loans.lenders');
    Route::get('loans/pay_loans', 'LoansController@pay_loans')->name('loans.pay_loans');
    Route::post('loans/store_loans', 'LoansController@store_loans')->name('loans.store_loans');
    Route::get('loans/approve/{loan}', 'LoansController@approve_loan')->name('loans.approve_loan');
    Route::resource('loans', 'LoansController');
    //For Datatable
    Route::post('loans/get', 'LoansTableController')->name('loans.get');
});

Route::group(['namespace' => 'journal'], function () {
    Route::post('journals/journal_accounts', 'JournalsController@journal_accounts')->name('journals.journal_accounts');
    Route::resource('journals', 'JournalsController');
    //For Datatable
    Route::post('journals/get', 'JournalsTableController')->name('journals.get');
});

Route::group(['namespace' => 'reconciliation'], function () {
    Route::post('reconciliations/account_items', 'ReconciliationsController@account_items')->name('reconciliations.account_items');
    Route::resource('reconciliations', 'ReconciliationsController');
    //For Datatable
    Route::post('reconciliations/get', 'ReconciliationsTableController')->name('reconciliations.get');
});


Route::group(['namespace' => 'makepayment'], function () {
    Route::resource('makepayments', 'MakepaymentsController');

    //Route::post('purchases/customer_load', 'PurchasesController@customer_load')->name('purchases.customer_load');

    //For Datatable
    Route::get('makepayment/single_payment/{tr_id}', 'MakepaymentsController@single_payment')->name('makepayment.single_payment');
    Route::get('makepayment/receive_single_payment/{tr_id}', 'MakepaymentsController@receive_single_payment')->name('makepayment.receive_single_payment');
});



Route::group(['namespace' => 'misc'], function () {
    Route::resource('miscs', 'MiscsController');
    //For Datatable
    Route::post('miscs/get', 'MiscsTableController')->name('miscs.get');
});
Route::group(['namespace' => 'note'], function () {
    Route::resource('notes', 'NotesController');
    //For Datatable
    Route::post('notes/get', 'NotesTableController')->name('notes.get');
});


Route::group(['namespace' => 'order'], function () {
    Route::resource('orders', 'OrdersController');
    //For Datatable
    Route::post('orders/get', 'OrdersTableController')->name('orders.get');
});
Route::group(['namespace' => 'openingbalance'], function () {
    Route::resource('openingbalances', 'OpeningbalancesController');
    //For Datatable
    //Route::post('productstocktransfers/get', 'ProductstocktransfersTableController')->name('productstocktransfers.get');
});

Route::group(['namespace' => 'prefix'], function () {
    Route::resource('prefixes', 'PrefixesController');
    //For Datatable
    Route::post('prefixes/get', 'PrefixesTableController')->name('prefixes.get');
});
Route::group(['namespace' => 'pricegroup'], function () {
    Route::resource('pricegroups', 'PricegroupsController');
    //For Datatable
    Route::post('pricegroups/get', 'PricegroupsTableController')->name('pricegroups.get');
});

Route::group(['namespace' => 'client_product'], function () {
    Route::post('client_products/store_code', 'ClientProductsController@store_code')->name('client_products.store_code');
    Route::resource('client_products', 'ClientProductsController');
    //For Datatable
    Route::post('client_products/get', 'ClientProductsTableController')->name('client_products.get');
});

Route::group(['namespace' => 'pricelistSupplier'], function () {
    Route::get('pricelistsSupplier/list', 'PriceListsController@list')->name('pricelistsSupplier.list');
    Route::resource('pricelistsSupplier', 'PriceListsController');
    //For Datatable
    Route::post('pricelists/get', 'PriceListTableController')->name('pricelistsSupplier.get');
    Route::post('pricelists/gets', 'SupplierPriceListTableController')->name('pricelistsSupplier.gets');
});

Route::group(['namespace' => 'productcategory'], function () {
    Route::get('productcategories/search_code/{code}', 'ProductcategoriesController@search_code')->name('productcategories.search_code');
    Route::resource('productcategories', 'ProductcategoriesController');
    //For Datatable
    Route::post('productcategories/get', 'ProductcategoriesTableController')->name('productcategories.get');
});
Route::group(['namespace' => 'projectstocktransfer'], function () {
    Route::resource('projectstocktransfers', 'ProjectstocktransfersController');
    //For Datatable
    Route::post('projectstocktransfers/get', 'ProjectstocktransfersTableController')->name('projectstocktransfers.get');
});

Route::group(['namespace' => 'lpo'], function () {
    Route::post('lpo/update_lpo', 'LpoController@update_lpo')->name('lpo.update_lpo');
    Route::get('lpo/delete_lpo/{id}', 'LpoController@delete_lpo')->name('lpo.delete_lpo');

    Route::resource('lpo', 'LpoController');
    // for dataTable
    Route::post('lpo/get', 'LpoTableController')->name('lpo.get');
});

Route::group(['namespace' => 'productvariable'], function () {
    Route::resource('productvariables', 'ProductvariablesController');
    //For Datatable
    Route::post('productvariables/get', 'ProductvariablesTableController')->name('productvariables.get');
});
Route::group(['namespace' => 'purchase'], function () {
    Route::resource('purchases', 'PurchasesController');
    Route::post('purchases/customer_load', 'PurchasesController@customer_load')->name('purchases.customer_load');
    Route::post('purchases/quote', 'PurchasesController@quote_product_search')->name('purchase.quote_purchase_search');


    //For Datatable
    Route::post('purchases/get', 'PurchasesTableController')->name('purchases.get');
});

Route::group(['namespace' => 'PurchaseClass'], function () {

    Route::resource('purchase-classes', 'PurchaseClassController');
    Route::get('purchase-class/get-reports', 'PurchaseClassController@reportIndex')->name('purchase_classes.get-reports');
    Route::post('purchase-class/{id}/get-purchases-data', 'PurchaseClassController@getPurchasesData')->name('purchase_classes.get-purchases-data');
    Route::post('purchase-class/{id}/get-purchase-orders-data', 'PurchaseClassController@getPurchaseOrdersData')->name('purchase_classes.get-purchase-orders-data');

});


Route::group(['namespace' => 'projectequipment'], function () {
    Route::resource('projectequipments', 'ProjectequipmentsController');
    Route::post('projectequipments/write_job_card', 'ProjectequipmentsController@write_job_card')->name('projectequipments.write_job_card');
    //For Datatable
    Route::post('projectequipments/get', 'ProjectequipmentsTableController')->name('projectequipments.get');
});
Route::group(['namespace' => 'quote'], function () {
    Route::post('quotes/convert', 'QuotesController@convert')->name('quotes.convert');
    Route::post('quotes/approve_quote/{quote}', 'QuotesController@approve_quote')->name('quotes.approve_quote');

    Route::post('quotes/close_quote/{quote}', 'QuotesController@close_quote')->name('quotes.close_quote');
    Route::post('quotes/storeverified', 'QuotesController@storeverified')->name('quotes.storeverified');
    Route::get('quotes/customer_quotes', 'QuotesController@customer_quotes')->name('quotes.customer_quotes');
    Route::get('quotes/verify/{quote}', 'QuotesController@verify_quote')->name('quotes.verify');
    Route::post('quotes/verified_jcs/{id}', 'QuotesController@fetch_verified_jcs')->name('quotes.fetch_verified_jcs');
    Route::get('quotes/get_verify', 'QuotesController@get_verify_quote')->name('quotes.get_verify_quote');
    Route::get('quotes/turn_around', 'QuotesController@turn_around')->name('quotes.turn_around');

    // should be delete methods
    Route::get('quotes/delete_product/{id}', 'QuotesController@delete_product')->name('quotes.delete_product');
    Route::get('quotes/verified_item/{id}', 'QuotesController@delete_verified_item')->name('quotes.delete_verified_item');
    Route::get('quotes/verified_jcs/{id}', 'QuotesController@delete_verified_jcs')->name('quotes.delete_verified_jcs');
    Route::get('quotes/reset_verified/{id}', 'QuotesController@reset_verified')->name('quotes.reset_verified');

    Route::post('quotes/lpo', 'QuotesController@update_lpo')->name('quotes.lpo');
    Route::resource('quotes', 'QuotesController');
    //For Datatable
    Route::post('quotes/get_project', 'QuoteVerifyTableController')->name('quotes.get_project');
    Route::post('quotes/get', 'QuotesTableController')->name('quotes.get');
    Route::post('turn_around/search', 'TurnAroundTimeTableController')->name('turn_around.search');
});

Route::group(['namespace' => 'template_quote'], function () {
    Route::resource('template-quotes', 'TemplateQuoteController');
    Route::post('template-quotes/get', 'TemplateQuoteTableController')->name('template-quotes.get');
    Route::post('template-quote/details','TemplateQuoteController@getTemplateQuoteDetails')->name('template-quote-details');
   
});

Route::group(['namespace' => 'rfq'], function () {
    Route::resource('rfq', 'RfQController');
    Route::post('rfq/get', 'RfQTableController')->name('rfq.get');
    // Route::get('print_rfq/{id}', 'RfQController@printRfQ')->name('print-rfq');


    // Route::post('template-quote/details','TemplateQuoteController@getTemplateQuoteDetails')->name('template-quote-details');

});

// partial verification
Route::group(['namespace' => 'verification'], function () {
    Route::get('verifications/quote_index', 'VerificationsController@quote_index')->name('verifications.quote_index');
    Route::resource('verifications', 'VerificationsController');
    //For Datatable
    Route::post('verifications/get', 'VerificationsTableController')->name('verifications.get');
    Route::post('verifications/quotes/get', 'VerificationQuotesTableController')->name('verifications.get_quotes');
});

Route::group(['namespace' => 'region'], function () {
    Route::resource('regions', 'RegionsController');
    Route::post('regions/load_region', 'RegionsController@load_region')->name('regions.load_region');

    Route::post('regions/get', 'RegionsTableController')->name('regions.get');
});

Route::group(['namespace' => 'section'], function () {
    Route::resource('sections', 'SectionsController');

    Route::post('sections/get', 'SectionsTableController')->name('sections.get');
});

Route::group(['namespace' => 'spvariations'], function () {
    Route::resource('spvariations', 'SpVariablesController');
    //For Datatable
    Route::post('spvariations/get', 'SpVariablesControllerTableController')->name('spvariations.get');
});
Route::group(['namespace' => 'template'], function () {
    Route::resource('templates', 'TemplatesController');
    //For Datatable
    Route::post('templates/get', 'TemplatesTableController')->name('templates.get');
});
Route::group(['namespace' => 'term'], function () {
    Route::resource('terms', 'TermsController');
    //For Datatable
    Route::post('terms/get', 'TermsTableController')->name('terms.get');
});

Route::group(['namespace' => 'transactioncategory'], function () {
    Route::resource('transactioncategories', 'TransactioncategoriesController');
    //For Datatable
    Route::post('transactioncategories/get', 'TransactioncategoriesTableController')->name('transactioncategories.get');
});

Route::group(['namespace' => 'gateway'], function () {
    Route::resource('usergatewayentries', 'UsergatewayentriesController');
    //For Datatable
    Route::post('usergatewayentries/get', 'UsergatewayentriesTableController')->name('usergatewayentries.get');
});
Route::group(['namespace' => 'warehouse'], function () {
    Route::resource('warehouses', 'WarehousesController');
    //For Datatable
    Route::post('warehouses/get', 'WarehousesTableController')->name('warehouses.get');
});
Route::group(['namespace' => 'withholding'], function () {
    Route::resource('withholdings', 'WithholdingsController');
    //For Datatable
    Route::post('withholdings/get', 'WithholdingsTableController')->name('withholdings.get');
});

Route::group(['namespace' => 'creditnote'], function () {
    Route::get('creditnotes/print_creditnote/{creditnote}', 'CreditNotesController@print_creditnote')->name('creditnotes.print_creditnote');
    Route::resource('creditnotes', 'CreditNotesController');
    // for DataTable
    Route::post('creditnotes/get', 'CreditNotesTableController')->name('creditnotes.get');
});

Route::group(['namespace' => 'financial_year'], function () {

    Route::resource('financial_years', 'FinancialYearController');

});

Route::group(['namespace' => 'documentManager'], function () {

    Route::resource('document-manager', 'DocumentManagerController');

});



