<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AkeneoProduct extends Model
{
    use HasFactory;
	protected $primaryKey = 'reference';
	public $incrementing = FALSE;
	protected $keyType = 'string';

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
