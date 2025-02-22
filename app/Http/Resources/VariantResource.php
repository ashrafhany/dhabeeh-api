<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VariantResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'     => $this->id,
            'weight' => $this->weight,
            'price'  => $this->price,
            'stock'  => $this->stock,
        ];
    }
}
