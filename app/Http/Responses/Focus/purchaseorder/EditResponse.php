<?php

namespace App\Http\Responses\Focus\purchaseorder;

use App\Models\additional\Additional;
use App\Models\pricegroup\Pricegroup;
use App\Models\PurchaseClass\PurchaseClass;
use App\Models\supplier\Supplier;
use App\Models\term\Term;
use App\Models\warehouse\Warehouse;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\purchaseorder\Purchaseorder
     */
    protected $purchaseorder;

    /**
     * @param App\Models\purchaseorder\Purchaseorder $purchaseorder
     */
    public function __construct($purchaseorder)
    {
        $this->purchaseorder = $purchaseorder;
    }

    /**
     * To Response
     *
     * @param \App\Http\Requests\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function toResponse($request)
    {
        $po = $this->purchaseorder;
        $prefixes = prefixesArray(['purchase_order'], $po->ins);

        $additionals = Additional::all();
        $pricegroups = Pricegroup::all();
        $warehouses = Warehouse::all();
        $supplier = Supplier::where('name', 'Walk-in')->first(['id', 'name']);
        $price_supplier = Supplier::whereHas('products')->get(['id', 'name']);
        $purchaseClasses = PurchaseClass::all();

        // Purchase order
        $terms = Term::where('type', 4)->get();
        
        // assign project name
        foreach ($po->products as $po_items) {
            if ($po_items->project){
                $quote_tid = !$po_items->project->quote ?: gen4tid('QT-', $po_items->project->quote->tid);
                $customer = !$po_items->project->customer ?: $po_items->project->customer->company;
                $branch = !$po_items->project->branch ?: $po_items->project->branch->name;
                $project_tid = gen4tid('PRJ-', $po_items->project->tid);
                $project = $po_items->project->name;
                $customer_branch = "{$customer}" .'-'. "{$branch}";
                // 
                $po_items['project_name'] = "[" . $quote_tid ."]"." - " . $customer_branch. " - ".$project_tid." - ".$project;
            }
        }

        return view('focus.purchaseorders.edit', compact('po', 'additionals','warehouses', 'pricegroups','price_supplier', 'terms', 'prefixes', 'purchaseClasses'));
    }
}