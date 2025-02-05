<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */
namespace App\Http\Controllers\Focus\note;

use App\Models\note\Note;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\note\NoteRepository;
use App\Http\Requests\Focus\note\ManageNoteRequest;

/**
 * Class NotesTableController.
 */
class NotesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var NoteRepository
     */
    protected $note;

    /**
     * contructor to initialize repository object
     * @param NoteRepository $note ;
     */
    public function __construct(NoteRepository $note)
    {
        $this->note = $note;
    }

    /**
     * This method return the data of the model
     * @param ManageNoteRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
//        $core = collect();
//        $note_access = access()->allow('manage-note') || project_view(request('project_id', 0));
//        if ($note_access) $core = $this->note->getForDataTable();

        $notes = Note::where('project_id', request('project_id'))->get();

        return Datatables::of($notes)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('created_at', function ($note) {
                return Carbon::parse($note->created_at)->toDateString();
            })
            ->addColumn('user', function ($note) {
                return user_data($note->user_id)['first_name'];
            })
            ->addColumn('actions', function ($note) {
                return $note->action_buttons;
            })
            ->make(true);
    }
}
