<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'image'       => $this->getImageUrl(),
            'price' => $this->price ?? optional($this->variants->first())->price,
            'stock' => $this->variants->sum('stock') ?? 0,
            'category'    => new CategoryResource($this->whenLoaded('category')), // ✅ تضمين التصنيف
            'variants'    => VariantResource::collection($this->whenLoaded('variants')), // ✅ تضمين المتغيرات
            'options'     => OptionResource::collection($this->whenLoaded('options')), // ✅ تضمين الخيارات
            'created_at'  => $this->created_at->toDateTimeString(),
            'updated_at'  => $this->updated_at->toDateTimeString(),
        ];
    }
}
