<?php

namespace App\Http\Controllers\Focus\health_and_safety;

use App\Http\Controllers\Controller;
use App\Models\account\Account;
use App\Models\additional\Additional;
use App\Models\customer\Customer;
use App\Models\hrm\Hrm;
use App\Models\misc\Misc;
use App\Models\project\Project;
use App\Models\health_and_safety\HealthAndSafetyTracking;
use Illuminate\Http\Request;
use App\Http\Responses\RedirectResponse;
use App\Exceptions\GeneralException;
use App\Http\Requests\Request as RequestsRequest;
use Illuminate\Support\Facades\DB;

class HealthAndSafetyTrackingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $month = date('n');
        $year = date('Y');
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $customers = Customer::whereHas('quotes')->get(['id', 'company']);
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $projects = Project::all();
        $additionals = Additional::all();

        $mics = Misc::all();
        $statuses = Misc::where('section', 2)->get();
        $tags = Misc::where('section', 1)->get();

        $employees = Hrm::all();

        return view('focus.health_and_safety.index', compact('days', 'additionals', 'customers', 'accounts', 'projects', 'mics', 'employees', 'statuses', 'tags'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customers = Customer::whereHas('quotes')->get(['id', 'company']);
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $projects = Project::all();
        $additionals = Additional::all();

        $mics = Misc::all();
        $statuses = Misc::where('section', 2)->get();
        $tags = Misc::where('section', 1)->get();

        $employees = Hrm::all();

        $clients = Customer::all();

        return view('focus.health_and_safety.create', compact('clients', 'additionals', 'customers', 'accounts', 'projects', 'mics', 'employees', 'statuses', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $input = $request->all();

        $input['employee'] = json_encode($request->employee);;
        $input['ins'] = auth()->user()->ins;
        $input['user_id'] = auth()->user()->id;


        try {
            $result = HealthAndSafetyTracking::create($input);
        } catch (\Throwable $th) {
            throw new GeneralException('Error adding issue.');
        }

        return new RedirectResponse(route('biller.health-and-safety.index'), ['flash_success' => 'Issue has been added successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = HealthAndSafetyTracking::find($id);
        $employees = json_decode($data['employee']);

        $k = [];
        foreach ($employees as $employee){
            $c = Hrm::where('id', $employee)->first();
            $d['a'] = $c->first_name.' '. $c->last_name;
            $k[] = $d;
        }
        // dd($k);
        return view('focus.health_and_safety.view', compact('data', 'k'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = HealthAndSafetyTracking::find($id);
        $customers = Customer::whereHas('quotes')->get(['id', 'company']);
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $projects = Project::all();
        $additionals = Additional::all();

        $mics = Misc::all();
        $statuses = Misc::where('section', 2)->get();
        $tags = Misc::where('section', 1)->get();

        $employees = Hrm::all();

        $clients = Customer::all();

        return view('focus.health_and_safety.edit', compact('data', 'clients', 'additionals', 'customers', 'accounts', 'projects', 'mics', 'employees', 'statuses', 'tags'));
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
        $data = HealthAndSafetyTracking::find($id);

        $input = $request->all();
        $input['employee'] = json_encode($request->employee);;
        $input['ins'] = auth()->user()->ins;
        $input['user_id'] = auth()->user()->id;


        try {
            $data->update($input);
        } catch (\Throwable $th) {
            throw new GeneralException('Error updating issue.');
        }

        return new RedirectResponse(route('biller.health-and-safety.index'), ['flash_success' => 'Issue has been updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = HealthAndSafetyTracking::find($id);
        $data->delete();
        return new RedirectResponse(route('biller.health-and-safety.index'), ['flash_success' => 'Issue has been deleted successfully']);
    }

    public function clientProjects(Request $request)
    {
        $projects = Project::where('customer_id', $request->customer_id)
            ->where('end_note', '!=', 'Closed')
            ->where('end_note', '!=', 'Completed')
            ->get();

        return response()->json($projects);
    }

    public function monthlySummary()
    {
        // TODO: Get number of days of current month
        $month = date('n');
        $year = date('Y');
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $thisMonth = [];
        // TODO: Loop through days
        for ($i = 1; $i <= $days; $i++) {
            $t['day'] = $i;
            $t['date'] = $year . '-' . $month . '-' . $i;
            $date = \DateTime::createFromFormat('Y-m-d', $t['date']);
            $date =  $date->format('Y-m-d');
            $t['date'] = $date;

            $data = DB::table('health_and_safety_tracking')
                ->where('date', $date)
                ->get()->toArray();

            if (empty($data)) {
                $t['color'] = 'green';
            } else {
                foreach ($data as $d){
                    if (in_array('lost-work-day', (array)$d)) {
                        $t['color'] = 'red';
                    } else {
                        $t['color'] = 'yellow';
                    }
                }
            }
            $thisMonth[]  = $t;
        }
        // dd($thisMonth);


        // dd($data);

        // $days = [];
        // foreach ($data as $d) {
        //     $color['day'] = date('d', strtotime($d->date));
        //     $color['date'] = $d->date;

        //     if ($d->status == 'lost-work-day') {
        //         $color['color'] = 'red';
        //     } elseif ($d->status == 'first-aid-case') {
        //         $color['color'] = 'yellow';
        //     } else {
        //         $color['color'] = 'green';
        //     }

        //     $days[] = $color;
        // }

        // array_multisort(array_column($days, 'day'), SORT_ASC, $days);

        // dd($days);



        $month = date('n');
        $year = date('Y');
        $days = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        $customers = Customer::whereHas('quotes')->get(['id', 'company']);
        $accounts = Account::where('account_type', 'Income')->get(['id', 'holder', 'number']);
        $projects = Project::all();
        $additionals = Additional::all();

        $mics = Misc::all();
        $statuses = Misc::where('section', 2)->get();
        $tags = Misc::where('section', 1)->get();

        $employees = Hrm::all();


        $firstFour = array_slice($thisMonth, 0, 4);
        $chunk1 = array_chunk($firstFour, 2);

        $secondSixteen = array_slice($thisMonth, 4, 16);
        $chunk2 = array_chunk($secondSixteen, 8);

        $thirdGroup = array_slice($thisMonth, 20, $days);
        $chunk3 = array_chunk($thirdGroup, 2);



        // return view('focus.tracking_sheets.health_and_safety.summary', compact('chunk1', 'thisMonth'));
        return view('focus.health_and_safety.summary', compact('chunk1','chunk2','chunk3', 'thisMonth','days', 'additionals', 'customers', 'accounts', 'projects', 'mics', 'employees', 'statuses', 'tags'));
    }

    public function dayIncidents(Request $request){
        $day = $request->day;
        $month = date('n');
        $year = date('Y');

        $t = $year . '-' . $month . '-' . $day;
        $date = \DateTime::createFromFormat('Y-m-d', $t);
        $date =  $date->format('Y-m-d');

        $data = DB::table('health_and_safety_tracking')
                ->where('date', $date)
                ->get();
            
        $daysData= [];
        foreach($data as $d){
            $e = json_decode($d->employee);
            $d->customer = Customer::where('id', $d->customer_id)->first();
            $d->project = Project::where('id', $d->project_id)->first();
            // $employees = [];
            // foreach($e as $f){
            //     $employees[] = Hrm::where('id', $f)->first();
            // }
            // $d->emp = $employees;
            
            $daysData[] = $d;
        }
        return response()->json([$daysData, $date]);
    }
}
