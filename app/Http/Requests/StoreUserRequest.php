<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
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
        return [
            'name' => 'required|max:255',
            'email' => [
                'required',
                'email',
                'unique:users'
            ],
            'password' => 'required',
            'avatar' => 'nullable|image',
            'phone' => 'nullable|numeric',
            'address' => 'nullable|max:255',
            'gender' => 'nullable|in:0,1,2',
            'birthday' => 'nullable|date_format:Y-m-d|date',
            'role' => ['nullable', Rule::in(User::ROLE_MEMBER, User::ROLE_MEMBER)]
        ];
    }
}
