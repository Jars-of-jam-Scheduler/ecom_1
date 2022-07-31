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
			/*'suppliers' => AkeneoProductSupplierResource::collection($this->suppliers),
			'suppliers_relationship' => $this->whenPivotLoaded('akeneoProduct_productSupplier', function() {
				return $this->pivot->comments;
			}),*/
		];
    }
}
