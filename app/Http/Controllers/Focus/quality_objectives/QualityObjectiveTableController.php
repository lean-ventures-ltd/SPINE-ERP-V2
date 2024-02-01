<?php

namespace App\Http\Controllers\Focus\quality_objectives;

use App\Http\Controllers\Controller;
use App\Repositories\Focus\quality_objectives\QualityObjectiveRepository;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class QualityObjectiveTableController extends Controller
{
    protected $qualityObjective;

    public function __construct(QualityObjectiveRepository $qualityObjective)
    {
        $this->qualityObjective = $qualityObjective;
    }

    public function __invoke()
    {
        $core = $this->qualityObjective->getForDataTable();

        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('name', function ($qualityObjective) {
                return $qualityObjective->name;
            })
            ->addColumn('actions', function ($qualityObjective) {
                return $qualityObjective->action_buttons;
            })
            ->make(true);
    }
}
