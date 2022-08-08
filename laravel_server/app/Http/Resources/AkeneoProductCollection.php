<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AkeneoProductCollection extends ResourceCollection
{
	public function with($request) {
		return [
			'response_timing' => [
				'datetime' => now()
			]
		];
	}
}
