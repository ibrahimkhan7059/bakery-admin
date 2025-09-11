<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CakeSize;
use App\Models\CakeOptionGroup;
use App\Models\CakeOption;
use Illuminate\Http\Request;

class CakeConfigController extends Controller
{
	public function index()
	{
		$sizes = CakeSize::orderBy('id')->get();
		$groups = CakeOptionGroup::with('options')->get();
		return view('admin.cake-config.index', compact('sizes','groups'));
	}

	public function storeSize(Request $request)
	{
		$validated = $request->validate([
			'name' => 'required|string|unique:cake_sizes,name',
			'base_price' => 'required|numeric|min:0',
		]);
		CakeSize::create($validated);
		return back()->with('success','Size created');
	}

	public function updateSize(Request $request, CakeSize $cakeSize)
	{
		$validated = $request->validate([
			'name' => 'required|string|unique:cake_sizes,name,'.$cakeSize->id,
			'base_price' => 'required|numeric|min:0',
		]);
		$cakeSize->update($validated);
		return back()->with('success','Size updated');
	}

	public function deleteSize(CakeSize $cakeSize)
	{
		$cakeSize->delete();
		return back()->with('success','Size deleted');
	}

	public function storeOption(Request $request)
	{
		$validated = $request->validate([
			'group_key' => 'required|in:flavor,filling,frosting',
			'name' => 'required|string',
			'price' => 'required|numeric|min:0',
		]);
		$group = CakeOptionGroup::firstOrCreate(
			['key' => $validated['group_key']],
			['label' => ucfirst($validated['group_key'])]
		);
		CakeOption::create([
			'cake_option_group_id' => $group->id,
			'name' => $validated['name'],
			'price' => $validated['price'],
		]);
		return back()->with('success','Option created');
	}

	public function updateOption(Request $request, CakeOption $cakeOption)
	{
		$validated = $request->validate([
			'name' => 'required|string',
			'price' => 'required|numeric|min:0',
		]);
		$cakeOption->update($validated);
		return back()->with('success','Option updated');
	}

	public function deleteOption(CakeOption $cakeOption)
	{
		$cakeOption->delete();
		return back()->with('success','Option deleted');
	}
} 