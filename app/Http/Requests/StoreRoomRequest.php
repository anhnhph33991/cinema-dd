<?php

namespace App\Http\Requests;

use App\Traits\ErrorResponse;
use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    use ErrorResponse;
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
            'movie_id' => 'required|exists:movies,id',
            'name' => 'required|max:255|unique:rooms',
            'seat_structures' => 'json',
            'is_active' => 'nullable|in:0,1',
            'surcharge' => 'required|numeric|integer',
        ];
    }
}
