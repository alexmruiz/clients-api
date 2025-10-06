<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|max:150|unique:clients,email',
            'status'     => 'nullable|boolean',
            'legacy_id'  => 'nullable|integer',
        ];

        // En actualización,  mantener el mismo email
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $clientId = $this->route('client');
            $rules['email'] = 'required|email|max:150|unique:clients,email,' . $clientId;
        }

        return $rules;
    
    }
}
