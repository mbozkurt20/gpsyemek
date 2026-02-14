@extends('admin.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="db-card shadow-sm">
                <div class="db-card-header !bg-gray-100 flex justify-between items-center py-3">
                    <h3 class="db-card-title text-lg font-bold text-gray-700">
                        <i class="fa-solid fa-utensils mr-2"></i> {{ $menuItem->name }} - Opsiyon Yönetimi
                    </h3>
                    <button type="button" class="db-btn h-[38px] text-white bg-primary hover:bg-primary-dark transition-all" id="add-group">
                        <i class="fa-solid fa-circle-plus mr-1"></i>
                        <span>Yeni Grup Ekle</span>
                    </button>
                </div>

                <div class="db-card-body p-6">
                    <form action="{{ route('admin.menu-items.modify', $menuItem) }}" method="POST" id="option-form">
                        @csrf
                        @method('PUT')

                        <div id="groups-container">
                            @foreach($menuItem->optionGroups ?? [] as $gIndex => $group)
                                <div class="db-card border border-gray-300 mb-8 group-item rounded-lg overflow-hidden shadow-sm" data-index="{{ $gIndex }}">
                                    <div class="db-card-header bg-gray-50 flex justify-between items-center border-b p-4">
                                        <div class="flex gap-4 items-center flex-wrap">
                                            <div class="flex flex-col">
                                                <label class="text-xs font-bold uppercase text-gray-500 mb-1">Grup Adı</label>
                                                <input type="text" name="groups[{{ $gIndex }}][name]" value="{{ $group->name }}" placeholder="Örn: Ekstralar" class="db-field-control !w-64 border-gray-300 focus:ring-primary text-sm">
                                            </div>
                                            <div class="flex flex-col">
                                                <label class="text-xs font-bold uppercase text-gray-500 mb-1">Seçim Tipi</label>
                                                <select name="groups[{{ $gIndex }}][type]" class="db-field-control !w-44 border-gray-300 type-select text-sm">
                                                    <option value="radio" {{ $group->type == 'radio' ? 'selected' : '' }}>Tekli Seçim (Radio)</option>
                                                    <option value="checkbox" {{ $group->type == 'checkbox' ? 'selected' : '' }}>Çoklu Seçim (Checkbox)</option>
                                                </select>
                                            </div>
                                            <div class="flex gap-2 count-settings {{ $group->type == 'radio' ? 'hidden' : '' }}">
                                                <div class="flex flex-col">
                                                    <label class="text-xs font-bold uppercase text-gray-500 mb-1">En Az</label>
                                                    <input type="number" name="groups[{{ $gIndex }}][min_count]" value="{{ $group->min_count ?? 0 }}" class="db-field-control !w-20 border-gray-300 text-sm">
                                                </div>
                                                <div class="flex flex-col">
                                                    <label class="text-xs font-bold uppercase text-gray-500 mb-1">En Fazla (Sınırsız için 0 giriniz)</label>
                                                    <div class="relative">
                                                        <input type="number" name="groups[{{ $gIndex }}][max_count]" value="{{ $group->max_count ?? 0 }}" class="db-field-control !w-24 border-gray-300 text-sm max-count-input" placeholder="0 = ∞">
                                                        @if(($group->max_count ?? 0) == 0)
                                                            <span class="text-[10px] text-primary font-bold block mt-1">Sınırsız (∞)</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-center mt-5 ml-2">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" name="groups[{{ $gIndex }}][is_required]" class="w-4 h-4 text-primary rounded border-gray-300" {{ $group->is_required ? 'checked' : '' }}>
                                                    <span class="ml-2 text-sm font-semibold text-gray-700">Zorunlu</span>
                                                </label>
                                            </div>
                                        </div>
                                        <button type="button" class="text-red-500 remove-group text-sm font-bold"><i class="fa-solid fa-trash-can mr-1"></i> Grubu Sil</button>
                                    </div>

                                    <div class="db-card-body !p-0">
                                        <table class="w-full text-left border-collapse">
                                            <thead class="bg-gray-100 border-b">
                                            <tr>
                                                <th class="px-4 py-3 text-xs font-bold uppercase text-gray-600">Seçenek Adı / Bağlı Ürün</th>
                                                <th class="px-4 py-3 text-xs font-bold uppercase text-gray-600 w-48">Ek Fiyat (₺)</th>
                                                <th class="px-4 py-3 text-xs font-bold uppercase text-gray-600 w-20 text-center">İşlem</th>
                                            </tr>
                                            </thead>
                                            <tbody class="items-container divide-y divide-gray-200">
                                            @foreach($group->options as $iIndex => $option)
                                                <tr class="hover:bg-gray-50 transition-colors">
                                                    <td class="p-3">
                                                        <div class="flex gap-2">
                                                            <input type="text" name="groups[{{ $gIndex }}][items][{{ $iIndex }}][name]" value="{{ $option->name }}" placeholder="Seçenek Adı" class="db-field-control !w-1/2 text-sm opt-name">
                                                            <select name="groups[{{ $gIndex }}][items][{{ $iIndex }}][linked_item_id]" class="db-field-control !w-1/2 text-sm border-gray-200 linked-item-select">
                                                                <option value="">-- Ürün Bağla (Opsiyonel) --</option>
                                                                @foreach($allMenuItems as $item)
                                                                    <option value="{{ $item->id }}" {{ $option->linked_item_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td class="p-3">
                                                        <input type="number" step="0.01" name="groups[{{ $gIndex }}][items][{{ $iIndex }}][price]" value="{{ $option->price }}" class="db-field-control !w-full text-sm">
                                                    </td>
                                                    <td class="p-3 text-center">
                                                        <button type="button" class="text-gray-400 hover:text-red-500 remove-item"><i class="fa-solid fa-circle-xmark text-xl"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                        <div class="p-4 bg-white border-t">
                                            <button type="button" class="text-sm font-bold text-primary add-item" data-group="{{ $gIndex }}">
                                                <i class="fa-solid fa-plus-circle mr-1"></i> Yeni Seçenek Ekle
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" class="db-btn px-10 py-3 text-white bg-primary rounded-full font-bold">
                                <i class="fa-solid fa-save mr-2"></i> AYARLARI KAYDET
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        // Veriyi alıyoruz (Artık içinde price var)
        const allMenuItems = @json($allMenuItems ?? []);

        // Ürün seçildiğinde ismi VE fiyatı otomatik dolduran fonksiyon
        $(document).on('change', '.linked-item-select', function() {
            const selectedId = $(this).val();
            const row = $(this).closest('tr');
            const nameInput = row.find('.opt-name');
            const priceInput = row.find('input[type="number"]');

            if (selectedId !== "") {
                const selectedProduct = allMenuItems.find(item => item.id == selectedId);

                if (selectedProduct) {
                    // Kullanıcıyı uyarmadan ismi ve fiyatı doldur
                    nameInput.val(selectedProduct.name);
                    priceInput.val(selectedProduct.price);
                }
            }
        });

        // Tip Değişimi (Radio/Checkbox)
        function handleTypeChange(selectElement) {
            let container = $(selectElement).closest('.group-item').find('.count-settings');
            if ($(selectElement).val() === 'checkbox') {
                container.removeClass('hidden').addClass('flex');
            } else {
                container.removeClass('flex').addClass('hidden');
            }
        }

        $(document).ready(function() {
            $('.type-select').each(function() { handleTypeChange(this); });
        });

        $(document).on('change', '.type-select', function() { handleTypeChange(this); });

        // Grup Ekleme (Aynı kalıyor ama price uyumu eklendi)
        $(document).on('click', '#add-group', function() {
            let gIndex = $('.group-item').length;
            let groupHtml = `
            <div class="db-card border border-gray-300 mb-8 group-item rounded-lg overflow-hidden shadow-sm" data-index="${gIndex}">
                <div class="db-card-header bg-gray-50 flex justify-between items-center border-b p-4">
                    <div class="flex gap-4 items-center flex-wrap">
                        <div class="flex flex-col">
                            <label class="text-xs font-bold uppercase text-gray-500 mb-1">Grup Adı</label>
                            <input type="text" name="groups[${gIndex}][name]" placeholder="Yeni Grup Adı" class="db-field-control !w-64 border-gray-300 text-sm">
                        </div>
                        <div class="flex flex-col">
                            <label class="text-xs font-bold uppercase text-gray-500 mb-1">Seçim Tipi</label>
                            <select name="groups[${gIndex}][type]" class="db-field-control !w-44 border-gray-300 type-select text-sm">
                                <option value="radio">Tekli Seçim (Radio)</option>
                                <option value="checkbox">Çoklu Seçim (Checkbox)</option>
                            </select>
                        </div>
                        <div class="flex gap-2 count-settings hidden">
                            <div class="flex flex-col">
                                <label class="text-xs font-bold uppercase text-gray-500 mb-1">En Az</label>
                                <input type="number" name="groups[${gIndex}][min_count]" value="0" class="db-field-control !w-20 border-gray-300 text-sm">
                            </div>
                          // Şablonun içindeki ilgili kısım:
<div class="flex flex-col">
    <label class="text-xs font-bold uppercase text-gray-500 mb-1">En Fazla (Sınırsız için 0 giriniz)</label>
    <input type="number" name="groups[${gIndex}][max_count]" value="0" class="db-field-control !w-24 border-gray-300 text-sm max-count-input" placeholder="0 = ∞">
    <span class="infinity-badge text-[10px] text-primary font-bold block mt-1">Sınırsız (∞)</span>
</div>
                        </div>
                        <div class="flex items-center mt-5 ml-2">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="groups[${gIndex}][is_required]" class="w-4 h-4 text-primary rounded border-gray-300">
                                <span class="ml-2 text-sm font-semibold text-gray-700">Zorunlu</span>
                            </label>
                        </div>
                    </div>
                    <button type="button" class="text-red-500 remove-group text-sm font-bold"><i class="fa-solid fa-trash-can mr-1"></i> Grubu Sil</button>
                </div>
                <div class="db-card-body !p-0">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-3 text-xs font-bold uppercase text-gray-600">Seçenek Adı / Bağlı Ürün</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase text-gray-600 w-48">Ek Fiyat (₺)</th>
                                <th class="px-4 py-3 text-xs font-bold uppercase text-gray-600 w-20 text-center">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="items-container divide-y divide-gray-200"></tbody>
                    </table>
                    <div class="p-4 bg-white border-t">
                        <button type="button" class="text-sm font-bold text-primary add-item" data-group="${gIndex}">
                            <i class="fa-solid fa-plus-circle mr-1"></i> Yeni Seçenek Ekle
                        </button>
                    </div>
                </div>
            </div>`;
            $('#groups-container').append(groupHtml);
        });

        // Seçenek Ekleme
        $(document).on('click', '.add-item', function() {
            let gIndex = $(this).closest('.group-item').attr('data-index');
            let iIndex = $(this).closest('.db-card-body').find('tbody tr').length;

            let productOptions = '<option value="">-- Ürün Bağla (Opsiyonel) --</option>';
            allMenuItems.forEach(item => {
                productOptions += `<option value="${item.id}">${item.name}</option>`;
            });

            let itemHtml = `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="p-3">
                    <div class="flex gap-2">
                        <input type="text" name="groups[${gIndex}][items][${iIndex}][name]" placeholder="Seçenek Adı" class="db-field-control !w-1/2 text-sm opt-name">
                        <select name="groups[${gIndex}][items][${iIndex}][linked_item_id]" class="db-field-control !w-1/2 text-sm border-gray-200 linked-item-select">
                            ${productOptions}
                        </select>
                    </div>
                </td>
                <td class="p-3">
                    <input type="number" step="0.01" name="groups[${gIndex}][items][${iIndex}][price]" value="0.00" class="db-field-control !w-full text-sm">
                </td>
                <td class="p-3 text-center">
                    <button type="button" class="text-gray-400 hover:text-red-500 remove-item"><i class="fa-solid fa-circle-xmark text-xl"></i></button>
                </td>
            </tr>`;
            $(this).closest('.db-card-body').find('.items-container').append(itemHtml);
        });

        $(document).on('click', '.remove-group', function() { if(confirm('Emin misiniz?')) $(this).closest('.group-item').remove(); });
        $(document).on('click', '.remove-item', function() { $(this).closest('tr').remove(); });

        $(document).on('input', '.max-count-input', function() {
            let val = $(this).val();
            let parent = $(this).closest('.flex-col');
            parent.find('.infinity-badge').remove(); // Eski uyarıyı temizle

            if (val == 0 || val == "") {
                $(this).after('<span class="infinity-badge text-[10px] text-primary font-bold block mt-1">Sınırsız (∞)</span>');
            }
        });
    </script>
@endpush
