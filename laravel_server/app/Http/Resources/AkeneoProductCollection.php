<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AkeneoProductCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
			'data' => $this->collection,
			'collection_custom_data' => 'collection_custom_value'
		];
    }

	public function with($request) {
		return [
			'meta' => [
				'collection_custom_with_key' => 'collection_custom_with_value'
			]
		];
	}
}
