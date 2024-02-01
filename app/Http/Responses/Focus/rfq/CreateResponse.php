<?php

namespace App\Http\Responses\Focus\rfq;

use App\Models\additional\Additional;
use App\Models\pricegroup\Pricegroup;
use App\Models\rfq\RfQ;
use App\Models\supplier\Supplier;
use App\Models\term\Term;
use App\Models\warehouse\Warehouse;
use Illuminate\Contracts\Support\Responsable;

class CreateResponse implements Responsable
{
    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $ins = auth()->user()->ins;
        $prefixes = prefixesArray(['rfq'], $ins);
        $last_tid = RfQ::where('ins', $ins)->max('tid');
        $warehouses = Warehouse::all();
        $additionals = Additional::all();
        $pricegroups = Pricegroup::all();
        $supplier = Supplier::where('name', 'Walk-in')->first(['id', 'name']);
        $price_supplier = Supplier::whereHas('products')->get(['id', 'name']);
        // Purchase order
        $terms = Term::where('type', 5)->get();

        return compact('last_tid','warehouses', 'additionals', 'pricegroups','price_supplier','price_supplier', 'terms', 'prefixes');

        return view('focus.rfq.create', compact('last_tid','warehouses', 'additionals', 'pricegroups','price_supplier','price_supplier', 'terms', 'prefixes'));
    }
}
