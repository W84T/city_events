@php
    use Illuminate\View\ComponentAttributeBag;
@endphp

@props([
    'debounce' => '500ms',
    'onBlur' => false,
    'options' => [],
    'placeholder' => __('filament-tables::table.fields.search.placeholder'),
    'wireModel' => 'tableSearch',
])

@php
    $wireModelAttribute = $onBlur ? 'wire:model.blur' : "wire:model.live.debounce.{$debounce}";
@endphp

<div
    x-id="['input']"
    {{ $attributes->class(['fi-ta-search-field']) }}
>

    <x-filament::input.wrapper
        prefix-icon="heroicon-m-magnifying-glass"

        prefix-icon-alias="tables::search-field"
        :wire:target="$wireModel">
        <x-filament::input.select
            :attributes="
                (new ComponentAttributeBag)->merge([
                    'wire:key' => $this->getId() . '.table.' . $wireModel . '.field.select',
                    $wireModelAttribute => $wireModel,
                    'x-bind:id' => '$id(\'input\')',
                ])
            ">
            <option value="">{{ $placeholder }}</option>
            @foreach ($options as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </x-filament::input.select>
    </x-filament::input.wrapper>
</div>
