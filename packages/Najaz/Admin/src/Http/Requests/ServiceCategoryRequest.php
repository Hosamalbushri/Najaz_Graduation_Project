<?php

namespace Najaz\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $locale = core()->getRequestedLocaleCode();
        
        $id = $this->route('id') ?: ($this->id ?? null);

        return [
            $locale.'.name'        => ['required', 'string', 'max:255'],
            $locale.'.slug'        => [
                'required',
                'string',
                $id 
                    ? 'unique:service_category_translations,slug,'.$id.',service_category_id,locale,'.$locale
                    : 'unique:service_category_translations,slug,NULL,service_category_id,locale,'.$locale
            ],
            $locale.'.description' => ['nullable', 'string'],
            $locale.'.meta_title'  => ['nullable', 'string'],
            $locale.'.meta_keywords' => ['nullable', 'string'],
            $locale.'.meta_description' => ['nullable', 'string'],
            'position'             => ['nullable', 'integer'],
            'status'               => ['nullable', 'boolean'],
            'display_mode'         => ['nullable', 'string'],
            'parent_id'            => ['nullable', 'integer', 'exists:service_categories,id'],
        ];
    }

}

