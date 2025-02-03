<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MovieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $pathImage = Storage::exists($this->img_thumbnail)
            ? Storage::url($this->img_thumbnail)
            : 'https://os.alipayobjects.com/rmsportal/QBnOOoLaAfKPirc.png';
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category,
            'img_thumbnail' => $pathImage,
            'description' => $this->description,
            'duration' => $this->duration,
            'release_date' => $this->release_date,
            'end_date' => $this->end_date,
            'trailer_url' => $this->trailer_url,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
