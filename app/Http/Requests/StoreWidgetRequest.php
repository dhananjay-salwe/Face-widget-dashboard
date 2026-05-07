<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWidgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'string', 'max:255'],
            'mode'               => ['required', 'in:floating,embedded'],
            'position'           => ['nullable', 'in:top-left,top-right,bottom-left,bottom-right,top-center,bottom-center'],
            'theme_color'        => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'api_limit'          => ['required', 'integer', 'min:1', 'max:1000000'],
            'allowed_domains'    => ['nullable', 'string', 'max:2000'],
            'allowed_pages'      => ['nullable', 'string', 'max:2000'],
            'welcome_title'      => ['nullable', 'string', 'max:255'],
            'welcome_message'    => ['nullable', 'string', 'max:1000'],
            'button_text'        => ['nullable', 'string', 'max:50'],
            'button_color'       => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active'          => ['required', 'boolean'],
            'show_start_button'  => ['required', 'boolean'], // true=button, false=auto-start
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_active'         => $this->boolean('is_active'),
            'show_start_button' => $this->boolean('show_start_button'),
        ]);
    }
}