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
}
