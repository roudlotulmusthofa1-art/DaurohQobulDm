{{--
    Dasbor admin > Pergantian Pelajaran (menggantikan panel "Jadwal Hari Ini"; dipakai di dashboard laptop & HP).
    Jadwal hari ini per jam ke: Sekarang / Berikutnya / Nanti / Selesai, status tiap guru (absen, tanda dikirim, dilihat),
    tombol "Kirim tanda ke semua guru jam ini" dan "Tanda" per guru. Diperbarui sendiri tiap 30 detik.
    Data: Admin\PergantianPelajaranController::susun(). Skrip Vue: partials/pergantian-pelajaran-skrip (dimuat terpisah,
    karena panel ini berada di dalam aplikasi Vue dasbor -> skrip melepas panel sebelum Vue dasbor berjalan lalu memasangnya
    kembali. Jangan pakai v-pre: browser HP lama menolak atribut @click/:class yang disalin v-pre, dasbor jadi kosong).
    Vue: #pergantian-pelajaran.
--}}
<div id="tempat-pergantian-pelajaran">
<section id="pergantian-pelajaran" v-cloak class="{{ $kelasKartu ?? 'bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-sm' }} p-4 sm:p-6">
    <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
            <h2 class="font-bold text-slate-800">{{ __('Pergantian Pelajaran') }}</h2>
            <p class="text-xs text-slate-400">{{ now('Asia/Jakarta')->locale('id')->translatedFormat('l') }} · <span v-text="jumlahJadwal"></span> {{ __('jadwal') }} · {{ __('diperbarui') }} <span v-text="diperbarui"></span></p>
        </div>
        <a href="{{ \Illuminate\Support\Facades\Route::has('admin.jadwal.index') ? route('admin.jadwal.index') : '#' }}" class="shrink-0 text-xs font-semibold text-emerald-700 hover:underline">{{ __('Jadwal Mengajar') }}</a>
    </div>

    <p v-if="!jam.length" class="py-8 text-center text-sm text-slate-400">{{ __('Tidak ada jadwal mengajar hari ini.') }}</p>

    <div v-else class="mt-3 space-y-3 max-h-[520px] overflow-y-auto pr-1">
        <div v-for="j in jam" :key="j.waktu_id" class="rounded-2xl ring-1 overflow-hidden"
            :class="j.keadaan === 'sekarang' ? 'ring-emerald-300 bg-emerald-50/40' : (j.keadaan === 'lewat' ? 'ring-slate-100 opacity-70' : 'ring-slate-200')">
            {{-- Kepala jam --}}
            <div class="flex flex-wrap items-center gap-2 px-3 py-2.5 bg-white/70">
                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-bold" :class="AKSEN[j.keadaan]" v-text="LABEL[j.keadaan]"></span>
                <span class="font-bold text-slate-800 text-sm" dir="auto" v-text="j.jam"></span>
                <span v-if="j.mulai" class="text-xs text-slate-400 tabular-nums" v-text="j.mulai + (j.selesai ? '–' + j.selesai : '')"></span>
                <button type="button" @click="kirim({ waktu_id: j.waktu_id }, 'jam' + j.waktu_id)" :disabled="!!mengirim || !j.baris.some((b) => b.ada_guru)"
                    class="ml-auto inline-flex items-center gap-1.5 rounded-xl bg-[#0b332c] px-3 py-1.5 text-xs font-bold text-white hover:bg-[#135347] disabled:opacity-40">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                    <span v-text="mengirim === 'jam' + j.waktu_id ? @js(__('Mengirim…')) : @js(__('Kirim tanda ke semua guru jam ini'))"></span>
                </button>
            </div>
            {{-- Guru di jam ini --}}
            <ul class="divide-y divide-slate-100 bg-white">
                <li v-for="b in j.baris" :key="b.jadwal_id" class="flex items-center gap-3 px-3 py-2.5">
                    <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-100" v-text="b.kelas || '-'"></span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-800" v-text="b.kitab || @js(__('Tanpa kitab'))"></p>
                        <p class="truncate text-[11px] text-slate-500" v-text="b.ustadz || @js(__('Ustadz belum ditentukan'))"></p>
                        <x-nama-arab vue="b.arab" kelas="text-xs text-slate-500" />
                        <p class="mt-1 flex flex-wrap gap-1 text-[10px] font-semibold">
                            <span class="rounded-full px-2 py-0.5 ring-1" :class="b.absen ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-50 text-slate-400 ring-slate-200'"
                                v-text="b.absen ? @js(__('Sudah absen')) : @js(__('Belum absen'))"></span>
                            <span v-if="b.dikirim" class="rounded-full bg-amber-50 px-2 py-0.5 text-amber-700 ring-1 ring-amber-200" v-text="@js(__('Tanda terkirim')) + ' ' + b.dikirim"></span>
                            <span v-if="b.dilihat" class="rounded-full bg-sky-50 px-2 py-0.5 text-sky-700 ring-1 ring-sky-200" v-text="@js(__('Dilihat guru')) + ' ' + b.dilihat"></span>
                            <span v-else-if="b.dikirim" class="rounded-full bg-slate-50 px-2 py-0.5 text-slate-400 ring-1 ring-slate-200">{{ __('Belum dilihat') }}</span>
                        </p>
                    </div>
                    <button v-if="b.ada_guru" type="button" @click="kirim({ jadwal_id: b.jadwal_id }, 'j' + b.jadwal_id)" :disabled="!!mengirim"
                        :title="@js(__('Kirim tanda ganti pelajaran ke guru ini'))"
                        class="shrink-0 rounded-xl bg-white px-3 py-1.5 text-xs font-bold text-[#0b332c] ring-1 ring-slate-200 hover:bg-slate-50 disabled:opacity-40"
                        v-text="mengirim === 'j' + b.jadwal_id ? '…' : @js(__('Tanda'))"></button>
                </li>
            </ul>
        </div>
    </div>
</section>
</div>
