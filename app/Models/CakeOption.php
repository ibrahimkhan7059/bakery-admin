<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CakeOption extends Model
{
	use HasFactory;

	protected $fillable = ['cake_option_group_id', 'name', 'price'];

	public function group()
	{
		return $this->belongsTo(CakeOptionGroup::class, 'cake_option_group_id');
	}
} 