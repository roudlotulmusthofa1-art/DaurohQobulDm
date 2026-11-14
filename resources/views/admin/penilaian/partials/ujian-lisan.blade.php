{{--
    Admin > Penilaian > Kelas & Pelajaran > Ujian Lisan (dimuat dari admin/penilaian/kelas.blade.php, di dalam Vue #halaman-penilaian-kelas).
    - Tombol "Pengaturan Ujian Lisan": tambah / ubah / matikan / hapus ujian
      (fan + kitab boleh kosong + jumlah pertanyaan + nilai maksimal tiap pertanyaan). Lihat App\Models\UjianLisan.
    - Tombol "Beri Nilai": admin ikut menilai, sama seperti asatid (santri -> ujian -> tanggal -> nilai tiap pertanyaan -> catatan).
    - Daftar semua nilai (dari asatid & admin) + saringan fan & cari; bisa diubah / dihapus. Lihat App\Models\NilaiUjianLisan.
    Data: $ujianLisan, $nilaiLisan, $fanList, $kitabFan, $siswaList.
--}}
@php
$inputLisan = 'w-full rounded-xl bg-[#f8fbfa] ring-1 ring-slate-200/80 border-0 px-3 py-2.5 text-sm text-slate-700 focus:ring-2 focus:ring-violet-500 outline-none';
$tutupIkon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>';
@endphp

<div v-show="tab === 'lisan'" @if ($tab !== 'lisan') style="display: none" @endif class="space-y-5">
    {{-- ===== Tombol + ringkasan ===== --}}
    <section class="{{ $kartu }} p-4 sm:p-5">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[12rem]">
                <h2 class="font-extrabold text-slate-800">{{ __('Ujian Lisan') }}</h2>
                <p class="text-sm text-slate-500">{{ __('Ujian dengan pertanyaan lisan. Diatur admin, dinilai asatid atau admin.') }}</p>
            </div>
            <button type="button" @click="bukaAturLisan()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-bold text-slate-700 hover:bg-slate-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                {{ __('Pengaturan Ujian Lisan') }}
            </button>
            <button type="button" @click="bukaNilaiLisan()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ __('Beri Nilai') }}
            </button>
        </div>

        {{-- Ujian yang aktif --}}
        <div class="mt-4 flex flex-wrap gap-2">
            <span v-for="u in ujianLisan" :key="u.id" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1"
                :class="u.is_active ? 'bg-violet-50 text-violet-800 ring-violet-200' : 'bg-slate-50 text-slate-400 ring-slate-200 line-through'">
                <span v-text="judulUjian(u)"></span>
                <span class="opacity-60" v-text="'· ' + u.maks.length + ' ' + teksPertanyaan + ' · ' + u.total_maks"></span>
            </span>
            <p v-if="!ujianLisan.length" class="w-full p-3 rounded-2xl bg-amber-50 ring-1 ring-amber-200 text-sm text-amber-900">{{ __('Belum ada ujian lisan. Tambahkan dulu lewat tombol Pengaturan Ujian Lisan.') }}</p>
        </div>
    </section>

    {{-- ===== Daftar nilai ===== --}}
    <section class="{{ $kartu }} p-4 sm:p-5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Fan / kategori') }}</span>
                <select v-model="saringFanLisan" class="mt-1 {{ $inputLisan }}">
                    <option value="">{{ __('Semua fan') }}</option>
                    <option v-for="f in fanDiNilai" :key="f" :value="f" v-text="f"></option>
                </select></label>
            <label class="block sm:col-span-2"><span class="text-xs font-semibold text-slate-500">{{ __('Cari') }}</span>
                <input type="search" v-model="cariLisan" placeholder="{{ __('Nama / nomor santri / kitab…') }}" class="mt-1 {{ $inputLisan }}"></label>
        </div>

        <div class="mt-4 flex flex-wrap gap-2 text-xs">
            <span class="rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-600"><span v-text="nilaiLisanTersaring.length"></span> {{ __('nilai') }}</span>
            <span v-if="nilaiLisanTersaring.length" class="rounded-full bg-violet-50 px-3 py-1 font-semibold text-violet-700 ring-1 ring-violet-200">{{ __('Rata-rata') }} <span v-text="bulat(rataLisan) + '%'"></span></span>
        </div>

        <ul class="mt-3 divide-y divide-slate-100">
            <li v-for="n in nilaiLisanTersaring" :key="n.id" class="py-3 flex flex-wrap items-start gap-3">
                <img v-if="n.foto" :src="n.foto" alt="" class="w-10 h-10 shrink-0 rounded-xl object-cover bg-slate-100">
                <span v-else class="w-10 h-10 shrink-0 rounded-xl bg-slate-100 text-slate-500 font-bold flex items-center justify-center" v-text="inisial(n.siswa)"></span>
                <div class="flex-1 min-w-[12rem]">
                    <p class="font-semibold text-slate-800">
                        <span v-text="n.siswa"></span>
                        <span v-if="n.kelas" class="ml-1 text-xs font-normal text-slate-400" v-text="n.kelas"></span>
                    </p>
                    <x-nama-arab vue="n.arab" />
                    <p class="text-xs text-slate-500">
                        <span class="font-semibold text-violet-700" v-text="n.fan || '-'"></span><template v-if="n.kitab"> · <span v-text="n.kitab"></span></template>
                        · <span v-text="tglPanjang(n.tanggal)"></span>
                        <template v-if="n.oleh"> · {{ __('oleh') }} <span v-text="n.oleh"></span></template>
                    </p>
                    <x-nama-arab vue="n.oleh_arab" kelas="text-xs text-slate-400" />
                    <div class="mt-1.5 flex flex-wrap gap-1">
                        <span v-for="(v, i) in n.nilai" :key="i" class="rounded-lg bg-slate-50 ring-1 ring-slate-200 px-1.5 py-0.5 text-[11px] text-slate-600">
                            <span class="text-slate-400" v-text="'P' + (i + 1) + ' '"></span><b v-text="v"></b><span class="text-slate-400" v-text="'/' + n.maks[i]"></span>
                        </span>
                    </div>
                    <p v-if="n.catatan" class="mt-1.5 rounded-xl bg-amber-50 ring-1 ring-amber-100 px-2.5 py-1.5 text-xs text-slate-700 whitespace-pre-line" dir="auto">
                        <b class="text-amber-800">{{ __('Catatan') }}:</b> <span v-text="n.catatan"></span>
                    </p>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1" :class="warnaTotal(n.persen)" v-text="n.total + '/' + n.total_maks"></span>
                    <button v-if="ujianAktif(n.ujian_id)" type="button" @click="ubahNilaiLisan(n)" class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-violet-700 hover:bg-violet-50">{{ __('Ubah') }}</button>
                    <button type="button" @click="hapusNilaiLisan(n)" :disabled="menghapus === 'lisan-' + n.id" class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 disabled:opacity-50">{{ __('Hapus') }}</button>
                </div>
            </li>
            <li v-if="!nilaiLisanTersaring.length" class="py-10 text-center text-sm text-slate-400">{{ __('Belum ada nilai ujian lisan.') }}</li>
        </ul>
    </section>
</div>

{{-- ===== Modal: Pengaturan Ujian Lisan ===== --}}
<div v-if="modalAturLisan" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:items-center" @mousedown.self="modalAturLisan = false">
    <div class="w-full max-w-2xl rounded-3xl bg-white shadow-xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-base font-extrabold text-slate-800">{{ __('Pengaturan Ujian Lisan') }}</h3>
            <button type="button" @click="modalAturLisan = false" class="w-8 h-8 rounded-xl text-slate-400 hover:bg-slate-100 flex items-center justify-center" aria-label="{{ __('Tutup') }}">{!! $tutupIkon !!}</button>
        </div>

        <div class="p-6 space-y-6">
            {{-- Daftar ujian --}}
            <div>
                <p class="text-sm font-bold text-slate-700">{{ __('Daftar ujian') }}</p>
                <ul class="mt-2 divide-y divide-slate-100 rounded-2xl ring-1 ring-slate-100">
                    <li v-for="u in ujianLisan" :key="u.id" class="flex flex-wrap items-center gap-2 px-3 py-2.5">
                        <div class="flex-1 min-w-[10rem]">
                            <p class="text-sm font-semibold" :class="u.is_active ? 'text-slate-800' : 'text-slate-400 line-through'" v-text="judulUjian(u)"></p>
                            <p class="text-xs text-slate-400">
                                <span v-text="u.maks.length + ' ' + teksPertanyaan"></span> · {{ __('total') }} <span v-text="u.total_maks"></span>
                                · <span v-text="u.jumlah_nilai"></span> {{ __('nilai') }}
                            </p>
                        </div>
                        <button type="button" @click="aktifkanUjian(u)" class="px-2.5 py-1.5 rounded-xl text-xs font-bold ring-1"
                            :class="u.is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-50 text-slate-500 ring-slate-200'">
                            <template v-if="u.is_active">{{ __('Aktif') }}</template><template v-else>{{ __('Mati') }}</template>
                        </button>
                        <button type="button" @click="ubahUjian(u)" class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-violet-700 hover:bg-violet-50">{{ __('Ubah') }}</button>
                        <button type="button" @click="hapusUjian(u)" class="px-2.5 py-1.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50">{{ __('Hapus') }}</button>
                    </li>
                    <li v-if="!ujianLisan.length" class="px-3 py-4 text-center text-xs text-slate-400">{{ __('Belum ada ujian. Isi form di bawah.') }}</li>
                </ul>
            </div>

            {{-- Form tambah / ubah --}}
            <form class="rounded-2xl bg-violet-50/50 ring-1 ring-violet-100 p-4 space-y-4" @submit.prevent="simpanUjian">
                <p class="text-sm font-bold text-violet-800">
                    <template v-if="formUjian.id">{{ __('Ubah ujian') }}</template><template v-else>{{ __('Tambah ujian') }}</template>
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Fan / kategori') }}</span>
                        <select v-model="formUjian.bidang_ilmu_id" @change="formUjian.kitab_id = ''" required class="mt-1 {{ $inputLisan }}">
                            <option value="">{{ __('— Pilih fan —') }}</option>
                            <option v-for="f in fanList" :key="f.id" :value="f.id" v-text="f.nama"></option>
                        </select></label>
                    <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Kitab / pelajaran') }}</span>
                        <select v-model="formUjian.kitab_id" class="mt-1 {{ $inputLisan }}">
                            <option value="">{{ __('Tanpa kitab (seluruh fan)') }}</option>
                            <option v-for="k in kitabDiFan" :key="k.id" :value="k.id" v-text="k.nama"></option>
                        </select></label>
                    <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Jumlah pertanyaan') }}</span>
                        <input type="number" min="1" max="{{ \App\Models\UjianLisan::MAKS_PERTANYAAN }}" v-model.number="formUjian.jumlah" @input="aturJumlah($event.target.value)" required class="mt-1 {{ $inputLisan }}"></label>
                    <label class="block"><span class="text-xs font-semibold text-slate-500">{{ __('Nilai tiap pertanyaan (semua)') }}</span>
                        <input type="number" min="1" max="{{ \App\Models\UjianLisan::MAKS_NILAI }}" v-model.number="formUjian.rata" @input="isiSemuaNilai($event.target.value)" class="mt-1 {{ $inputLisan }}"></label>
                </div>

                <div v-if="formUjian.maks.length">
                    <p class="text-xs font-semibold text-slate-500">{{ __('Nilai maksimal per pertanyaan (boleh dibedakan)') }}</p>
                    <div class="mt-1.5 grid grid-cols-3 sm:grid-cols-5 gap-2">
                        <label v-for="(m, i) in formUjian.maks" :key="i" class="block">
                            <span class="text-[11px] text-slate-400" v-text="'P' + (i + 1)"></span>
                            <input type="number" min="1" max="{{ \App\Models\UjianLisan::MAKS_NILAI }}" v-model.number="formUjian.maks[i]" required class="w-full rounded-xl bg-white ring-1 ring-slate-200/80 border-0 px-2 py-2 text-sm font-semibold text-slate-700 text-center focus:ring-2 focus:ring-violet-500 outline-none">
                        </label>
                    </div>
                    <p class="mt-2 text-sm text-slate-600">{{ __('Total nilai maksimal') }}: <b class="text-violet-700" v-text="totalFormUjian"></b></p>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" v-model="formUjian.is_active" class="rounded text-violet-600">
                    {{ __('Aktif (muncul di dasbor asatid)') }}
                </label>

                <div class="flex justify-end gap-2">
                    <button v-if="formUjian.id" type="button" @click="formUjianBaru()" class="px-4 py-2 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal ubah') }}</button>
                    <button :disabled="menyimpan" class="px-5 py-2 rounded-2xl bg-violet-600 text-white text-sm font-bold hover:bg-violet-700 disabled:opacity-50">
                        <template v-if="menyimpan">{{ __('Menyimpan...') }}</template>
                        <template v-else-if="formUjian.id">{{ __('Simpan perubahan') }}</template>
                        <template v-else>{{ __('Tambah ujian') }}</template>
                    </button>
                </div>
            </form>
        </div>
        <div class="border-t border-slate-100 px-6 py-4 text-right">
            <button type="button" @click="modalAturLisan = false" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347]">{{ __('Selesai') }}</button>
        </div>
    </div>
</div>

{{-- ===== Modal: Beri Nilai Ujian Lisan (admin) ===== --}}
<div v-if="modalNilaiLisan" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-gray-900/40 p-4 sm:items-center" @mousedown.self="modalNilaiLisan = false">
    <form class="w-full max-w-lg rounded-3xl bg-white shadow-xl" @submit.prevent="simpanNilaiLisan">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <h3 class="text-base font-extrabold text-slate-800">{{ __('Beri Nilai') }} — {{ __('Ujian Lisan') }}</h3>
            <button type="button" @click="modalNilaiLisan = false" class="w-8 h-8 rounded-xl text-slate-400 hover:bg-slate-100 flex items-center justify-center" aria-label="{{ __('Tutup') }}">{!! $tutupIkon !!}</button>
        </div>

        <div class="p-6 space-y-5">
            {{-- 1. Santri --}}
            <div>
                <p class="text-sm font-bold text-slate-700"><span class="text-slate-400">1.</span> {{ __('Nama santri') }}</p>
                <div v-if="santriLisan" class="mt-2 flex items-center gap-3 rounded-2xl bg-violet-50 ring-1 ring-violet-200 p-2.5">
                    <img v-if="santriLisan.foto" :src="santriLisan.foto" alt="" class="w-10 h-10 rounded-xl object-cover">
                    <span v-else class="w-10 h-10 rounded-xl bg-violet-600 text-white font-bold flex items-center justify-center" v-text="inisial(santriLisan.siswa)"></span>
                    <span class="flex-1 min-w-0">
                        <span class="block font-semibold text-slate-800 truncate" v-text="santriLisan.siswa"></span>
                        <x-nama-arab vue="santriLisan.arab" />
                        <span v-if="santriLisan.kelas" class="block text-xs text-slate-500 truncate" v-text="santriLisan.kelas"></span>
                    </span>
                    <button type="button" @click="formLisan.siswa_id = null" class="px-3 py-1.5 rounded-xl text-xs font-bold text-violet-700 hover:bg-violet-100">{{ __('Ganti') }}</button>
                </div>
                <template v-else>
                    <input type="search" v-model="cariSantriLisan" placeholder="{{ __('Cari nama / nomor santri...') }}" class="mt-2 {{ $inputLisan }}">
                    <ul class="mt-2 max-h-56 overflow-y-auto rounded-2xl ring-1 ring-slate-100 divide-y divide-slate-100">
                        <li v-for="s in santriLisanCari" :key="s.siswa_id">
                            <button type="button" @click="pilihSantriLisan(s.siswa_id)" class="w-full flex items-center gap-3 px-3 py-2 text-left hover:bg-slate-50">
                                <img v-if="s.foto" :src="s.foto" alt="" class="w-8 h-8 rounded-lg object-cover">
                                <span v-else class="w-8 h-8 rounded-lg bg-slate-200 text-slate-600 text-sm font-bold flex items-center justify-center" v-text="inisial(s.siswa)"></span>
                                <span class="flex-1 min-w-0">
                                    <span class="block text-sm font-semibold text-slate-800 truncate" v-text="s.siswa"></span>
                                    <x-nama-arab vue="s.arab" kelas="text-xs text-slate-500" />
                                    <span v-if="s.kelas" class="block text-[11px] text-slate-400 truncate" v-text="s.kelas"></span>
                                </span>
                            </button>
                        </li>
                        <li v-if="!santriLisanCari.length" class="px-3 py-4 text-center text-xs text-slate-400">{{ __('Santri tidak ditemukan.') }}</li>
                    </ul>
                </template>
            </div>

            {{-- 2. Fan yang diujikan --}}
            <div>
                <p class="text-sm font-bold text-slate-700"><span class="text-slate-400">2.</span> {{ __('Fan yang diujikan') }}</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button v-for="u in ujianLisanAktif" :key="u.id" type="button" @click="pilihUjianLisan(u.id)"
                        class="px-3.5 py-2 rounded-xl text-sm font-semibold ring-1 transition"
                        :class="formLisan.ujian_lisan_id === u.id ? 'bg-violet-600 text-white ring-violet-600' : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'"
                        v-text="judulUjian(u)"></button>
                </div>
                <p v-if="!ujianLisanAktif.length" class="mt-1 text-xs text-amber-600">{{ __('Belum ada ujian aktif. Tambahkan dulu lewat tombol Pengaturan Ujian Lisan.') }}</p>
            </div>

            {{-- 3. Tanggal --}}
            <div>
                <p class="text-sm font-bold text-slate-700"><span class="text-slate-400">3.</span> {{ __('Tanggal') }}</p>
                <input type="date" v-model="formLisan.tanggal" @change="isiNilaiLama" required class="mt-2 {{ $inputLisan }}">
            </div>

            {{-- 4. Nilai tiap pertanyaan --}}
            <div v-if="ujianLisanTerpilih">
                <p class="text-sm font-bold text-slate-700"><span class="text-slate-400">4.</span> {{ __('Nilai tiap pertanyaan') }}</p>
                <p v-if="adaNilaiLama" class="mt-1 text-xs text-amber-600">{{ __('Santri ini sudah dinilai pada tanggal ini. Menyimpan akan memperbarui nilainya.') }}</p>
                <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <label v-for="(m, i) in ujianLisanTerpilih.maks" :key="i" class="block rounded-2xl bg-[#f8fbfa] ring-1 ring-slate-200 p-2.5">
                        <span class="block text-xs font-semibold text-slate-500"><span v-text="teksPertanyaanKe + ' ' + (i + 1)"></span> <span class="text-slate-400" v-text="'(' + teksMaks + ' ' + m + ')'"></span></span>
                        <input type="number" min="0" :max="m" inputmode="numeric" :value="formLisan.nilai[i]" @input="batasiNilai(i, m, $event)" required
                            class="mt-1 w-full rounded-xl bg-white ring-1 ring-slate-200 border-0 px-3 py-2 text-lg font-bold text-slate-800 text-center focus:ring-2 focus:ring-violet-500 outline-none">
                    </label>
                </div>
                <p class="mt-3 text-sm text-slate-600">{{ __('Total') }}: <b class="text-violet-700" v-text="totalNilaiLisan + ' / ' + ujianLisanTerpilih.total_maks"></b></p>
            </div>

            {{-- 5. Catatan --}}
            <div v-if="ujianLisanTerpilih">
                <p class="text-sm font-bold text-slate-700"><span class="text-slate-400">5.</span> {{ __('Catatan') }} <span class="font-normal text-slate-400">({{ __('boleh dikosongkan') }})</span></p>
                <textarea v-model="formLisan.catatan" rows="3" maxlength="5000" dir="auto" class="mt-2 {{ $inputLisan }}"></textarea>
            </div>
        </div>

        <div class="border-t border-slate-100 px-6 py-4 flex justify-end gap-2">
            <button type="button" @click="modalNilaiLisan = false" class="px-5 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50">{{ __('Batal') }}</button>
            <button :disabled="menyimpan || !formLisan.siswa_id || !ujianLisanTerpilih" class="px-5 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-bold hover:bg-[#135347] disabled:opacity-50">
                <template v-if="menyimpan">{{ __('Menyimpan...') }}</template>
                <template v-else>{{ __('Simpan Nilai') }}</template>
            </button>
        </div>
    </form>
</div>
