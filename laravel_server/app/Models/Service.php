<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;

/**
 * Services are special Products with special features
 */
class Service extends AkeneoProduct
{
	protected $table = 'akeneo_products';

	protected static function booted() {
		static::addGlobalScope('services', fn (Builder $query_builder) => $query_builder->where('type', 'service'));
	}

	public function suppliers() {
		return $this->belongsToMany(Supplier::class, 'akeneo_product_supplier', 'akeneo_product_reference', 'supplier_id')->withTimestamps()->withPivot('expires_at');
	}

}
