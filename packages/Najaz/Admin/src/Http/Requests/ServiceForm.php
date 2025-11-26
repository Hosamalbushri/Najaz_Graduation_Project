<?php

namespace Najaz\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceForm extends FormRequest
{
    /**
     * Determine if the service is authorized to make this request.
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
        $locale = core()->getRequestedLocaleCode();

        $rules = [
            'status'      => 'nullable|boolean',
            'image'       => 'nullable|string|max:2048',
            'sort_order'  => 'nullable|integer|min:0',
            'citizen_type_ids'   => 'nullable|array',
            'citizen_type_ids.*' => 'integer|exists:citizen_types,id',
        ];

        // Check if this is an update request
        $id = $this->route('id');

        if ($id) {
            // Update: require locale-specific fields
            $rules[$locale.'.name'] = 'required|string|max:255';
            $rules[$locale.'.description'] = 'nullable|string';
        } else {
            // Create: allow both locale-specific and direct input for backward compatibility
            $rules[$locale.'.name'] = 'required_without:name|string|max:255';
            $rules['name'] = 'required_without:'.$locale.'.name|string|max:255';
            $rules[$locale.'.description'] = 'nullable|string';
            $rules['description'] = 'nullable|string';
        }

        return $rules;
    }
}

