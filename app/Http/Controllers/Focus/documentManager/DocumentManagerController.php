<?php

namespace App\Http\Controllers\Focus\documentManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\Focus\documentManager\StoreDocumentManagerRequest;
use App\Http\Requests\Focus\documentManager\UpdateDocumentManagerRequest;
use App\Models\documentManager\DocumentManager;
use App\Models\hrm\Hrm;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $employees = Hrm::select(['id', 'first_name', 'last_name'])
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                ];
            });

        $documentTypes = ['LICENSE', 'CONTRACT', 'CERTIFICATE', 'POLICY', 'AGREEMENT'];
        $documentStatuses = ['ACTIVE', 'ARCHIVED'];


        return view('focus.documentManager.create', compact('employees', 'documentTypes', 'documentStatuses'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDocumentManagerRequest $request)
    {

        $validated = $request->validated();

        try{
            DB::beginTransaction();

            $documentManager = new DocumentManager();
            $documentManager->fill($validated);
            $documentManager->save();

            DB::commit();
        }
        catch (Exception $exception){

            DB::rollBack();
            return [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
            return errorHandler('Error Updating Stock Issue', $exception);
        }

        return $documentManager;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $documentManager = DocumentManager::find($id);
        $employees = Hrm::select(['id', 'first_name', 'last_name'])
            ->get()
            ->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->first_name . ' ' . $emp->last_name,
                ];
            });

        $documentTypes = ['LICENSE', 'CONTRACT', 'CERTIFICATE', 'POLICY', 'AGREEMENT'];
        $documentStatuses = ['ACTIVE', 'ARCHIVED'];


        return view('focus.documentManager.create', compact('documentManager', 'employees', 'documentTypes', 'documentStatuses'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDocumentManagerRequest $request, $id)
    {

        $validated = $request->validated();

        try{
            DB::beginTransaction();

            $documentManager = DocumentManager::find($id);
            $documentManager->fill($validated);
            $documentManager->save();

            DB::commit();
        }
        catch (Exception $exception){

            DB::rollBack();
            return [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
            return errorHandler('Error Updating Stock Issue', $exception);
        }

        return $documentManager;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
