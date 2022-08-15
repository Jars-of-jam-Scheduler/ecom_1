<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class AkeneoProduct extends Model
{
    use HasFactory;
	protected $primaryKey = 'reference';
	public $incrementing = FALSE;
	protected $keyType = 'string';

	protected static function booted() {
		static::addGlobalScope('price_defined', fn (Builder $query_builder) => $query_builder->whereNotNull('price_with_taxes')->where('price_with_taxes', '>', 0));  // Adding a protection to not display products that would be free otherwise
	}

	/**
	 * The attributes that aren't mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [];

	public function suppliers() {
		return $this->belongsToMany(Supplier::class)->withTimestamps()->withPivot('expires_at');
	}

	public function scopeCheap($query) {
		return $query->where('price_with_taxes', '<', 100);
	}

	public function scopeExpensive($query) {
		return $query->where('price_with_taxes', '>', 500);
	}

}
