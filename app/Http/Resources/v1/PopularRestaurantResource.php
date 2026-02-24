<?php

namespace App\Http\Resources\v1;

use App\Helpers\RestaurantHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class PopularRestaurantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $isOpen = RestaurantHelper::getStatus($this->resource) === 'open';

        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => strip_tags($this->description),
            "lat" => $this->lat,
            "long" => $this->long,
            "address" => $this->address,
            "image" => $this->image,
            "avgRating" => $this->avgRatings['avgRating'],
            "avgRatingUser" => $this->avgRatings['countUser'],
            "isOpen" => $isOpen,
            "isOpenMessage" => $isOpen ? 'Şimdi Açık' : RestaurantHelper::getStatusMessage($this->resource),
        ];
    }
}
