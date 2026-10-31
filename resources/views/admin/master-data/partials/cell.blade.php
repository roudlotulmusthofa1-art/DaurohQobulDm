{{--
    Satu sel tabel. Variabel: $col (dari config/master-data.php).
    Data baris hanya ditampilkan lewat v-text / binding Vue — tidak pernah di-echo
    ke template — supaya isi database tidak bisa dieksekusi sebagai template Vue.
--}}
@php $value = 'row.'.$col['key']; @endphp

@switch($col['type'])
    @case('name')
        <td class="px-3 py-4">
            <div class="font-medium text-gray-900" v-text="{{ $value }}"></div>
            @isset($col['sub'])
                <div class="text-xs text-gray-500" v-text="'#' + row.id + (row.{{ $col['sub'] }} ? ' • ' + row.{{ $col['sub'] }} : '')"></div>
            @else
                <div class="text-xs text-gray-500" v-text="'#' + row.id"></div>
            @endisset
        </td>
        @break

    @case('color')
        <td class="px-3 py-4">
            <div class="flex items-center gap-2">
                <span class="h-6 w-6 rounded-md ring-1 ring-inset ring-black/5" :style="{ backgroundColor: colorHex({{ $value }}) }"></span>
                <span class="text-xs text-gray-500" v-text="{{ $value }} || '-'"></span>
            </div>
        </td>
        @break

    @case('count')
        <td class="px-3 py-4 text-center">
            <span class="inline-flex h-6 min-w-7 items-center justify-center rounded-full bg-amber-500 px-2 text-xs font-semibold text-white" v-text="{{ $value }} ?? 0"></span>
        </td>
        @break

    @case('badge')
        <td class="px-3 py-4">
            <span
                class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ring-1 ring-inset"
                :class="(@js($col['badges']))[{{ $value }}] || 'bg-gray-50 text-gray-700 ring-gray-200'"
                v-text="{{ $value }}"
            ></span>
        </td>
        @break

    @case('time-range')
        <td class="px-3 py-4 text-gray-600" v-text="timeRange(row)"></td>
        @break

    @case('suffix')
        <td class="px-3 py-4 text-gray-600" v-text="({{ $value }} ?? 0) + ' {{ $col['suffix'] }}'"></td>
        @break

    @default
        <td class="px-3 py-4">
            <span class="line-clamp-2 text-gray-600" v-text="show({{ $value }})"></span>
        </td>
@endswitch
