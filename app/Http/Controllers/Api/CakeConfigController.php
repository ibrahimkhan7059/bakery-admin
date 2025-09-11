<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CakeSize;
use App\Models\CakeOptionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CakeConfigController extends Controller
{
	public function index()
	{
		$sizes = CakeSize::orderBy('id')->get(['id','name','base_price']);
		$groups = CakeOptionGroup::with(['options:id,cake_option_group_id,name,price'])
			->get(['id','key','label']);

		return response()->json([
			'sizes' => $sizes,
			'groups' => $groups,
		]);
	}
} 