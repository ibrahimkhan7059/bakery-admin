@extends('layouts.app')

@section('title','Cake Configuration')

@section('content')
<div class="container-fluid">
	<div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-4">
		<div></div>
		<a href="{{ route('dashboard') }}" class="btn btn-primary"><i class="bi bi-arrow-left me-1"></i> Back</a>
	</div>

	@if(session('success'))
		<div class="alert alert-success">{{ session('success') }}</div>
	@endif
	@if($errors->any())
		<div class="alert alert-danger">
			<ul class="mb-0">
				@foreach($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	@php
		$presetSizes = collect(['1 Pound','2 Pounds','3 Pounds','5 Pounds']);
		$existingSizes = collect($sizes)->pluck('name');
		$allSizeOptions = $presetSizes->merge($existingSizes)->unique()->values();
		$groupPayload = $groups->map(function($g){
			return [
				'key' => $g->key,
				'label' => $g->label,
				'options' => $g->options->map(function($o){
					return ['name' => $o->name, 'price' => $o->price];
				})->values(),
			];
		})->values();
	@endphp

	<div class="row g-4">
		<div class="col-lg-5">
			<div class="card shadow-sm">
				<div class="card-header bg-white fw-semibold">Cake Sizes</div>
				<div class="card-body">
					<form action="{{ route('admin.cake-config.size.store') }}" method="POST" class="row g-2 align-items-end mb-3" id="sizeCreateForm">
						@csrf
						<div class="col-7">
							<label class="form-label">Size Name</label>
							<select class="form-select" id="size_name_select">
								@foreach($allSizeOptions as $opt)
									<option value="{{ $opt }}">{{ $opt }}</option>
								@endforeach
								<option value="__other__">Other...</option>
							</select>
							<input type="text" name="name" id="size_name_custom" class="form-control mt-2 d-none" placeholder="e.g., 7 Pounds">
						</div>
						<div class="col-5">
							<label class="form-label">Base Price (PKR)</label>
							<input type="number" step="0.01" name="base_price" class="form-control" required>
						</div>
						<div class="col-12">
							<button class="btn btn-success w-100"><i class="bi bi-plus-circle me-1"></i> Add Size</button>
						</div>
					</form>

					<table class="table table-sm align-middle">
						<thead><tr><th>Name</th><th>Base Price</th><th class="text-end">Actions</th></tr></thead>
						<tbody>
							@forelse($sizes as $size)
							<tr>
								<td>{{ $size->name }}</td>
								<td>PKR {{ number_format($size->base_price,2) }}</td>
								<td class="text-end">
									<button class="btn btn-sm btn-primary"
										data-bs-toggle="modal"
										data-bs-target="#editSizeModal"
										data-action="{{ route('admin.cake-config.size.update',$size) }}"
										data-name="{{ $size->name }}"
										data-price="{{ $size->base_price }}">
										<i class="bi bi-pencil"></i> Edit
									</button>
									<form action="{{ route('admin.cake-config.size.delete',$size) }}" method="POST" class="d-inline">
										@csrf
										@method('DELETE')
										<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
									</form>
								</td>
							</tr>
							@empty
							<tr><td colspan="3" class="text-center text-muted">No sizes yet</td></tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="col-lg-7">
			<div class="card shadow-sm">
				<div class="card-header bg-white fw-semibold">Options (Tags) & Prices</div>
				<div class="card-body">
					<form action="{{ route('admin.cake-config.option.store') }}" method="POST" class="row g-2 align-items-end mb-3">
						@csrf
						<div class="col-md-4">
							<label class="form-label">Group</label>
							<select name="group_key" class="form-select" required>
								<option value="flavor">Flavor</option>
								<option value="filling">Filling</option>
								<option value="frosting">Frosting</option>
							</select>
						</div>
						<div class="col-md-5">
							<label class="form-label">Option Name</label>
							<select id="option_name_select" class="form-select"></select>
							<input type="text" id="option_name_custom" class="form-control mt-2 d-none" placeholder="e.g., Vanilla">
							<input type="hidden" name="name" id="option_name_hidden">
						</div>
						<div class="col-md-3">
							<label class="form-label">Price (PKR)</label>
							<input type="number" step="0.01" name="price" id="option_price_input" class="form-control" required>
						</div>
						<div class="col-12">
							<button class="btn btn-success w-100"><i class="bi bi-plus-circle me-1"></i> Add Option</button>
						</div>
					</form>

					<div class="row g-3">
						@foreach($groups as $group)
						<div class="col-12">
							<h6 class="mb-2">{{ $group->label }}</h6>
							<table class="table table-sm align-middle">
								<thead><tr><th>Name</th><th>Price</th><th class="text-end">Actions</th></tr></thead>
								<tbody>
									@forelse($group->options as $option)
									<tr>
										<td>{{ $option->name }}</td>
										<td>PKR {{ number_format($option->price,2) }}</td>
										<td class="text-end">
																				<button class="btn btn-sm btn-primary"
										data-bs-toggle="modal"
										data-bs-target="#editOptionModal"
										data-action="{{ route('admin.cake-config.option.update',$option) }}"
										data-name="{{ $option->name }}"
										data-price="{{ $option->price }}">
										<i class="bi bi-pencil"></i> Edit
									</button>
											<form action="{{ route('admin.cake-config.option.delete',$option) }}" method="POST" class="d-inline">
												@csrf
												@method('DELETE')
												<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
											</form>
										</td>
									</tr>
									@empty
									<tr><td colspan="3" class="text-center text-muted">No options</td></tr>
									@endforelse
								</tbody>
							</table>
						</div>
						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@push('scripts')
<script>
	document.addEventListener('DOMContentLoaded', function() {
		const select = document.getElementById('size_name_select');
		const custom = document.getElementById('size_name_custom');
		const form = document.getElementById('sizeCreateForm');

		function syncNameField() {
			if (select.value === '__other__') {
				custom.classList.remove('d-none');
				custom.setAttribute('name','name');
				custom.required = true;
			} else {
				custom.classList.add('d-none');
				custom.removeAttribute('name');
				custom.required = false;
				let hidden = form.querySelector('input[type="hidden"][name="name"]');
				if (!hidden) {
					hidden = document.createElement('input');
					hidden.type = 'hidden';
					hidden.name = 'name';
					form.appendChild(hidden);
				}
				hidden.value = select.value;
			}
		}
		select?.addEventListener('change', syncNameField);
		syncNameField();

		// Populate option dropdown by selected group
		const optionSelect = document.getElementById('option_name_select');
		const optionCustom = document.getElementById('option_name_custom');
		const optionHidden = document.getElementById('option_name_hidden');
		const optionPrice = document.getElementById('option_price_input');
		const groupSelect = document.querySelector('select[name="group_key"]');
		const groups = @json($groupPayload);

		function refreshOptions() {
			const key = groupSelect.value;
			const group = groups.find(g => g.key === key);
			optionSelect.innerHTML = '';
			if (group) {
				group.options.forEach(o => {
					const opt = document.createElement('option');
					opt.value = o.name;
					opt.textContent = o.name;
					opt.dataset.price = o.price;
					optionSelect.appendChild(opt);
				});
			}
			const other = document.createElement('option');
			other.value = '__other__';
			other.textContent = 'Other...';
			optionSelect.appendChild(other);
			applyOptionSelection(optionSelect.value || '');
		}

		function applyOptionSelection(value) {
			if (value === '__other__') {
				optionCustom.classList.remove('d-none');
				optionHidden.value = optionCustom.value;
				optionPrice.value = '';
				optionPrice.focus();
			} else {
				optionCustom.classList.add('d-none');
				optionHidden.value = value;
				const selected = optionSelect.options[optionSelect.selectedIndex];
				optionPrice.value = selected?.dataset?.price ?? '';
			}
		}

		groupSelect?.addEventListener('change', refreshOptions);
		optionSelect?.addEventListener('change', (e)=> applyOptionSelection(e.target.value));
		optionCustom?.addEventListener('input', ()=> optionHidden.value = optionCustom.value);
		refreshOptions();

		// Edit modal
		const editModal = document.getElementById('editSizeModal');
		const sizeOptions = @json($allSizeOptions->values());
		if (editModal) {
			editModal.addEventListener('show.bs.modal', function (event) {
				const button = event.relatedTarget;
				const action = button.getAttribute('data-action');
				const name = button.getAttribute('data-name');
				const price = button.getAttribute('data-price');
				const formEl = editModal.querySelector('form');
				formEl.setAttribute('action', action);
				editModal.querySelector('input[name="base_price"]').value = price;

				const selectEl = editModal.querySelector('#edit_size_name_select');
				const customEl = editModal.querySelector('#edit_size_name_custom');
				const hiddenEl = editModal.querySelector('#edit_size_name_hidden');

				function applySelection(value) {
					if (sizeOptions.includes(value)) {
						selectEl.value = value;
						customEl.classList.add('d-none');
						customEl.required = false;
						hiddenEl.value = value;
					} else {
						selectEl.value = '__other__';
						customEl.classList.remove('d-none');
						customEl.required = true;
						customEl.value = value || '';
						hiddenEl.value = value || '';
					}
				}

				applySelection(name);
				selectEl.onchange = function() {
					if (this.value === '__other__') {
						customEl.classList.remove('d-none');
						customEl.required = true;
						hiddenEl.value = customEl.value;
					} else {
						customEl.classList.add('d-none');
						customEl.required = false;
						hiddenEl.value = this.value;
					}
				};
				customEl.oninput = function() {
					hiddenEl.value = this.value;
				};
			});
		}

		// Edit Option modal
		const editOptionModal = document.getElementById('editOptionModal');
		if (editOptionModal) {
			editOptionModal.addEventListener('show.bs.modal', function (event) {
				const button = event.relatedTarget;
				const action = button.getAttribute('data-action');
				const name = button.getAttribute('data-name');
				const price = button.getAttribute('data-price');
				const formEl = editOptionModal.querySelector('form');
				formEl.setAttribute('action', action);
				editOptionModal.querySelector('input[name="name"]').value = name;
				editOptionModal.querySelector('input[name="price"]').value = price;
			});
		}
	});
</script>
@endpush

<!-- Global Edit Size Modal -->
<div class="modal fade" id="editSizeModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Edit Size</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form action="#" method="POST">
				@csrf
				@method('PUT')
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Size Name</label>
						<select id="edit_size_name_select" class="form-select">
							@foreach($allSizeOptions as $opt)
								<option value="{{ $opt }}">{{ $opt }}</option>
							@endforeach
							<option value="__other__">Other...</option>
						</select>
						<input id="edit_size_name_custom" type="text" class="form-control mt-2 d-none" placeholder="e.g., 7 Pounds">
						<input id="edit_size_name_hidden" type="hidden" name="name" value="">
					</div>
					<div class="mb-3">
						<label class="form-label">Base Price (PKR)</label>
						<input name="base_price" type="number" step="0.01" class="form-control" required>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button class="btn btn-primary">Update</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- Global Edit Option Modal -->
<div class="modal fade" id="editOptionModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Edit Option</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form action="#" method="POST">
				@csrf
				@method('PUT')
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label">Option Name</label>
						<input name="name" type="text" class="form-control">
					</div>
					<div class="mb-3">
						<label class="form-label">Price (PKR)</label>
						<input name="price" type="number" step="0.01" class="form-control" required>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button class="btn btn-primary">Update</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection 