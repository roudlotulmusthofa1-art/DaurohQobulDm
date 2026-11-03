{{-- Admin > Detail pendaftar: form Persyaratan & catatan admin (disisipkan lewat partials/detail-santri) --}}
        {{-- Persyaratan & catatan admin --}}
        <section class="{{ $kartu }} p-5 sm:p-6 xl:col-span-2">
            {{-- Form ini TANPA @method: tombol Terima di atas ikut memakainya lewat atribut form="form-syarat" --}}
            <form id="form-syarat" method="POST" action="{{ route('admin.pendaftaran.syarat', $p) }}">
                @csrf

                <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                    {!! $judulKartu('Persyaratan & Ketentuan Admin', 'badge', 'emerald', 'Centang syarat yang sudah dipenuhi, lalu simpan') !!}
                    <div class="flex items-center gap-3">
                        <div class="hidden sm:block w-28 h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-teal-500" style="width: {{ $persenSyarat }}%"></div>
                        </div>
                        <span @class([ 'text-xs font-semibold px-3 py-1 rounded-full ring-1' ,
                            'bg-emerald-50 text-emerald-700 ring-emerald-100'=> $p->syarat_terpenuhi === $totalSyarat,
                            'bg-amber-50 text-amber-700 ring-amber-100' => $p->syarat_terpenuhi !== $totalSyarat,
                            ])>
                            {{ $p->syarat_terpenuhi }}/{{ $totalSyarat }} terpenuhi
                        </span>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($p::SYARAT as $kolom => $nama)
                    @php $kolomBerkas = $p::SYARAT_BERKAS[$kolom] ?? null; @endphp

                    <div
                        class="rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-100 p-3.5 transition hover:ring-emerald-300 has-[:checked]:bg-emerald-50 has-[:checked]:ring-emerald-300">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="{{ $kolom }}" value="1" @checked($p->$kolom)
                            class="h-5 w-5 shrink-0 rounded accent-emerald-600 cursor-pointer">
                            <span class="text-sm font-semibold text-slate-700">{{ $nama }}</span>
                        </label>

                        @if ($kolomBerkas && $p->$kolomBerkas)
                        <div class="ml-8 mt-1">
                            <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($p->$kolomBerkas) }}" target="_blank" rel="noopener"
                                class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 hover:underline">{!! $svg('open', 'w-3 h-3') !!} Lihat berkas yang diunggah</a>
                        </div>
                        @elseif ($kolomBerkas)
                        <p class="ml-8 mt-1 text-xs text-slate-400">Belum ada berkas unggahan</p>
                        @endif
                    </div>
                    @endforeach

                    {{-- No paspor --}}
                    <div class="rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-100 p-3.5">
                        <label for="syarat_no_paspor" class="block text-sm font-semibold text-slate-700">No Paspor</label>
                        <input type="number" min="1" max="200" id="syarat_no_paspor" name="syarat_no_paspor"
                            value="{{ old('syarat_no_paspor', $p->syarat_no_paspor) }}"
                            class="mt-2 w-full bg-white border border-slate-200 rounded-xl py-2 px-4 text-sm focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/20">
                        @error('syarat_no_paspor')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kelas: pilihan dari Master Data > Kelas --}}
                    <div class="rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-100 p-3.5">
                        <label for="kelas_id" class="block text-sm font-semibold text-slate-700">Kelas</label>
                        <select id="kelas_id" name="kelas_id"
                            class="mt-2 w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/20">
                            <option value="">Pilih kelas...</option>
                            @foreach ($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" @selected((string) old('kelas_id', $p->kelas_id) === (string) $kelas->id)>
                                {{ $kelas->nama }}{{ $kelas->kode ? ' (' . $kelas->kode . ')' : '' }}{{ $kelas->is_active ? '' : ' — nonaktif' }}
                            </option>
                            @endforeach
                        </select>
                        @if ($kelasList->isEmpty())
                        <p class="text-xs text-slate-400 mt-1">Belum ada kelas. Tambahkan di
                            <a href="{{ route('admin.master-data.index', ['tab' => 'kelas']) }}" class="text-emerald-700 hover:underline">Master Data &rsaquo; Kelas</a>.</p>
                        @endif
                        @error('kelas_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Kamar: pilihan dari Master Data > Kamar, dengan jumlah isi / kapasitas --}}
                    <div class="rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-100 p-3.5 text-right" dir="rtl">
                        <label for="kamar_id" class="block text-sm font-semibold text-slate-700">Kamar</label>
                        <select id="kamar_id" name="kamar_id" v-model="kamarId"
                            class="mt-2 w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/20">
                            <option value="">Pilih kamar...</option>
                            @foreach ($kamarList as $kamar)
                            <option value="{{ $kamar->id }}" @selected((string) old('kamar_id', $p->kamar_id) === (string) $kamar->id)>
                                {{ $kamar->label_isi }}{{ $kamar->is_active ? '' : ' — nonaktif' }}
                            </option>
                            @endforeach
                        </select>
                        @if ($kamarList->isEmpty())
                        <p class="text-xs text-slate-400 mt-1">Belum ada kamar. Tambahkan di
                            <a href="{{ route('admin.master-data.index', ['tab' => 'kamar']) }}" class="text-emerald-700 hover:underline">Master Data &rsaquo; Kamar</a>.</p>
                        @endif
                        @error('kamar_id')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nomor kasur: pilihan mengikuti kamar yang dipilih (Vue: computed "pilihanKasur") --}}
                    <div class="rounded-2xl bg-[#f5f9f7] ring-1 ring-slate-100 p-3.5">
                        <label for="nomor_kasur" class="block text-sm font-semibold text-slate-700">Nomor Kasur</label>
                        <select id="nomor_kasur" name="nomor_kasur" v-model="nomorKasur" :disabled="!kamarTerpilih || kamarTerpilih.kapasitas < 1"
                            class="mt-2 w-full bg-white border border-slate-200 rounded-xl py-2 px-3 text-sm focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/20 disabled:bg-slate-50 disabled:text-slate-400">
                            <option value="">Belum ditentukan</option>
                            {{-- Kasur yang sudah ditempati santri lain tampil dengan namanya dan tidak bisa dipilih --}}
                            <option v-for="k in pilihanKasur" :key="k.nomor" :value="String(k.nomor)" :disabled="!!k.penghuni"
                                v-text="k.penghuni ? 'Kasur ' + k.nomor + ' — ' + k.penghuni : 'Kasur ' + k.nomor"></option>
                        </select>
                        <p class="text-xs text-slate-400 mt-1" v-text="keteranganKasur"></p>
                        @error('nomor_kasur')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-5">
                    <label for="catatan_admin" class="block text-sm font-semibold text-slate-700 mb-1.5">Catatan Admin untuk Pendaftar</label>
                    <textarea id="catatan_admin" name="catatan_admin" rows="3" dir="auto"
                        placeholder="Contoh: uang pendaftaran masih kurang, mohon dilunasi sebelum berangkat"
                        class="w-full bg-[#f5f9f7] border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:bg-white focus:outline-none focus:border-emerald-400 focus:ring-2 focus:ring-emerald-500/20 transition">{{ old('catatan_admin', $p->catatan_admin) }}</textarea>
                    <p class="text-xs text-slate-400 mt-1.5">Catatan ini ikut terkirim ke WhatsApp peserta saat siswa
                        diterima atau ditolak.</p>
                    @error('catatan_admin')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-semibold hover:bg-[#135347] shadow-lg shadow-[#0b332c]/20 transition-colors">
                        {!! $svg('check', 'w-4 h-4') !!} Simpan Persyaratan &amp; Ketentuan
                    </button>
                </div>
            </form>
        </section>
