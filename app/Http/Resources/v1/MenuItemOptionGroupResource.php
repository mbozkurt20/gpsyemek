<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemOptionGroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'type'        => $this->type, // radio veya checkbox
            'is_required' => (bool)$this->is_required,
            'min_count'   => (int)$this->min_count,
            'max_count'   => (int)$this->max_count, // 0 gelirse front-end sınırsız kabul edecek
            'options'     => MenuItemOptionResource::collection($this->options),
        ];
    }
}
