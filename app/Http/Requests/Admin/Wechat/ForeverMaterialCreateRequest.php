<?php

namespace App\Http\Requests\Admin\Wechat;

use Illuminate\Foundation\Http\FormRequest;

class ForeverMaterialCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $file = $this->input('file_field');
        return [
            //
            'file_field' => ['required', 'string'],
            $file => ['required', 'file']
        ];
    }
}