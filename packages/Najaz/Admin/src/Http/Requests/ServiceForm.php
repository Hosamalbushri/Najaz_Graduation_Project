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

        // Check if this is an update request
        $id = $this->route('id');

        $rules = [
            'status'             => 'nullable|boolean',
            'images.files'       => 'nullable|array',
            'images.files.*'     => 'nullable|file|image|max:2048',
            'sort_order'         => 'nullable|integer|min:0',
            'citizen_type_ids'   => 'nullable|array',
            'citizen_type_ids.*' => 'integer|exists:citizen_types,id',
        ];

        // Service number validation
        if ($id) {
            // Update: unique but ignore current record
            $rules['service_number'] = 'required|string|unique:services,service_number,'.$id.'|regex:/^[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*$/';
        } else {
            // Create: must be unique
            $rules['service_number'] = 'required|string|unique:services,service_number|regex:/^[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*$/';
        }

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
