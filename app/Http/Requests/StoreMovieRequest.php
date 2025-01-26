<?php

namespace App\Http\Requests;

use App\Traits\ErrorResponse;
use Illuminate\Foundation\Http\FormRequest;

class StoreMovieRequest extends FormRequest
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
            'name' => 'required|max:255|unique:movies',
            'slug' => 'nullable|max:255',
            'category' => 'required|in:2D,3D,4D',
            'img_thumbnail' => 'required|image',
            'description' => 'nullable',
            'duration' => [
                'required',
                'integer',
                'numeric',
            ],
            'release_date' => 'date|date_format:Y-m-d',
            'end_date' => 'date|date_format:Y-m-d',
            'trailer_url' => 'required|url:http,https',
            'is_active' => 'nullable|in:0,1',
        ];
    }
}
