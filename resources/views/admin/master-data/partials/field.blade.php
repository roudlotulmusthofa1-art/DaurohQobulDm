{{-- Satu isian form. Variabel: $field (dari config), $colors (dari controller). --}}
@php
    $id = 'f-'.$field['key'];
    $model = 'modal.form.'.$field['key'];
    $required = ! empty($field['required']);
    $input = 'block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-[#0b332c]/40 focus:ring-2 focus:ring-[#22c55e]/20';
@endphp

<div class="{{ ! empty($field['half']) ? 'col-span-2 sm:col-span-1' : 'col-span-2' }}">
    <label for="{{ $id }}" class="mb-1 block text-sm font-medium text-gray-700">
        {{ $field['label'] }}
        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    @switch($field['type'])
        @case('textarea')
            <textarea id="{{ $id }}" v-model="{{ $model }}" rows="3" class="{{ $input }}" placeholder="{{ $field['placeholder'] ?? '' }}"></textarea>
            @break

        @case('select')
            <select id="{{ $id }}" v-model="{{ $model }}" class="{{ $input }}">
                @foreach ($field['options'] as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @break

        @case('color')
            <div class="flex flex-wrap gap-2">
                @foreach ($colors as $value => $hex)
                    <button
                        type="button"
                        title="{{ $value }}"
                        class="h-8 w-8 rounded-lg ring-1 ring-inset ring-black/10 transition"
                        style="background-color: {{ $hex }}"
                        :class="{{ $model }} === '{{ $value }}' ? 'scale-110 outline-2 outline-offset-2 outline-[#22c55e]' : 'hover:scale-105'"
                        @click="{{ $model }} = '{{ $value }}'"
                    ></button>
                @endforeach
            </div>
            @break

        @default
            <input
                id="{{ $id }}"
                type="{{ $field['type'] }}"
                v-model{{ $field['type'] === 'number' ? '.number' : '' }}="{{ $model }}"
                class="{{ $input }}"
                placeholder="{{ $field['placeholder'] ?? '' }}"
                @isset($field['min']) min="{{ $field['min'] }}" @endisset
                {{ $required ? 'required' : '' }}
            >
    @endswitch

    <p v-if="modal.errors['{{ $field['key'] }}']" class="mt-1 text-xs text-red-600" v-text="modal.errors['{{ $field['key'] }}'][0]"></p>
    @isset($field['hint'])
        <p v-else class="mt-1 text-xs text-gray-500">{{ $field['hint'] }}</p>
    @endisset
</div>
