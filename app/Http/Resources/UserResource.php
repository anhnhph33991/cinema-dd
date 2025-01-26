<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar ? Storage::url($this->avatar)  : null,
            'phone' => $this->phone ?? null,
            'address' => $this->address ?? null,
            'gender' => $this->gender ?? null,
            'birthday' => $this->birthday ?? null,
            'role' => $this->role ?? null,
        ];
    }
}
