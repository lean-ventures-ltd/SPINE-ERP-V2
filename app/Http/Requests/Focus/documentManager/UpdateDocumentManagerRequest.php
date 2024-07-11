<?php

namespace App\Http\Requests\Focus\documentManager;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentManagerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {

        //TODO: Add Document Manager Create Permission
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'in:LICENSE,CONTRACT,CERTIFICATE,POLICY,AGREEMENT'],
            'description' => ['required', 'string'],
            'responsible' => ['required', 'exists:users,id'],
            'co_responsible' => ['required', 'exists:users,id'],
            'issuing_body' => ['required', 'string', 'max:255'],
            'issue_date' => ['required', 'date', 'before:renewal_date', 'before:expiry_date'],
            'cost_of_renewal' => ['required', 'numeric', 'min:0'],
            'renewal_date' => ['required', 'date', 'after:issue_date'],
            'expiry_date' => ['required', 'date', 'after:issue_date'],
            'alert_days_before' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:ACTIVE,EXPIRED,ARCHIVED'],
        ];    }
}
