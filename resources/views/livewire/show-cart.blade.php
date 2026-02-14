<div>
    <div wire:ignore.self class="modal fade product-modal" id="cartModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                @if (!blank($menuItem))
                    <div class="product-modal-media">
                        <img src="{{ $menuItem->image }}" alt="modal">
                        <button class="fa-regular fa-circle-xmark" type="button" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="product-modal-group">
                        <h3 class="product-modal-title">{{ $menuItem->name }} </h3>
                        <p class="product-modal-describe">{!! $menuItem->description !!} </p>
                    </div>

                    <form wire:submit.prevent="submit({{ $restaurant->id }},{{ $menuItem->id }})">

                        @foreach ($menuItem->optionGroups as $group)
                            <div class="product-modal-group">
                                <dl class="product-modal-subset">
                                    <dt>
                                        {{ $group->name }}
                                        {{-- Müşteriye Min/Max bilgisini gösteriyoruz --}}
                                        @if($group->type == 'checkbox')
                                            <small class="text-muted" style="font-size: 12px; display: block; font-weight: normal;">
                                                @if($group->min_count > 0 && $group->max_count > 0)
                                                    (En az {{ $group->min_count }}, en fazla {{ $group->max_count }} seçim)
                                                @elseif($group->max_count > 0)
                                                    (En fazla {{ $group->max_count }} seçim yapabilirsiniz)
                                                @endif
                                            </small>
                                        @endif
                                    </dt>
                                    <dd class="{{ $group->is_required ? 'require' : 'option' }}">
                                        {{ $group->is_required ? __('frontend.required') : __('frontend.optional') }}
                                    </dd>
                                </dl>

                                <ul class="product-modal-list">
                                    @php
                                        // Bu grupta kaç tane checkbox işaretlenmiş hesapla
                                        $currentSelections = isset($selectedOptions[$group->id]) && is_array($selectedOptions[$group->id])
                                            ? count(array_filter($selectedOptions[$group->id]))
                                            : 0;
                                    @endphp

                                    @foreach ($group->options as $option)
                                        <li>
                                            @if($group->type == 'radio')
                                                <input wire:model.live="selectedOptions.{{ $group->id }}"
                                                       id="opt-{{ $option->id }}"
                                                       name="group-{{ $group->id }}"
                                                       type="radio"
                                                       value="{{ $option->id }}"
                                                       class="form-radio">
                                            @else
                                                {{-- Çoklu seçimde limit kontrolü --}}
                                                <input wire:model.live="selectedOptions.{{ $group->id }}.{{ $option->id }}"
                                                       id="opt-{{ $option->id }}"
                                                       type="checkbox"
                                                       value="{{ $option->id }}"
                                                       class="form-checkbox"
                                                       {{-- Limit dolduysa ve bu kutu seçili değilse tık kapat --}}
                                                       @if($group->max_count > 0 && $currentSelections >= $group->max_count && !($selectedOptions[$group->id][$option->id] ?? false))
                                                           disabled
                                                    @endif
                                                >
                                            @endif

                                            <label for="opt-{{ $option->id }}" class="{{ ($group->max_count > 0 && $currentSelections >= $group->max_count && !($selectedOptions[$group->id][$option->id] ?? false)) ? 'text-muted' : '' }}">
                                                {{ $option->name }}
                                            </label>

                                            @if($option->price > 0)
                                                <span> + {{ setting('currency_code') }} {{ $option->price }} </span>
                                            @else
                                                <span class="text-gray-700">+ ₺ 0.00</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach

                        <div class="product-modal-group">
                            <dl class="product-modal-subset">
                                <dt>{{ __('frontend.special_instructions') }} </dt>
                                <dd class="option">{{ __('frontend.optional') }}</dd>
                            </dl>
                            <textarea class="product-modal-instruct"
                                      wire:model.live="instructions"
                                      placeholder="Örn: Soğan olmasın..."></textarea>
                        </div>

                        <div class="product-modal-footer">
                            <div class="cart-counter">
                                <button type="button" wire:click.prevent="removeItemQty()" class="fa-solid fa-minus"></button>
                                <input type="number" wire:model.live="quantity" readonly class="cart-counter-value">
                                <button type="button" wire:click.prevent="addItemQty()" class="fa-solid fa-plus"></button>
                            </div>

                            <button class="cart-btn" type="submit">
                                <span>{{ __('frontend.add_to_cart') }} </span>
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
