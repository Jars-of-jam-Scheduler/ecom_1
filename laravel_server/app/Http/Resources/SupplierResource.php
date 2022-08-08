<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
			'name' => $this->name,
			'id' => $this->id,

			'akeneo_products' => AkeneoProductResource::collection($this->whenLoaded('akeneoProducts')),

			'created_at' => $this->whenPivotLoaded('akeneo_product_supplier', fn() => $this->pivot->created_at),
			'expires_at' => $this->whenPivotLoaded('akeneo_product_supplier', fn() => $this->pivot->expires_at)
		];
    }

}
