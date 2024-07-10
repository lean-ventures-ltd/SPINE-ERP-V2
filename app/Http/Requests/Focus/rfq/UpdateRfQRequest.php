<?php

namespace App\Http\Requests\Focus\rfq;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRfQRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return access()->allow('edit-rfq');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'date' => ['required', 'date', 'before:due_date'],
            'due_date' => ['required', 'date', 'after:date']
        ];
    }
}
