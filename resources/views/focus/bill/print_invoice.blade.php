<html>
<head>
	<title>Invoice</title>
	<style>
		body {
			font-family: "Times New Roman", Times, serif;
			font-size: 10pt;
		}

		table {
			font-family: "Myriad Pro", "Myriad", "Liberation Sans", "Nimbus Sans L", "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size: 10pt;
		}

		table thead td {
			background-color: #BAD2FA;
			text-align: center;
			border: 0.1mm solid black;
			font-variant: small-caps;
		}

		td {
			vertical-align: top;
		}

		.bullets {
			width: 8px;
		}

		.items {
			border-bottom: 0.1mm solid black;
			font-size: 10pt;
			border-collapse: collapse;
			width: 100%;
			font-family: sans-serif;
		}

		.items td {
			border-left: 0.1mm solid black;
			border-right: 0.1mm solid black;
		}

		.align-r {
			text-align: right;
		}

		.align-c {
			text-align: center;
		}

		.bd {
			border: 1px solid black;
		}

		.bd-t {
			border-top: 1px solid
		}

		.ref {
			width: 100%;
			font-family: serif;
			font-size: 10pt;
			border-collapse: collapse;
		}

		.ref tr td {
			border: 0.1mm solid #888888;
		}

		.ref tr:nth-child(2) td {
			width: 50%;
		}

		.customer-dt {
			width: 100%;
			font-family: serif;
			font-size: 10pt;
		}

		.customer-dt tr td:nth-child(1) {
			border: 0.1mm solid #888888;
		}

		.customer-dt tr td:nth-child(3) {
			border: 0.1mm solid #888888;
		}

		.customer-dt-title {
			font-size: 7pt;
			color: #555555;
			font-family: sans;
		}

		.doc-title-td {
			text-align: center;
			width: 100%;
		}

		.doc-title {
			font-size: 15pt;
			color: #0f4d9b;
		}

		.doc-table {
			font-size: 10pt;
			margin-top: 5px;
			width: 100%;
		}

		.header-table {
			width: 100%;
			border-bottom: 0.8mm solid #0f4d9b;
		}

		.header-table tr td:first-child {
			color: #0f4d9b;
			font-size: 9pt;
			width: 100%;
		}

		.address {
			color: #0f4d9b;
			font-size: 10pt;
			width: 40%;
			text-align: right;
		}

		.header-table-text {
			color: #0f4d9b;
			font-size: 9pt;
			margin: 0;
		}

		.header-table-child {
			color: #0f4d9b;
			font-size: 8pt;
		}

		.header-table-child tr:nth-child(2) td {
			font-size: 9pt;
			padding-left: 50px;
		}

		.footer {
			font-size: 9pt;
			text-align: center;
		}
	</style>
</head>
<body>
	<htmlpagefooter name="myfooter">
		<div class="footer">
			Page {PAGENO} of {nb}
		</div>
	</htmlpagefooter>
	<sethtmlpagefooter name="myfooter" value="on" />
		@php
			$dir_sep = DIRECTORY_SEPARATOR;
		@endphp
	<table class="header-table">
		<tr>
			<td>
				{{-- {{ Storage::path("public{$dir_sep}img{$dir_sep}company{$dir_sep}{$company->logo}") }} --}}
				<img src="{{ Storage::disk('public')->url('app/public/img/company/' . $company->logo) }}" style="object-fit:contain" width="100%" />
			</td>
		</tr>
	</table>
	<table class="doc-table">
		<tr>
			<td class="doc-title-td">
				<span class='doc-title'>
					<b>INVOICE</b>
				</span>
			</td>
		</tr>
	</table><br>
	<table class="customer-dt" cellpadding="10">
		<tr>
			<td width="50%">
				<span class="customer-dt-title">CUSTOMER DETAILS:</span><br><br>
				@if ($resource->customer)
					<b>Client Name :</b> {{ $resource->customer->company }}<br>
					<b>Client Tax Pin : </b>{{ $resource->customer->taxid }}<br>
					<b>Address :</b> {{ $resource->customer->address }}<br>
					<b>Email :</b> {{ $resource->customer->email }}<br>
					<b>Cell :</b> {{ $resource->customer->phone }}<br>
				@else
					@php
						$customer = '';
						$lead = '';
						$quote = isset($resource->products->first()->quote)? $resource->products->first()->quote : '';
						if ($quote && $quote->customer) $customer = $quote->customer;
						elseif ($quote && $quote->lead) $lead = $quote->lead;
					@endphp
					@if ($customer)
						<b>Client Name</b> {{ $customer->company }}<br>
						<b>Client Tax Pin : </b>{{ $customer->taxid }}<br>
						<b>Address :</b> {{ $customer->address }}<br>
						<b>Email :</b> {{ $customer->email }}<br>
						<b>Cell :</b> {{ $customer->phone }}<br>
					@elseif ($lead)
						<b>Client Name</b> {{ $lead->client_name }}<br>
						<b>Client Tax Pin : </b>{{ '' }}<br>
						<b>Address :</b> {{ $lead->client_address }}<br>
						<b>Email :</b> {{ $lead->client_email }}<br>
						<b>Cell :</b> {{ $lead->client_contact }}<br>
					@endif
				@endif 
			</td>
			<td width="5%">&nbsp;</td>
			<td width="45%">
				<span class="customer-dt-title">REFERENCE DETAILS:</span><br><br>
				@if (@$resource->cu_invoice_no)
    				<b>CU Invoice No :</b> {{ $resource->cu_invoice_no }}<br>
				@endif
				<b>Invoice No :</b> {{ gen4tid('', $resource->tid) }}<br>
				<b>Date :</b> {{ dateFormat($resource->invoicedate, 'd-M-Y') }}<br>
				<b>Overdue after :</b> {{ $resource->validity ? $resource->validity . ' days' : 'On Receipt' }}<br>
				<b>KRA Pin :</b> {{ $company->taxid }}<br>
				@php
					$currencyName = \App\Models\currency\Currency::where('id', $resource->currency_id)->first()->code;
				@endphp
				<b>Currency :</b> {{ $currencyName }}<br>
				@php
					if ($resource->etr_url) {
						parse_str(parse_url($resource->etr_url, PHP_URL_QUERY), $params);
						$invoice_no = $params['invoiceNo'];
						echo '<b>ETR Invoice No :</b> ' . $invoice_no;
					}
				@endphp
			</td>
		</tr>
	</table><br>

	<table  class="ref" cellpadding="10">
		<tr><td colspan="2">Ref : <b>{{ $resource->notes }}</b></td></tr>
	</table><br>

	<table class="items" cellpadding="8">
		<thead>
			<tr>
				<td width="6%">No.</td>
				@php
					$product = $resource->products->first();
				@endphp
				@if ($product && $product->reference)
					<td width="24%">REFERENCE</td>
					<td width="24%">DESCRIPTION</td>
				@else
					<td colspan="2">DESCRIPTION</td>
				@endif
				
				<td width="8%">QTY</td>
				<td width="8%">UoM</td>
				<td width="14%">RATE</td>
				@php
					$code = '';
					$inv_product = 	$resource->products->first();
					if ($inv_product && isset($inv_product->quote->currency)) {
						$code = $inv_product->quote->currency->code;
					} 
				@endphp
				<td width="14%">AMOUNT {{ $code? "({$code})" : '' }}</td>
			</tr>
		</thead>
		<tbody>
			@foreach($resource->products as $k => $val)
				<tr>
					<td>{{ $val->numbering ?: $k+1 }}</td>

					@if ($product && $product->reference)
						<td>{{ $val->reference }}</td>
						<td>{{ $val->description }}</td>
					@else
						<td colspan="2">{{ $val->description }}</td>
					@endif
			
					<td class="align-c">{{ $val->product_qty > 0? +$val->product_qty : '' }}</td>
					<td class="align-c">{{ $val->unit }}</td>
					<td class="align-r">{{ $val->product_price > 0? numberFormat($val->product_price) : '' }}</td>
					<td class="align-r">{{ $val->product_qty > 0? numberFormat($val->product_qty * $val->product_price) : '' }}</td>
				</tr>
			@endforeach
			<!-- 20 dynamic empty rows -->
			@for ($i = count($resource->products); $i < 5; $i++)
				<tr>
					@if ($product && !$product->reference)
						@for($j = 0; $j < 6; $j++)
							@if ($j == 1)
								<td colspan="2"></td>
							@else
								<td></td>
							@endif
						@endfor
					@else
						@for($j = 0; $j < 7; $j++)
							<td></td>
						@endfor
					@endif
				</tr>
			@endfor
			<!--  -->
			<tr>
				<td colspan="3" class="bd-t" rowspan="4">
					@if ($resource->bank)
						<span class="customer-dt-title">BANK DETAILS:</span><br>
						<b>Account Name :</b> {{ $resource->bank->name }}<br>
						<b>Account Number :</b> {{ $resource->bank->number }}<br>
						<b>Bank :</b> {{ $resource->bank->bank }} &nbsp;&nbsp;<b>Branch :</b> {{ $resource->bank->branch }} <br>
						<b>Currency :</b> {{ $resource->currency? $resource->currency->code : 'Kenyan Shillings' }} &nbsp;&nbsp;<b>Swift Code :</b> {{ $resource->bank->code }} <br>
						{{ $resource->bank->paybill? "({$resource->bank->paybill})" : '' }}<br><br>
					@endif
					<b>Terms: </b> {{ $resource->term? $resource->term->title : '' }}<br>
				</td>
				<td colspan="2" class="bd-t" rowspan="3" style="border-left: hidden; padding-top: 1em;">
					{{-- Storage::path("public{$dir_sep}qr{$dir_sep}{$resource->etr_qrcode}") --}}
					{{-- <img src="{{ '' }}" style="object-fit:contain" width="10%"/> --}}
				</td>
				
				@php
                    $non_taxable_amount = $resource->products()->where('product_tax', 0)->sum(DB::raw('product_qty*product_price'));
                    $taxable_amount = $resource->products()->where('product_tax', '>', 0)->sum(DB::raw('product_qty*product_price'));
                @endphp
                <td class="bd align-r">Taxable Total:</td>
				<td class="bd align-r">{{ numberFormat($taxable_amount) }}</td>
			</tr>
			<tr>
				<td class="bd align-r">Sub Total:</td>
				@if ($resource->print_type == 'inclusive')
					<td class="bd align-r">{{ numberFormat($resource->total) }}</td>
				@else
					<td class="bd align-r">{{ numberFormat($resource->subtotal) }}</td>
				@endif
			</tr>
			<tr>
				@if ($resource->print_type == 'inclusive')
					<td class="align-r">VAT {{ $resource->tax_id }}%</td>
					<td class="align-r">{{ $resource->tax_id ? 'INCLUSIVE' : 'NONE' }}</td>
				@else
					<td class="align-r">Tax {{ $resource->tax_id ? $resource->tax_id . '%' : 'Off' }}</td>
					<td class="align-r">{{ numberFormat($resource->tax) }}</td>
				@endif
			</tr>
			<tr>
			    <td colspan="2"></td>
				<td class="bd align-r"><b>Grand Total:</b></td>
				<td class="bd align-r">{{ numberFormat($resource->total) }}</td>
			</tr>
		</tbody>
	</table>
</body>
</html>