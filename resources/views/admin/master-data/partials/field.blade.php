{{-- Satu isian form. Variabel: $field (dari config), $colors (dari controller). --}}

{{-- ini juga yang mengatur modal ketika di klik --}}
@php
    $id = 'f-'.$field['key'];
    $model = 'modal.form.'.$field['key'];
    $required = ! empty($field['required']);
    $input = 'block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm outline-none focus:border-[#0b332c]/40 focus:ring-2 focus:ring-[#22c55e]/20';
@endphp

@if ($field['type'] === 'section')
    {{-- Judul kecil pemisah di dalam form, mis. "Kalender Masehi" --}}
    @php $warna = $field['color'] ?? 'gray'; @endphp
    <div class="col-span-2 -mb-1 mt-1 flex items-center gap-2">
        <span @class([
            'rounded-md px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide',
            'bg-sky-50 text-sky-700' => $warna === 'sky',
            'bg-emerald-50 text-emerald-700' => $warna === 'emerald',
            'bg-gray-100 text-gray-600' => ! in_array($warna, ['sky', 'emerald']),
        ])>{{ $field['label'] }}</span>
        <span class="h-px flex-1 bg-gray-200"></span>
    </div>
    @isset($field['hint'])
        <p class="col-span-2 -mt-2 text-xs text-gray-500">{{ $field['hint'] }}</p>
    @endisset
@else
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
                {{-- tanggal Masehi -> isi otomatis tanggal Hijriyah-nya --}}
                @isset($field['hijri']) v-on:change="isiHijriyah('{{ $field['key'] }}', '{{ $field['hijri'] }}')" @endisset
                {{ $required ? 'required' : '' }}
            >
    @endswitch

    <p v-if="modal.errors['{{ $field['key'] }}']" class="mt-1 text-xs text-red-600" v-text="modal.errors['{{ $field['key'] }}'][0]"></p>
    @isset($field['hint'])
        <p v-else class="mt-1 text-xs text-gray-500">{{ $field['hint'] }}</p>
    @endisset
</div>
@endif
