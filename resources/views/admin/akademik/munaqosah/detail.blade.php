{{--
    Admin > Akademik > Munaqosah > detail satu santri (Admin\MunaqosahController@show), dibuka di tab baru.
    Panel: 1 Detail santri (tanpa persyaratan & catatan admin) · 2 Nilai Ilmu · 3 Nilai Harian/Lisan/Ujian · 4 Nilai Dakwah
           5 Nilai Suluk · 6 Catatan Pencapaian & Pelanggaran · 7 Catatan Mandub · Simpan (-> tab Lajnah).
    Status "lajnah": panel 2/5/7 hanya dibaca, lalu panel Lajnah Qobul Darul Musthofa (catatan khusus + Lulus/Tidak,
    Kembalikan ke Musyrifin selama belum diputuskan).
    Dua aplikasi Vue: #munaqosah-santri (panel 1, partials/detail-santri) dan #halaman-munaqosah-detail (panel lainnya).
--}}
@extends('layouts.admin')

@section('title', __('Munaqosah') . ' · ' . $siswa->nama_lengkap)

@section('content')
@include('admin.akademik.munaqosah._skrip-panel')
@php
$p = $siswa; // nama variabel yang dipakai partials/detail-santri
$kartu = 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_12px_32px_-16px_rgba(11,51,44,0.18)]';
$input = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none';
$lajnah = $data['status'] === \App\Models\Munaqosah::LAJNAH;
$nomorPanel = fn ($n) => '<span class="w-7 h-7 shrink-0 rounded-lg bg-[#0b332c] text-white text-xs font-extrabold flex items-center justify-center">' . $n . '</span>';
@endphp

<div class="space-y-6 mx-4 md:mx-10 lg:mx-20 mt-4 pb-10">
    <a href="{{ route('admin.munaqosah.index', ['tab' => $lajnah ? 'lajnah' : 'musyrifin']) }}"
        class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-[#0b332c]">
        <span class="w-8 h-8 rounded-xl bg-white ring-1 ring-slate-200/70 flex items-center justify-center">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        </span>
        {{ __('Kembali ke Munaqosah') }}
    </a>

    {{-- Pita status --}}
    <div class="rounded-2xl px-4 py-3 text-sm font-semibold ring-1 {{ $lajnah ? 'bg-amber-50 text-amber-800 ring-amber-200' : 'bg-emerald-50 text-emerald-800 ring-emerald-200' }}">
        @if ($lajnah)
            {{ __('Tahap: Lajnah Qobul Darul Musthofa. Isian Musyrifin hanya bisa dibaca.') }}
        @else
            {{ __('Tahap: Musyrifin Dauroh. Isi Nilai Ilmu, Suluk, dan Catatan Mandub, lalu tekan Simpan di paling bawah.') }}
        @endif
    </div>

    {{-- ===== Panel 1: Detail santri ===== --}}
    <h2 class="flex items-center gap-2 text-lg font-extrabold text-slate-800">{!! $nomorPanel(1) !!} {{ __('Detail santri') }}</h2>
    <div id="munaqosah-santri" class="space-y-6">
        @include('partials.detail-santri', ['admin' => false, 'judul' => __('Munaqosah'), 'rahasia' => true, 'tanpaSyarat' => true])
    </div>

    <div id="halaman-munaqosah-detail" v-cloak class="space-y-6">
        {{-- ===== Panel 2: Nilai Ilmu ===== --}}
        @include('admin.akademik.munaqosah._panel-isian', ['nomor' => 2, 'b' => 'ilmu'])

        {{-- ===== Panel 3: Nilai Harian, Ujian Lisan, Ujian ===== --}}
        @include('admin.akademik.munaqosah._panel-nilai')

        {{-- ===== Panel 4: Nilai Dakwah ===== --}}
        @include('admin.akademik.munaqosah._panel-dakwah')

        {{-- ===== Panel 5: Nilai Suluk ===== --}}
        @include('admin.akademik.munaqosah._panel-isian', ['nomor' => 5, 'b' => 'suluk'])

        {{-- ===== Panel 6: Catatan Pencapaian & Pelanggaran ===== --}}
        @include('admin.akademik.munaqosah._panel-catatan')

        {{-- ===== Panel 7: Catatan Mandub ===== --}}
        @include('admin.akademik.munaqosah._panel-isian', ['nomor' => 7, 'b' => 'mandub'])

        @if (! $lajnah)
        {{-- ===== Simpan (Musyrifin) ===== --}}
        <div class="{{ $kartu }} p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center gap-4">
            <p class="flex-1 text-sm text-slate-500">{{ __('Setelah disimpan, santri ini pindah ke tab Lajnah Qobul Darul Musthofa untuk diputuskan.') }}</p>
            <button type="button" @click="simpan" :disabled="menyimpan"
                class="px-6 py-3 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50"
                v-text="menyimpan ? teks.menyimpan : teks.simpan"></button>
        </div>
        @else
        {{-- ===== Lajnah Qobul Darul Musthofa ===== --}}
        <section class="relative overflow-hidden rounded-[28px] bg-linear-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white p-5 sm:p-7">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-200/80">{{ __('Keputusan') }}</p>
            <h2 class="mt-1 text-2xl font-extrabold">{{ __('Lajnah Qobul Darul Musthofa') }}</h2>
            @if ($data['disimpan'])
            <p class="mt-1 text-xs text-white/60" v-pre>{{ __('Dikirim Musyrifin oleh :nama, :waktu', ['nama' => $data['disimpan'], 'waktu' => \Illuminate\Support\Carbon::parse($data['dikirim'])->translatedFormat('d F Y, H:i')]) }}</p>
            @endif

            {{-- Tanda keputusan --}}
            <div class="mt-5 rounded-2xl px-4 py-3 ring-1
                {{ $data['keputusan'] === 'lulus' ? 'bg-emerald-400/20 ring-emerald-300/40' : ($data['keputusan'] === 'tidak' ? 'bg-rose-400/20 ring-rose-300/40' : 'bg-white/10 ring-white/20') }}">
                @if ($data['keputusan'])
                    <p class="text-lg font-extrabold">{{ $data['keputusan'] === 'lulus' ? '✅' : '❌' }} {{ __(\App\Models\Munaqosah::KEPUTUSAN[$data['keputusan']]) }}</p>
                    <p class="text-xs text-white/70" v-pre>{{ __('Diputuskan oleh :nama, :waktu', ['nama' => $data['diputuskan'] ?? '-', 'waktu' => \Illuminate\Support\Carbon::parse($data['diputuskan_pada'])->translatedFormat('d F Y, H:i')]) }}</p>
                @else
                    <p class="font-bold">⏳ {{ __('Menunggu keputusan Lajnah') }}</p>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.munaqosah.keputusan', $siswa) }}" class="mt-5">
                @csrf
                <label class="block"><span class="text-sm font-semibold text-white/80">{{ __('Catatan khusus Lajnah Qobul Darul Musthofa') }}</span>
                    <textarea name="catatan_lajnah" rows="5" maxlength="10000" placeholder="{{ __('Tulis catatan hasil musyawarah Lajnah…') }}"
                        class="mt-1.5 w-full rounded-2xl border-0 bg-white px-4 py-3 text-sm text-slate-700 focus:ring-2 focus:ring-emerald-400 outline-none" v-pre>{{ old('catatan_lajnah', $data['catatan']) }}</textarea></label>

                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="submit" name="keputusan" value="lulus" @click="konfirmasi" data-konfirmasi="{{ __('Putuskan :nama LULUS / DITERIMA?', ['nama' => $siswa->nama_lengkap]) }}"
                        class="px-5 py-3 rounded-2xl text-sm font-extrabold {{ $data['keputusan'] === 'lulus' ? 'bg-emerald-300 text-[#0b332c] ring-4 ring-emerald-200/50' : 'bg-emerald-400 text-[#0b332c] hover:bg-emerald-300' }}">✅ {{ __('Lulus / Diterima') }}</button>
                    <button type="submit" name="keputusan" value="tidak" @click="konfirmasi" data-konfirmasi="{{ __('Putuskan :nama TIDAK lulus / tidak diterima?', ['nama' => $siswa->nama_lengkap]) }}"
                        class="px-5 py-3 rounded-2xl text-sm font-extrabold {{ $data['keputusan'] === 'tidak' ? 'bg-rose-300 text-[#4c0519] ring-4 ring-rose-200/50' : 'bg-rose-400 text-white hover:bg-rose-300' }}">❌ {{ __('Tidak') }}</button>
                    <button type="submit" name="keputusan" value="catatan" class="px-5 py-3 rounded-2xl text-sm font-bold bg-white/10 ring-1 ring-white/20 hover:bg-white/20">{{ __('Simpan catatan saja') }}</button>
                    @if ($data['keputusan'])
                    <button type="submit" name="keputusan" value="batal" @click="konfirmasi" data-konfirmasi="{{ __('Batalkan keputusan untuk :nama?', ['nama' => $siswa->nama_lengkap]) }}"
                        class="px-5 py-3 rounded-2xl text-sm font-bold bg-white/10 ring-1 ring-white/20 hover:bg-white/20">{{ __('Batalkan keputusan') }}</button>
                    @endif
                </div>
                @if ($data['keputusan'])
                <p class="mt-2 text-xs text-white/60">{{ __('Untuk mengubah keputusan, tekan tombol keputusan yang lain.') }}</p>
                @endif
            </form>

            @unless ($data['keputusan'])
            <form method="POST" action="{{ route('admin.munaqosah.kembalikan', $siswa) }}" class="mt-5 border-t border-white/10 pt-4">
                @csrf
                <button type="submit" @click="konfirmasi" data-konfirmasi="{{ __('Kembalikan :nama ke Musyrifin Dauroh untuk diperbaiki?', ['nama' => $siswa->nama_lengkap]) }}"
                    class="px-4 py-2.5 rounded-2xl text-sm font-bold bg-amber-300 text-[#3b2a00] hover:bg-amber-200">&larr; {{ __('Kembalikan ke Musyrifin') }}</button>
            </form>
            @endunless
        </section>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const CSRF = @json(csrf_token());
        const DATA = @json($data);
        const KOLOM = @json($kolom);

        Vue.createApp({ methods: { ...metodeHalaman } }).mount('#munaqosah-santri');

        Vue.createApp({
            mixins: [window.MUNAQOSAH_PANEL],
            data() {
                return {
                    lajnah: @js($lajnah),
                    kolom: KOLOM,
                    bagian: @json($bagian),
                    isian: this.isianDari(KOLOM, DATA.isian),
                    tersimpan: DATA.isian,
                    nilai: @json($nilai),
                    aspek: @json($aspek),
                    dakwah: @json($dakwah),
                    jenisDakwah: @json($jenisDakwah),
                    catatan: @json($catatan),
                    menyimpan: false,
                    teks: {
                        simpan: @js(__('Simpan & kirim ke Lajnah')), menyimpan: @js(__('Menyimpan…')),
                        yakin: @js(__('Simpan semua isian dan kirim santri ini ke Lajnah Qobul Darul Musthofa?')),
                        gagal: @js(__('Gagal menyimpan. Coba lagi.')),
                    },
                };
            },
            methods: {
                ...metodeHalaman,
                async simpan() {
                    if (!confirm(this.teks.yakin)) return;
                    this.menyimpan = true;
                    try {
                        const res = await fetch(@js(route('admin.munaqosah.simpan', $siswa)), {
                            method: 'POST',
                            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                            body: JSON.stringify({ isian: this.isian }),
                        });
                        const json = await res.json().catch(() => ({}));
                        if (!res.ok) throw new Error(json.errors ? Object.values(json.errors)[0][0] : (json.message || this.teks.gagal));
                        location.reload(); // tampil sebagai tahap Lajnah + notifikasi berhasil
                    } catch (e) {
                        window.notifikasi ? window.notifikasi.gagal(e.message) : alert(e.message);
                        this.menyimpan = false;
                    }
                },
            },
        }).mount('#halaman-munaqosah-detail');
    });
</script>
@endpush
