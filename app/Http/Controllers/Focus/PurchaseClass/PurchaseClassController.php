<?php

namespace App\Http\Controllers\Focus\PurchaseClass;

use App\Http\Controllers\Controller;
use App\Models\PurchaseClass\PurchaseClass;
use Illuminate\Http\Request;
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

            $purchaseClasses = PurchaseClass::all();

            return Datatables::of($purchaseClasses)
                ->addColumn('action', function ($model) {

                    $route = route('biller.purchase-classes.edit', $model->id);
                    $routeShow = route('biller.purchase-classes.show', $model->id);
                    $routeDelete = route('biller.purchase-classes.destroy', $model->id);

                    return '<a href="'.$routeShow.'" class="btn btn-secondary round mr-1">View</a>'
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
                ->rawColumns(['action'])
                ->make(true);

        }


        return view('focus.purchase_classes.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('focus.purchase_classes.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        $request->validate([
            'name' => 'required|unique:purchase_classes|max:255',
            // Add other validation rules as needed
        ]);

//        return $request;

        $p = new PurchaseClass();
        $p->name = $request->name;
        $p->ins = auth()->user()->ins;
        $p->save();

        return redirect()->route('biller.purchase-classes.index')
            ->with('success', 'Purchase class created successfully');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $purchaseClass = PurchaseClass::where('id', $id)->first();

        $purchases = $purchaseClass->purchases;
        $purchaseOrders = $purchaseClass->purchaseOrders;

        return view('focus.purchase_classes.show', compact('purchases', 'purchaseOrders'));
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

        return view('focus.purchase_classes.edit', compact('purchaseClass'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $purchaseClass = PurchaseClass::find($id);

        $request->validate([
            'name' => 'required|max:255|unique:purchase_classes,name,' . $purchaseClass->id,
            // Add other validation rules as needed
        ]);

        $purchaseClass = PurchaseClass::find($id);
        $purchaseClass->update($request->all());

        return redirect()->route('biller.purchase-classes.index')
            ->with('success', 'Purchase class updated successfully');
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

        $directsAndOrders = PurchaseClass::where('id', $id)->with('purchases', 'purchaseOrders')->first();

        if (count($directsAndOrders->purchases) > 0){
            return redirect()->route('biller.purchase-classes.index')
                ->with('flash_error', 'Delete Blocked as Purchase Class is Allocated to ' . count($directsAndOrders->purchases) . ' purchases');
        }
        else if (count($directsAndOrders->purchaseOrders) > 0){
            return redirect()->route('biller.purchase-classes.index')
                ->with('flash_error', 'Delete Blocked as Purchase Class is Allocated to ' . count($directsAndOrders->purchases) . ' purchase orders');
        }

        $purchaseClass->delete();

        return redirect()->route('biller.purchase-classes.index')
            ->with('success', 'Purchase class deleted successfully');
    }
}
