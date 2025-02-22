<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'id' => $this->id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->first_name . ' ' . $this->user->last_name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'address' => $this->user->address,
            ],
            'variant' => [
            'id' => $this->variant->id,
            'product_id' => $this->variant->product->id,
            'product_name' => $this->variant->product->name,
            'variant_weight' => $this->variant->weight,
            'variant_price' => $this->variant->price,
            ],
            'quantity' => $this->quantity,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'order_date' => $this->created_at->format('Y-m-d H:i'),
        ];
    }

}
