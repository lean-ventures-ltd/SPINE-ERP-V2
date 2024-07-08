<?php

namespace App\Http\Controllers\Focus\PurchaseClass;

use App\FinancialYear;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Models\PurchaseClass\PurchaseClass;
use DateTime;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Yajra\DataTables\Facades\DataTables;

class PurchaseClassController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if ($request->ajax()) {

            if (empty($request->financial_year)) $purchaseClasses = PurchaseClass::all();
            else $purchaseClasses = PurchaseClass::where('financial_year_id', $request->financial_year)->get();

            return Datatables::of($purchaseClasses)

                ->editColumn('financial_year_id', function ($model) {

                    $fId = $model->financial_year_id;

                    if(!empty($fId)) return FinancialYear::find($model->financial_year_id)->name;
                    else return '<b><i> Financial Year Not Set </i></b>';
                })
                ->editColumn('budget', function ($model) {

                    $budget = $model->budget;

                    if(!empty($budget)) return number_format($budget);
                    else return '<b><i> Budget Not Set </i></b>';
                })
                ->addColumn('action', function ($model) {

                    $route = route('biller.purchase-classes.edit', $model->id);
                    $routeShow = route('biller.purchase-classes.show', $model->id);
                    $routeDelete = route('biller.purchase-classes.destroy', $model->id);

                    return '<a href="'.$routeShow.'" class="btn btn-secondary round mr-1">Reports</a>'
                        .'<a href="'.$route.'" class="btn btn-secondary round mr-1">Edit</a>'
                        . '<a href="' .$routeDelete . '" 
                            class="btn btn-danger round" data-method="delete"
                            data-trans-button-cancel="' . trans('buttons.general.cancel') . '"
                            data-trans-button-confirm="' . trans('buttons.general.crud.delete') . '"
                            data-trans-title="' . trans('strings.backend.general.are_you_sure') . '" 
                            data-toggle="tooltip" 
                            data-placement="top" 
                            title="Delete"
                            >
                                <i  class="fa fa-trash"></i>
                            </a>';

                })
                ->rawColumns(['financial_year_id', 'budget','action'])
                ->make(true);

        }

        $financialYears = FinancialYear::all(['id', 'name']);


        return view('focus.purchase_classes.index', compact('financialYears'));
    }

    public function reportIndex(Request $request)
    {

        if ($request->ajax()) {

            $purchaseClasses = PurchaseClass::all();

            return Datatables::of($purchaseClasses)
                ->addColumn('action', function ($model) {

                    $routeShow = route('biller.purchase-classes.show', $model->id);

                    return '<a href="'.$routeShow.'" class="btn btn-secondary round mr-1">Reports</a>';
                })
                ->rawColumns(['action'])
                ->make(true);

        }


        return view('focus.purchase_classes.reports');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $financialYears = FinancialYear::all(['id', 'name']);

        return view('focus.purchase_classes.create',  compact('financialYears'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'budget'=> ['required', 'numeric'],
            'description' => ['required', 'string'],
            'financial_year_id' => ['required', 'integer'],
        ]);

        $purchaseClass = new PurchaseClass();
        $purchaseClass->fill($validated);

        $purchaseClass->save();

        return new RedirectResponse(route('biller.purchase-classes.index'), ['flash_success' => "Purchase class '" . $purchaseClass->name . "' saved successfully"]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $purchaseClass = PurchaseClass::find($id);

        $purchases = PurchaseClass::where('id', $id)
            ->with('purchases.project', 'purchases.budgetLine', 'purchases.supplier', 'purchases.creator')
            ->first();
        $purchaseOrders = PurchaseClass::where('id', $id)
            ->with('purchaseOrders.project', 'purchaseOrders.budgetLine', 'purchaseOrders.supplier', 'purchaseOrders.creator')
            ->first();


        return view('focus.purchase_classes.show', compact('purchaseClass', 'purchases', 'purchaseOrders'));
    }

    public function getPurchasesData($id)
    {

        $purchases = PurchaseClass::where('id', $id)
            ->with('purchases.project', 'purchases.budgetLine', 'purchases.supplier', 'purchases.creator')
            ->first();

        return Datatables::of($purchases->purchases)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('p_number', function ($purchases) {

                return '<a href="'.route('biller.purchases.show', $purchases->id).'"><b>'. 'DP-' . str_pad($purchases->tid, 4, '0', STR_PAD_LEFT) .'</b></a>';
            })
            ->addColumn('supplier', function ($purchases) {
                return $purchases->supplier->name;
            })
            ->addColumn('date', function ($purchases) {
                return (new DateTime($purchases->date))->format('dS M Y');
            })
            ->addColumn('project', function ($purchases) {

                if(empty($purchases->project)) return 'No Project Selected';
                else return '<a href="'.route('biller.projects.show', $purchases->project->id).'"><b>'. 'PRJ-' . str_pad($purchases->project->tid, 4, '0', STR_PAD_LEFT) .'</b></a>';
            })
            ->addColumn('budget_line', function ($purchases) {
                if (empty($purchases->budgetLine)) return 'Not Selected';
                else return $purchases->budgetLine->name;
            })
            ->addColumn('created_by', function ($purchases) {
                return $purchases->creator->first_name . ' ' . $purchases->creator->last_name;
            })
            ->addColumn('total', function ($purchases) {
                return number_format($purchases->grandttl, 2);
            })
            ->make(true);
    }

    public function getPurchaseOrdersData($id)
    {

        $purchaseOrders = PurchaseClass::where('id', $id)
            ->with('purchaseOrders.project', 'purchaseOrders.budgetLine', 'purchaseOrders.supplier', 'purchaseOrders.creator')
            ->first();

        try {

            return Datatables::of($purchaseOrders->purchaseOrders)
                ->escapeColumns(['id'])
                ->addIndexColumn()
                ->addColumn('po_number', function ($purchaseOrders) {

                    return '<a href="'.route('biller.purchaseorders.show', $purchaseOrders->id).'"><b>'. 'PO-' . str_pad($purchaseOrders->tid, 4, '0', STR_PAD_LEFT) .'</b></a>';
                })
                ->addColumn('supplier', function ($purchaseOrders) {
                    return $purchaseOrders->supplier->name;
                })
                ->addColumn('date', function ($purchaseOrders) {
                    return (new DateTime($purchaseOrders->date))->format('dS M Y');
                })
                ->addColumn('project', function ($purchaseOrders) {

                    if(empty($purchaseOrders->project)) return 'No Project Selected';
                    else return '<a href="'.route('biller.projects.show', $purchaseOrders->project->id).'"><b>'. 'PRJ-' . str_pad($purchaseOrders->project->tid, 4, '0', STR_PAD_LEFT) .'</b></a>';
                })
                ->addColumn('budget_line', function ($purchaseOrders) {
                    if (empty($purchaseOrders->budgetLine)) return 'Not Selected';
                    else return $purchaseOrders->budgetLine->name;
                })
                ->addColumn('created_by', function ($purchaseOrders) {
                    return $purchaseOrders->creator->first_name . ' ' . $purchaseOrders->creator->last_name;
                })
                ->addColumn('total', function ($purchaseOrders) {
                    return number_format($purchaseOrders->grandttl, 2);
                })
                ->make(true);

        } catch (\Exception $e){

            return errorHandler("Error: '" . $e->getMessage() . " | on File: " . $e->getFile() . "  | & Line: " . $e->getLine());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $purchaseClass = PurchaseClass::find($id);
        $financialYears = FinancialYear::all(['id', 'name']);

        return view('focus.purchase_classes.edit', compact('purchaseClass', 'financialYears'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return RedirectResponse
     * @throws \Exception
     */
    public function update(Request $request, $id)
    {

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'budget'=> ['required', 'numeric'],
            'description' => ['required', 'string'],
            'financial_year_id' => ['required', 'integer'],
        ]);

        $purchaseClass = PurchaseClass::find($id);
        $purchaseClass->fill($validated);
        $purchaseClass->save();

        return new RedirectResponse(route('biller.purchase-classes.index'), ['flash_success' => "Purchase class '" . $purchaseClass->name . "' updated successfully"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        $purchaseClass = PurchaseClass::find($id);

        $name = $purchaseClass->name;

        $directsAndOrders = PurchaseClass::where('id', $id)->with('purchases', 'purchaseOrders')->first();

        if (count($directsAndOrders->purchases) > 0){
            return redirect()->route('biller.purchase-classes.index')
                ->with('flash_error', 'Action Denied! Purchase Class is Allocated to ' . count($directsAndOrders->purchases) . ' purchases');
        }
        else if (count($directsAndOrders->purchaseOrders) > 0){
            return redirect()->route('biller.purchase-classes.index')
                ->with('flash_error', 'Action Denied! Purchase Class is Allocated to ' . count($directsAndOrders->purchases) . ' purchase orders');
        }

        $purchaseClass->delete();

        return new RedirectResponse(route('biller.purchase-classes.index'), ['flash_success' => "Purchase class '" . $name . "' deleted successfully"]);
    }
}
