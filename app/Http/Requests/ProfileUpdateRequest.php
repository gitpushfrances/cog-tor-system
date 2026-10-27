<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return \App\Http\Controllers\Admin\UserController::identityRules($this->user());
    }

    public function messages(): array
    {
        return \App\Http\Controllers\Admin\UserController::identityMessages();
    }
}
