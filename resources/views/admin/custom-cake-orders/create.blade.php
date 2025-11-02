@extends('layouts.app')

@section('title', 'Create Custom Cake Order')

@section('content')
<style>
    /* Hide default dropdown arrow */
    .position-relative select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: none;
    }
</style>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4">
        <div></div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('custom-cake-orders.index') }}" class="btn btn-primary hover-lift">
                <i class="bi bi-arrow-left me-1"></i> Back to Orders
            </a>
        </div>
    </div>

    <!-- Multi-step Form Card -->
    <div class="card border-0 shadow-sm rounded-lg glass-card">
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h5><i class="bi bi-exclamation-triangle me-2"></i>Validation Errors:</h5>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Error:</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('custom-cake-orders.store') }}" method="POST" enctype="multipart/form-data" id="createOrderForm">
                @csrf
                
                <!-- Progress Steps -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="steps d-flex flex-wrap flex-sm-nowrap justify-content-between">
                            <div class="step active" data-step="1">
                                <div class="step-icon"><i class="bi bi-person"></i></div>
                                <div class="step-text">Customer Selection</div>
                            </div>
                            <div class="step" data-step="2">
                                <div class="step-icon"><i class="bi bi-cake2"></i></div>
                                <div class="step-text">Cake Specifications</div>
                            </div>
                            <div class="step" data-step="3">
                                <div class="step-icon"><i class="bi bi-truck"></i></div>
                                <div class="step-text">Delivery & Pricing</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Customer Selection -->
                <div class="step-content" id="step1">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select name="user_id" 
                                            class="form-select @error('user_id') is-invalid @enderror" 
                                            required>
                                        <option value="">Choose a customer...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} - {{ $user->email }}
                                                @if($user->phone)
                                                    ({{ $user->phone }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                                </div>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Select an existing customer from the list</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Cake Specifications -->
                <div class="step-content d-none" id="step2">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cake Size <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select name="cake_size" 
                                            class="form-select @error('cake_size') is-invalid @enderror" 
                                            required id="cake_size">
                                        <option value="">Select Size</option>
                                        @foreach($cakeSizes as $size)
                                            <option value="{{ $size->name }}" 
                                                    data-price="{{ $size->base_price }}"
                                                    {{ old('cake_size') == $size->name ? 'selected' : '' }}>
                                                {{ $size->name }} - PKR {{ $size->base_price }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                                </div>
                                @error('cake_size')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cake Flavor <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select name="cake_flavor" 
                                            class="form-select @error('cake_flavor') is-invalid @enderror" 
                                            required id="cake_flavor">
                                        <option value="">Select Flavor</option>
                                        @foreach($cakeFlavors as $flavor)
                                            <option value="{{ $flavor->name }}" 
                                                    data-price="{{ $flavor->price }}"
                                                    {{ old('cake_flavor') == $flavor->name ? 'selected' : '' }}>
                                                {{ $flavor->name }} - PKR {{ $flavor->price }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                                </div>
                                @error('cake_flavor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cake Filling <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select name="cake_filling" 
                                            class="form-select @error('cake_filling') is-invalid @enderror" 
                                            required id="cake_filling">
                                        <option value="">Select Filling</option>
                                        @foreach($cakeFillings as $filling)
                                            <option value="{{ $filling->name }}" 
                                                    data-price="{{ $filling->price }}"
                                                    {{ old('cake_filling') == $filling->name ? 'selected' : '' }}>
                                                {{ $filling->name }} - PKR {{ $filling->price }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                                </div>
                                @error('cake_filling')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cake Frosting <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select name="cake_frosting" 
                                            class="form-select @error('cake_frosting') is-invalid @enderror" 
                                            required id="cake_frosting">
                                        <option value="">Select Frosting</option>
                                        @foreach($cakeFrostings as $frosting)
                                            <option value="{{ $frosting->name }}" 
                                                    data-price="{{ $frosting->price }}"
                                                    {{ old('cake_frosting') == $frosting->name ? 'selected' : '' }}>
                                                {{ $frosting->name }} - PKR {{ $frosting->price }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                                </div>
                                @error('cake_frosting')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Special Instructions</label>
                                <textarea name="special_instructions" 
                                          class="form-control @error('special_instructions') is-invalid @enderror" 
                                          rows="3">{{ old('special_instructions') }}</textarea>
                                @error('special_instructions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Add any special requirements, decorations, or allergies here.</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Reference Image</label>
                                <input type="file" 
                                       name="reference_image" 
                                       class="form-control @error('reference_image') is-invalid @enderror" 
                                       accept="image/*">
                                @error('reference_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload a reference image for the cake design (optional).</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Delivery & Pricing -->
                <div class="step-content d-none" id="step3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Price (PKR) <span class="text-danger">*</span></label>
                                <input type="number" 
                                       name="price" 
                                       class="form-control @error('price') is-invalid @enderror" 
                                       value="{{ old('price') }}" 
                                       required
                                       id="price_input"
                                       step="0.01"
                                       min="0">
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Price will be auto-calculated based on selected options, but you can adjust it.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Order Status <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <select name="status" 
                                            class="form-select @error('status') is-invalid @enderror" 
                                            required>
                                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="in_progress" {{ old('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    </select>
                                    <i class="fas fa-chevron-down position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                                </div>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Delivery Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       name="delivery_date" 
                                       class="form-control @error('delivery_date') is-invalid @enderror" 
                                       value="{{ old('delivery_date') }}" 
                                       required
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                @error('delivery_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6"></div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Delivery Address <span class="text-danger">*</span></label>
                                <textarea name="delivery_address" 
                                          class="form-control @error('delivery_address') is-invalid @enderror" 
                                          rows="3" 
                                          required>{{ old('delivery_address') }}</textarea>
                                @error('delivery_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Navigation -->
                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-primary d-none" id="prevBtn" onclick="prevStep()">
                        <i class="bi bi-arrow-left me-1"></i> Previous
                    </button>
                    <button type="button" class="btn btn-primary" id="nextBtn" onclick="nextStep()">
                        Next <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                    <button type="submit" class="btn btn-success d-none" id="submitBtn">
                        <i class="bi bi-check-circle me-1"></i> Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.steps {
    position: relative;
    margin-bottom: 30px;
    padding: 0 20px;
}

.steps::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.step {
    position: relative;
    z-index: 2;
    text-align: center;
    width: 33.333%;
    padding: 0 20px;
}

.step-icon {
    width: 40px;
    height: 40px;
    margin: 0 auto;
    background: #fff;
    border: 2px solid #e9ecef;
    border-radius: 50%;
    line-height: 36px;
    font-size: 20px;
    color: #adb5bd;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.step-text {
    font-size: 0.875rem;
    color: #adb5bd;
    transition: all 0.3s ease;
}

.step.active .step-icon {
    background: #4e73df;
    border-color: #4e73df;
    color: #fff;
}

.step.active .step-text {
    color: #4e73df;
    font-weight: 600;
}

.step.completed .step-icon {
    background: #1cc88a;
    border-color: #1cc88a;
    color: #fff;
}

.step.completed .step-text {
    color: #1cc88a;
}
</style>

<script>
let currentStep = 1;
const totalSteps = 3;

function updateSteps() {
    // Update step indicators
    document.querySelectorAll('.step').forEach((step, index) => {
        const stepNum = index + 1;
        if (stepNum === currentStep) {
            step.classList.add('active');
            step.classList.remove('completed');
        } else if (stepNum < currentStep) {
            step.classList.remove('active');
            step.classList.add('completed');
        } else {
            step.classList.remove('active', 'completed');
        }
    });

    // Show/hide step content
    document.querySelectorAll('.step-content').forEach((content, index) => {
        if (index + 1 === currentStep) {
            content.classList.remove('d-none');
        } else {
            content.classList.add('d-none');
        }
    });

    // Update buttons
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');

    prevBtn.classList.toggle('d-none', currentStep === 1);
    nextBtn.classList.toggle('d-none', currentStep === totalSteps);
    submitBtn.classList.toggle('d-none', currentStep !== totalSteps);
}

function nextStep() {
    if (validateCurrentStep() && currentStep < totalSteps) {
        currentStep++;
        updateSteps();
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateSteps();
    }
}

function validateCurrentStep() {
    let isValid = true;
    let currentStepElement = document.getElementById('step' + currentStep);
    
    // Clear previous error states
    currentStepElement.querySelectorAll('.is-invalid').forEach(element => {
        element.classList.remove('is-invalid');
    });
    
    if (currentStep === 1) {
        // Validate customer selection
        const userSelect = currentStepElement.querySelector('select[name="user_id"]');
        if (!userSelect.value) {
            userSelect.classList.add('is-invalid');
            isValid = false;
        }
    } else if (currentStep === 2) {
        // Validate cake specifications
        const requiredFields = ['cake_size', 'cake_flavor', 'cake_filling', 'cake_frosting'];
        requiredFields.forEach(fieldName => {
            const field = currentStepElement.querySelector(`select[name="${fieldName}"]`);
            if (!field.value) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });
    } else if (currentStep === 3) {
        // Validate delivery and pricing
        const requiredFields = ['price', 'delivery_date', 'delivery_address'];
        requiredFields.forEach(fieldName => {
            const field = currentStepElement.querySelector(`input[name="${fieldName}"], textarea[name="${fieldName}"], select[name="${fieldName}"]`);
            if (field && !field.value) {
                field.classList.add('is-invalid');
                isValid = false;
            }
        });
    }
    
    if (!isValid) {
        // Show error message
        let errorMessage = 'Please fill in all required fields before proceeding.';
        if (currentStep === 1) errorMessage = 'Please select a customer.';
        else if (currentStep === 2) errorMessage = 'Please select all cake specifications.';
        else if (currentStep === 3) errorMessage = 'Please fill in price, delivery date, and address.';
        
        // Create or update error alert
        let existingAlert = document.querySelector('.validation-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger validation-alert';
        alert.innerHTML = `<i class="bi bi-exclamation-triangle me-2"></i>${errorMessage}`;
        currentStepElement.insertBefore(alert, currentStepElement.firstChild);
        
        // Remove alert after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    } else {
        // Remove any existing alerts
        const existingAlert = document.querySelector('.validation-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
    }
    
    return isValid;
}

// Calculate price based on size
document.getElementById('cake_size').addEventListener('change', function() {
    calculateTotalPrice();
});

document.getElementById('cake_flavor').addEventListener('change', function() {
    calculateTotalPrice();
});

document.getElementById('cake_filling').addEventListener('change', function() {
    calculateTotalPrice();
});

document.getElementById('cake_frosting').addEventListener('change', function() {
    calculateTotalPrice();
});

function calculateTotalPrice() {
    const sizeSelect = document.getElementById('cake_size');
    const flavorSelect = document.getElementById('cake_flavor');
    const fillingSelect = document.getElementById('cake_filling');
    const frostingSelect = document.getElementById('cake_frosting');
    const priceInput = document.getElementById('price_input');
    
    let totalPrice = 0;
    
    // Get size price
    if (sizeSelect.value) {
        const selectedSizeOption = sizeSelect.options[sizeSelect.selectedIndex];
        const sizePrice = parseFloat(selectedSizeOption.getAttribute('data-price')) || 0;
        totalPrice += sizePrice;
    }
    
    // Get flavor price
    if (flavorSelect.value) {
        const selectedFlavorOption = flavorSelect.options[flavorSelect.selectedIndex];
        const flavorPrice = parseFloat(selectedFlavorOption.getAttribute('data-price')) || 0;
        totalPrice += flavorPrice;
    }
    
    // Get filling price
    if (fillingSelect.value) {
        const selectedFillingOption = fillingSelect.options[fillingSelect.selectedIndex];
        const fillingPrice = parseFloat(selectedFillingOption.getAttribute('data-price')) || 0;
        totalPrice += fillingPrice;
    }
    
    // Get frosting price
    if (frostingSelect.value) {
        const selectedFrostingOption = frostingSelect.options[frostingSelect.selectedIndex];
        const frostingPrice = parseFloat(selectedFrostingOption.getAttribute('data-price')) || 0;
        totalPrice += frostingPrice;
    }

    // Update price input
    if (priceInput && totalPrice > 0) {
        priceInput.value = totalPrice.toFixed(2);
    }
}

// Form submission validation
document.getElementById('createOrderForm').addEventListener('submit', function(e) {
    // Validate all steps before submission
    let allValid = true;
    for (let i = 1; i <= totalSteps; i++) {
        currentStep = i;
        if (!validateCurrentStep()) {
            allValid = false;
            break;
        }
    }
    
    if (!allValid) {
        e.preventDefault();
        // Go back to first invalid step
        updateSteps();
        return false;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Creating Order...';
    submitBtn.disabled = true;
    
    // Re-enable button after timeout (in case of errors)
    setTimeout(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 10000);
});

// Initialize steps
document.addEventListener('DOMContentLoaded', function() {
    console.log('Custom Cake Order Form Initialized');
    updateSteps();
});
</script>
@endsection 