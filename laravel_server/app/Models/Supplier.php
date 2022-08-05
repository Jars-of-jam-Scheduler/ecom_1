<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

	public function akeneoProducts() {
		return $this->belongsToMany(AkeneoProduct::class)->withTimestamps()->withPivot('expires_at');
	}
}
