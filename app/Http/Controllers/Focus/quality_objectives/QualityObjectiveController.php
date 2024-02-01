<?php

namespace App\Http\Controllers\Focus\quality_objectives;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\quality_objectives\QualityObjectiveRepository;
use Illuminate\Http\Request;
use App\Http\Responses\RedirectResponse;
use App\Models\quality_objectives\QualityObjective;

class QualityObjectiveController extends Controller
{
    protected $repository;

    public function __construct(QualityObjectiveRepository $repository)
    {
        $this->repository = $repository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('focus.quality_objectives.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('focus.quality_objectives.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $input['ins'] = auth()->user()->ins;
        $input['user_id'] = auth()->user()->id;
        $this->repository->create($input);
        return new RedirectResponse(route('biller.quality-objectives.index'), ['flash_success' => 'Objective added successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $qualityObjective = QualityObjective::find($id);
        return view('focus.quality_objectives.view', compact('qualityObjective'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $qualityObjective = QualityObjective::find($id);
        return view('focus.quality_objectives.edit', compact('qualityObjective'));
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
        $qualityObjective = QualityObjective::find($id);
        $input = $request->all();
        $this->repository->update($qualityObjective, $input);
        return new RedirectResponse(route('biller.quality-objectives.index'), ['flash_success' => 'Objective updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $qualityObjective = QualityObjective::find($id);
        $qualityObjective->delete();
        return new RedirectResponse(route('biller.quality-objectives.index'), ['flash_success' => 'Objective deleted successfully']);
    }
}
