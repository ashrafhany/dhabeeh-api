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
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'description' => $this->product->description,
                'image' => $this->product->image,
                'price' => $this->product->price,
                'category_id' => $this->product->category_id,
                'weight' => $this->product->weight,
            ],
            'quantity' => $this->quantity,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'order_date' => $this->created_at->format('Y-m-d H:i'),
        ];
    }

}
