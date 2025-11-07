<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(): View
    {
        return view('admin.settings.index');
    }

    /**
     * Update general settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'store_phone' => ['required', 'string', 'max:20'],
            'store_address' => ['required', 'string', 'max:500'],
            'min_order_amount' => ['required', 'numeric', 'min:0'],
            'advance_payment_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'store_name.required' => 'Bakery name is required.',
            'store_phone.required' => 'Contact phone is required.',
            'store_address.required' => 'Delivery area information is required.',
            'min_order_amount.required' => 'Minimum order amount is required.',
            'advance_payment_percentage.required' => 'Advance payment percentage is required.',
        ]);

        // Here you would typically save to database or config files
        // For now, we'll just return success message
        
        return redirect()->route('settings.index')->with('success', 'Bakery settings updated successfully!');
    }


} 