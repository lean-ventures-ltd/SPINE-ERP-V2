<?php

namespace App\Http\Responses\Focus\project;

use App\Models\account\Account;
use App\Models\branch\Branch;
use App\Models\hrm\Hrm;
use App\Models\misc\Misc;
use Illuminate\Contracts\Support\Responsable;

class EditResponse implements Responsable
{
    /**
     * @var App\Models\project\Project
     */
    protected $project;

    /**
     * @param App\Models\project\Project $projects
     */
    public function __construct($project)
    {
        $this->project = $project;
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
        $mics = Misc::all();
        $statuses = Misc::where('section', 2)->get();
        $tags = Misc::where('section', 1)->get();
        $employees = Hrm::all();
        $project = $this->project;

        return view('focus.projects.edit',compact('mics','employees','project', 'statuses', 'tags'));
    }
}