<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CakeSize extends Model
{
	use HasFactory;

	protected $fillable = ['name', 'base_price'];
} 