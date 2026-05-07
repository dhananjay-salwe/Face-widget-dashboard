<?php

namespace App\Http\Requests;

// Note: Inherits from StoreWidgetRequest so we don't have to duplicate rules
class UpdateWidgetRequest extends StoreWidgetRequest
{
    public function authorize(): bool
    {
        return true; // Add auth logic if needed
    }
    
    // rules() and prepareForValidation() are inherited automatically!
}