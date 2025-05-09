<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\WelcomeCustomerNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class CustomerController extends Controller
{
    /**
     * Display a listing of the customers.
     */
    public function index()
    {
        // Only show users with 'customer' role
        $customers = User::where('role', 'customer')
                         ->latest()
                         ->paginate(10);
                         
        return view('admin.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z\s]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/'],
            'phone' => ['required', 'string', 'regex:/^(\+92|0)[0-9]{10}$/'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.regex' => 'Name can only contain letters and spaces.',
            'email.regex' => 'Please enter a valid email address.',
            'phone.regex' => 'Please enter a valid Pakistani phone number (e.g., +923001234567 or 03001234567).',
            'address.min' => 'Address must be at least 10 characters long.',
            'address.max' => 'Address cannot exceed 500 characters.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'role' => 'customer',
        ]);

        // Send welcome notification to the new customer
        $user->notify(new WelcomeCustomerNotification($user->name));

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully and welcome email sent.');
    }

    /**
     * Display the specified customer.
     */
    public function show(User $customer)
    {
        // Check if user is a customer
        if ($customer->role !== 'customer') {
            return redirect()->route('customers.index')
                ->with('error', 'Only customer accounts can be viewed here.');
        }
        
        return view('admin.customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(User $customer)
    {
        // Check if user is a customer
        if ($customer->role !== 'customer') {
            return redirect()->route('customers.index')
                ->with('error', 'Only customer accounts can be edited here.');
        }
        
        return view('admin.customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, User $customer)
    {
        // Check if user is a customer
        if ($customer->role !== 'customer') {
            return redirect()->route('customers.index')
                ->with('error', 'Only customer accounts can be updated here.');
        }
        
        $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[A-Za-z\s]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $customer->id, 'regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/'],
            'phone' => ['required', 'string', 'regex:/^(\+92|0)[0-9]{10}$/'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ], [
            'name.regex' => 'Name can only contain letters and spaces.',
            'email.regex' => 'Please enter a valid email address.',
            'phone.regex' => 'Please enter a valid Pakistani phone number (e.g., +923001234567 or 03001234567).',
            'address.min' => 'Address must be at least 10 characters long.',
            'address.max' => 'Address cannot exceed 500 characters.',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $customer->update($data);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(User $customer)
    {
        // Check if user is a customer
        if ($customer->role !== 'customer') {
            return redirect()->route('customers.index')
                ->with('error', 'Admin accounts cannot be deleted from here.');
        }
        
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
} 