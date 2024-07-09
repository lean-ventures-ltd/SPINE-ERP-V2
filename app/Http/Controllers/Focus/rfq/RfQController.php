<?php

namespace App\Http\Controllers\Focus\rfq;

use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\rfq\CreateRfQRequest;
use App\Http\Requests\Focus\rfq\DeleteRfQRequest;
use App\Http\Requests\Focus\rfq\EditRfQRequest;
use App\Http\Requests\Focus\rfq\ManageRfQRequest;
use App\Http\Requests\Focus\rfq\StoreRfQRequest;
use App\Models\additional\Additional;
use App\Models\pricegroup\Pricegroup;
use App\Models\rfq\RfQItem;
use App\Models\term\Term;
use App\Models\warehouse\Warehouse;
use App\Repositories\Focus\rfq\RfQRepository;
use DateTime;
use Illuminate\Http\Request;
use App\Http\Responses\ViewResponse;
use App\Models\supplier\Supplier;
use App\Http\Responses\Focus\rfq\CreateResponse;
use App\Http\Responses\Focus\rfq\EditResponse;
use App\Http\Responses\RedirectResponse;
use App\Models\rfq\RfQ;
use Illuminate\Support\Facades\Response;
use BillDetailsTrait;


class RfQController extends Controller
{
    protected $repository;

    public function __construct(RfQRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ManageRfQRequest $request)
    {
        $suppliers = Supplier::whereHas('purchase_orders')->get(['id', 'name']);

        return new ViewResponse('focus.rfq.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CreateRfQRequest $request)
    {

        return new CreateResponse('focus.rfq.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRfQRequest $request)
    {

        $request->validated();

        $rfq = $request->only(['tid', 'date', 'due_date', 'project_id', 'subject', 'tax',]);
        $rfqItems = $request->only([
            'item_id', 'description', 'uom', 'itemproject_id', 'qty', 'type', 'product_code', 'warehouse_id'
        ]);

        $rfq['ins'] = auth()->user()->ins;
        $rfq['user_id'] = auth()->user()->id;
        // modify and filter items without item_id
        $rfqItems = modify_array($rfqItems);
        $rfqItems = array_filter($rfqItems, function ($v) {
            return $v['item_id'];
        });

//        return compact('rfq', 'rfqItems');

        $newRfq = new RfQ();
        $newRfq->fill($rfq);
        $newRfq->date = (new DateTime($rfq['date']))->format('Y-m-d');
        $newRfq->due_date = (new DateTime($rfq['due_date']))->format('Y-m-d');

        $newRfq->save();

        $RFQITEMS = [];
        foreach ($rfqItems as $item){

            $newRfqItem = new RfQItem();

            $newRfqItem->fill($item);

            $newRfqItem->rfq_id = $newRfq->id;
            $newRfqItem->type = strtoupper($item['type']);

            if ($item['type'] === 'Stock') {

                $newRfqItem->product_id = $item['item_id'];
                $newRfqItem->project_id = $newRfq->project_id;
            }
            else if ($item['type'] === 'Expense') {

                $newRfqItem->expense_account_id = $item['item_id'];
                $newRfqItem->project_id = $item['itemproject_id'];
            }

            $newRfqItem->quantity = $item['qty'];

            $newRfqItem->save();
            array_push($RFQITEMS, $newRfqItem);
        }

        return new RedirectResponse(route('biller.rfq.index'), ['flash_success' => 'RFQ created successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ManageRfQRequest $request, $id)
    {
        $rfq = RfQ::find($id);
        return new ViewResponse('focus.rfq.view', compact('rfq'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(EditRfQRequest $request, $id)
    {
        return $rfq = RfQ::find($id);
        return new EditResponse($rfq);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EditRfQRequest $request, $id)
    {        
        $rfq = RfQ::find($id);

        $order = $request->only([
            'tid', 'date', 'due_date', 'term_id', 'project_id', 'subject', 'tax',
            'stock_subttl', 'stock_tax', 'stock_grandttl', 'expense_subttl', 'expense_tax', 'expense_grandttl',
            'asset_tax', 'asset_subttl', 'asset_grandttl', 'grandtax', 'grandttl', 'paidttl'
        ]);
        $order_items = $request->only([
            'item_id', 'description', 'uom', 'itemproject_id', 'qty', 'type', 'product_code', 'warehouse_id'
        ]);

        $order['ins'] = auth()->user()->ins;
        $order['user_id'] = auth()->user()->id;
        // modify and filter items without item_id
        $order_items = modify_array($order_items);
        $order_items = array_filter($order_items, function ($v) {
            return $v['item_id'];
        });

        $result = $this->repository->update($rfq, compact('order', 'order_items'));

        return new RedirectResponse(route('biller.rfq.index'), ['flash_success' => 'RFQ updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeleteRfQRequest $request, $id)
    {
        //
    }
    public function printRfQ($id)
    {
        $headers = [
            "Content-type" => "application/pdf",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        // $data = BillDetailsTrait::bill_details($id);
        $data = RfQ::find($id);

        $html = view('focus.bill.print_rfq', $data)->render();
        $pdf = new \Mpdf\Mpdf(config('pdf'));
        $pdf->WriteHTML($html);

        return Response::stream($pdf->Output('rfq.pdf', 'I'), 200, $headers);
    }
}
