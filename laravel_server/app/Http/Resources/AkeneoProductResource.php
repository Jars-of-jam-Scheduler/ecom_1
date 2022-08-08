<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AkeneoProductResource extends JsonResource
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
			'reference' => $this->reference, 
			'name' => $this->name,
			'description' => $this->whenNotNull($this->description),

			'suppliers' => SupplierResource::collection($this->whenLoaded('suppliers')),

			'created_at' => $this->whenPivotLoaded('akeneo_product_supplier', fn() => $this->pivot->created_at),
			'expires_at' => $this->whenPivotLoaded('akeneo_product_supplier', fn() => $this->pivot->expires_at),
		];
    }

	public function with($request) {
		return [
			'response_timing' => [
				'datetime' => now()
			]
		];
	}
}
