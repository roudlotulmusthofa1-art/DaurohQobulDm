<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ __('Pendaftaran Masuk Dauroh Qobul Darul Musthofa Tarim') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
            rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])

    </head>

    <body class="min-h-screen text-slate-800 bg-[#eef4f1] antialiased">

        {{-- ================= HEADER (hijau tua) ================= --}}
        <header class="relative overflow-hidden bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white pt-10 pb-28 sm:pt-14 sm:pb-32">
            <div class="pointer-events-none absolute inset-0 opacity-60"
                style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
            <div class="pointer-events-none absolute -top-32 -right-24 w-96 h-96 rounded-full bg-emerald-400/25 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-40 -left-20 w-96 h-96 rounded-full bg-amber-300/10 blur-3xl"></div>

            <div class="relative max-w-5xl mx-auto px-4 text-center">
                <div class="mx-auto w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-[#eff5f0] ring-4 ring-white/10 shadow-xl shadow-black/30 flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('images/Logo dar4.png') }}" alt="{{ __('Logo Dauroh Qobul') }}" class="w-full h-full object-contain">
                </div>
                <p class="mt-5 text-[11px] sm:text-xs font-semibold uppercase tracking-[0.22em] text-amber-200/90">{{ __('Dauroh Qobul · Darul Musthofa Tarim') }}</p>
                <h1 class="mt-2 text-3xl sm:text-5xl font-extrabold tracking-tight">{{ __('Pendaftaran Peserta Baru') }}</h1>
                <p class="mt-3 text-sm sm:text-lg text-emerald-50/75 max-w-2xl mx-auto">{{ __('Isi 6 langkah berikut dengan data yang benar') }}</p>

                <div class="mt-6 flex flex-wrap items-center justify-center gap-2 text-xs sm:text-sm">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-white/15 px-3.5 py-1.5">
                        <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        {{ __('6 langkah') }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-white/15 px-3.5 py-1.5">
                        <svg class="w-4 h-4 text-amber-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776"/></svg>
                        {{ __('Siapkan paspor & berkas') }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/10 ring-1 ring-white/15 px-3.5 py-1.5">
                        <svg class="w-4 h-4 text-cyan-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802"/></svg>
                        {{ __('Banyak isian ditulis huruf Arab') }}
                    </span>
                </div>
            </div>
        </header>

        <main class="relative -mt-20 sm:-mt-24 max-w-5xl mx-auto px-4 pb-14">
                <div id="app">
                    <!-- Indikator step -->
                    <div class="bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_20px_48px_-24px_rgba(7,31,26,0.35)] p-4 sm:p-6 mb-6">
                        <!-- Ringkasan untuk layar kecil -->
                        <div class="sm:hidden flex items-center justify-between mb-4">
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600">{{ __('Langkah') }} @{{ step + 1 }} {{ __('dari') }} @{{ steps.length }}</p>
                                <p class="font-bold text-slate-800">@{{ steps[step] }}</p>
                            </div>
                            <span class="text-xs font-bold text-[#0b332c] bg-emerald-50 ring-1 ring-emerald-100 rounded-full px-3 py-1">@{{ Math.round((step + 1) / steps.length * 100) }}%</span>
                        </div>

                        <ol class="flex items-start justify-between">
                            <li v-for="(judulLangkah, index) in steps" :key="index"
                                class="flex-1 flex flex-col items-center relative">
                                <div v-if="index > 0" class="absolute top-[18px] sm:top-[22px] right-1/2 w-full h-1 rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-500"
                                        :class="step >= index ? 'w-full' : 'w-0'"></div>
                                </div>
                                <div class="relative z-10 w-9 h-9 sm:w-11 sm:h-11 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300"
                                    :class="step > index ?
                                        'bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md shadow-emerald-500/30' :
                                        step === index ?
                                        'bg-[#0b332c] text-white ring-4 ring-emerald-200 shadow-lg shadow-emerald-900/20 scale-110' :
                                        'bg-white ring-2 ring-slate-200 text-slate-400'">
                                    <svg v-if="step > index" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    <span v-else>@{{ index + 1 }}</span>
                                </div>
                                <span class="mt-2.5 text-[12px] leading-tight text-center px-1 hidden sm:block"
                                    :class="step === index ?
                                        'text-[#0b332c] font-bold' :
                                        step > index ? 'text-emerald-700 font-medium' : 'text-slate-400'">@{{ judulLangkah }}</span>
                            </li>
                        </ol>
                    </div>

                    <div class="bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(11,51,44,0.25)] overflow-hidden">
                        <!-- Judul langkah -->
                        <div class="relative px-5 sm:px-8 py-5 sm:py-6 bg-gradient-to-r from-emerald-50 via-white to-amber-50/50 border-b border-slate-100 flex items-center gap-4">
                            <span class="w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/25">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="[
                                        'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
                                        'M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z',
                                        'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
                                        'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25',
                                        'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z',
                                        'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5'
                                    ][step]" />
                                </svg>
                            </span>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-600">{{ __('Langkah') }} @{{ step + 1 }} {{ __('dari') }} @{{ steps.length }}</p>
                                <h2 class="text-lg sm:text-2xl font-extrabold text-slate-800 leading-tight">@{{ steps[step] }}</h2>
                                <p class="text-xs sm:text-sm text-slate-500 mt-0.5" v-text="[@js(__('Isi identitas diri sesuai paspor.')), @js(__('Tempat tinggal Anda saat ini.')), @js(__('Kontak wali, muarrif, dan orang yang bisa dihubungi.')), @js(__('Pondok, sekolah, dan kitab yang pernah dipelajari.')), @js(__('Rencana belajar dan akun login Anda.')), @js(__('Unggah berkas, lalu kirim pendaftaran.'))][step]"></p>
                            </div>
                        </div>

                        <div class="p-5 sm:p-8">
                        {{-- Pesan gagal kirim tampil sebagai notifikasi pojok kanan bawah (watch serverError) --}}

                        <!-- STEP 1 -->
                        <div v-show="step === 0" class="grid sm:grid-cols-2 gap-x-5 gap-y-5">
                            <div class="sm:col-span-2">
                                <label class="lbl">{{ __('Nama Lengkap Bahasa Indonesia (sesuai paspor)') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="f.nama_lengkap" class="inp"
                                    placeholder="{{ __('Sesuai nama yang tertera di paspor') }}">
                                <p class="err" v-if="e.nama_lengkap">@{{ e . nama_lengkap }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="lbl">
                                    {{ __('Nama Lengkap Bahasa Arab (sesuai paspor)') }}
                                    <span class="text-rose-500">*</span>
                                </label>

                                <input v-model="f.nama_lengkap_arab" class="inp"
                                    placeholder="اكتب الاسم الكامل باللغة العربية" dir="auto"
                                    @input="validasiHurufArab(f.nama_lengkap_arab, 'nama_lengkap_arab')">

                                <p class="err" v-if="e.nama_lengkap_arab">
                                    @{{ e . nama_lengkap_arab }}
                                </p>
                            </div>

                            <div>
                                <label class="lbl">{{ __('Tempat Lahir') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="f.tempat_lahir" @input="validasiHurufArab(f.tempat_lahir, 'tempat_lahir')"
                                    class="inp" placeholder="اكتب مكان الميلاد باللغة العربية" dir="auto">
                                <p class="err" v-if="e.tempat_lahir">@{{ e . tempat_lahir }}</p>
                            </div>
                            <div>
                                <label class="lbl">{{ __('Tanggal Lahir') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" v-model="f.tanggal_lahir" class="inp">
                                <p class="err" v-if="e.tanggal_lahir">@{{ e . tanggal_lahir }}</p>
                            </div>
                            <div>
                                <label class="lbl">{{ __('Status Pernikahan') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <select v-model="f.status_pernikahan" class="inp">
                                    <option value="">{{ __('Pilih...') }}</option>
                                    <option value="K">متزوج</option>
                                    <option value="L">عازب</option>
                                </select>
                                <p class="err" v-if="e.status_pernikahan">@{{ e . status_pernikahan }}</p>
                            </div>
                            <div>
                                <label class="lbl">{{ __('Nomer WhatsApp') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="f.no_hp" type="tel" inputmode="tel" placeholder="+6281234567890"
                                    class="inp">
                                <p class="err" v-if="e.no_hp">@{{ e . no_hp }}</p>
                            </div>
                        </div>

                        <!-- STEP 2 -->
                        <div v-show="step === 1" class="grid sm:grid-cols-2 gap-x-5 gap-y-5">

                            <div>
                                <label class="lbl">{{ __('Provinsi') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="f.provinsi" @input="validasiHurufArab(f.provinsi, 'provinsi')"
                                    class="inp" placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto">
                                <p class="err" v-if="e.provinsi">@{{ e . provinsi }}</p>
                            </div>
                            <div>
                                <label class="lbl">{{ __('Kota/Kabupaten') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="f.kota" @input="validasiHurufArab(f.kota, 'kota')" class="inp"
                                    placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto">
                                <p class="err" v-if="e.kota">@{{ e . kota }}</p>
                            </div>
                            <div>
                                <label class="lbl">{{ __('Kecamatan') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <input v-model="f.kecamatan" @input="validasiHurufArab(f.kecamatan, 'kecamatan')"
                                    class="inp" placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto">
                                <p class="err" v-if="e.kecamatan">@{{ e . kecamatan }}</p>
                            </div>
                            <div>
                                <label class="lbl">{{ __('Kelurahan/Desa') }}</label>
                                <input v-model="f.kelurahan" @input="validasiHurufArab(f.kelurahan, 'kelurahan')"
                                    class="inp" placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto">
                                <p class="err" v-if="e.kelurahan">@{{ e . kelurahan }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="lbl">{{ __('Alamat Lengkap') }}
                                    <span class="text-rose-500">*</span>
                                </label>
                                <textarea v-model="f.alamat" @input="validasiHurufArab(f.alamat, 'alamat')" class="inp h-28"
                                    placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto"></textarea>
                                <p class="err" v-if="e.alamat">@{{ e . alamat }}</p>
                            </div>
                        </div>

                        <!-- STEP 3 -->
                        <div v-show="step === 2" class="space-y-6">
                            <!-- Wali -->
                            <div class="rounded-2xl ring-1 ring-amber-100 bg-gradient-to-br from-amber-50/70 to-white p-4 sm:p-5">
                                <p class="sub-judul"><span class="sub-ikon bg-amber-400">W</span> {{ __('Data Wali') }}</p>
                                <div class="grid sm:grid-cols-2 gap-x-5 gap-y-5">
                                    <div>
                                        <label class="lbl">{{ __('Nama Wali') }}
                                            <span class="text-rose-500">*</span>
                                        </label>
                                        <input v-model="f.nama_wali" @input="validasiHurufArab(f.nama_wali, 'nama_wali')"
                                            class="inp" placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto">
                                        <p class="err" v-if="e.nama_wali">@{{ e . nama_wali }}</p>
                                    </div>
                                    <div>
                                        <label class="lbl">{{ __('Pekerjaan Wali') }}</label>
                                        <input v-model="f.pekerjaan_wali"
                                            @input="validasiHurufArab(f.pekerjaan_wali, 'pekerjaan_wali')" class="inp"
                                            placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto">
                                        <p class="err" v-if="e.pekerjaan_wali">@{{ e . pekerjaan_wali }}</p>
                                    </div>
                                    <div>
                                        <label class="lbl">{{ __('Hubungan Dengan Wali') }}</label>
                                        <input v-model="f.hubungan_wali"
                                            @input="validasiHurufArab(f.hubungan_wali, 'hubungan_wali')"
                                            placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" class="inp">
                                        <p class="err" v-if="e.hubungan_wali">@{{ e . hubungan_wali }}</p>
                                    </div>

                                    <div>
                                        <label class="lbl">{{ __('No. WhatsApp Wali') }}
                                            <span class="text-rose-500">*</span>
                                        </label>
                                        <input v-model="f.no_hp_wali" inputmode="tel" placeholder="+6281234567890" dir="ltr" class="inp">
                                        <p class="hint">{{ __('Info penerimaan akan dikirim ke nomor ini.') }}</p>
                                        <p class="err" v-if="e.no_hp_wali">@{{ e . no_hp_wali }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Muarrif -->
                            <div class="rounded-2xl ring-1 ring-emerald-100 bg-gradient-to-br from-emerald-50/70 to-white p-4 sm:p-5">
                                <p class="sub-judul"><span class="sub-ikon bg-emerald-500">M</span> {{ __('Muarrif (Pemberi Rekomendasi)') }}</p>
                                <div class="grid sm:grid-cols-2 gap-x-5 gap-y-5">
                                    <div>
                                        <label class="lbl">{{ __('Nama Muarrif') }}
                                            <span class="text-rose-500">*</span>
                                        </label>
                                        <input v-model="f.nama_muarrif" @input="validasiHurufArab(f.nama_muarrif, 'nama_muarrif')"
                                            class="inp" placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto">
                                        <p class="err" v-if="e.nama_muarrif">@{{ e . nama_muarrif }}</p>
                                    </div>
                                    <div>
                                        <label class="lbl">{{ __('No. WhatsApp Muarrif') }}
                                            <span class="text-rose-500">*</span>
                                        </label>
                                        <input v-model="f.no_hp_muarrif" inputmode="tel" placeholder="+6281234567890" dir="ltr"
                                            class="inp">
                                        <p class="err" v-if="e.no_hp_muarrif">@{{ e . no_hp_muarrif }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Kontak darurat -->
                            <div class="rounded-2xl ring-1 ring-cyan-100 bg-gradient-to-br from-cyan-50/70 to-white p-4 sm:p-5">
                                <p class="sub-judul"><span class="sub-ikon bg-cyan-500">K</span> {{ __('Kontak yang Bisa Dihubungi') }}</p>
                                <div class="grid sm:grid-cols-2 gap-x-5 gap-y-5">
                                    <div>
                                        <label class="lbl">{{ __('Nama Yang Bisa Di Hubungi Jika Diperlukan') }}
                                            <span class="text-rose-500">*</span>
                                        </label>
                                        <input v-model="f.nama_butuh" @input="validasiHurufArab(f.nama_butuh, 'nama_butuh')"
                                            class="inp" placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto">
                                        <p class="err" v-if="e.nama_butuh">@{{ e . nama_butuh }}</p>
                                    </div>
                                    <div>
                                        <label class="lbl">{{ __('No. WhatsApp Nya') }}
                                            <span class="text-rose-500">*</span>
                                        </label>
                                        <input v-model="f.no_hp_nya" inputmode="tel" placeholder="+6281234567890" dir="ltr" class="inp">
                                        <p class="err" v-if="e.no_hp_nya">@{{ e . no_hp_nya }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- STEP 4 -->
                        <div v-show="step === 3" class="space-y-8">

                            <!-- A. Pondok -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="sub-judul !mb-0"><span class="sub-ikon bg-amber-400">A</span> {{ __('Riwayat Pendidikan Pondok Pesantren') }}</p>
                                    <span class="text-xs font-semibold text-amber-700 bg-amber-50 ring-1 ring-amber-100 rounded-full px-3 py-1 shrink-0">@{{ pondoks.length }}/5</span>
                                </div>
                                <div v-for="(pondok, index) in pondoks" :key="index"
                                    class="relative rounded-2xl ring-1 ring-amber-100 bg-gradient-to-br from-amber-50/70 via-white to-white p-4 sm:p-5 pl-5 sm:pl-6 space-y-4 overflow-hidden">
                                    <span class="absolute left-0 top-5 bottom-5 w-1 rounded-r-full bg-gradient-to-b from-amber-400 to-orange-400"></span>
                                    <div class="flex items-center justify-between">
                                        <h3 class="flex items-center gap-2 font-bold text-slate-800">
                                            <span class="w-7 h-7 rounded-full bg-amber-400 text-white text-xs font-bold flex items-center justify-center">@{{ index + 1 }}</span>
                                            {{ __('Pondok Pesantren') }} @{{ index + 1 }}
                                        </h3>

                                        <button v-if="pondoks.length > 1" type="button" @click="hapusPondok(index)"
                                            class="btn-hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                            {{ __('Hapus') }}
                                        </button>
                                    </div>
                                    <div class="grid sm:grid-cols-2 gap-x-5 gap-y-5">
                                        <!-- Asal Pondok -->
                                        <div>
                                            <label class="lbl">
                                                {{ __('Asal Pondok Pesantren') }}
                                                <span class="text-rose-500">*</span>
                                            </label>

                                            <input v-model="pondok.asal_pondok" class="inp inp-putih"
                                                placeholder="{{ __('Tulis dalam bahasa Arab atau Latin') }}" dir="auto"
                                                @input="validasiPondokField(index, 'asal_pondok'), validasiHurufArab(pondok.asal_pondok, `pondok_${index}_asal_pondok`)">

                                            <p class="err" v-if="e[`pondok_${index}_asal_pondok`]">
                                                @{{ e[`pondok_${index}_asal_pondok`] }}
                                            </p>
                                        </div>

                                        <!-- Nama Pengasuh -->
                                        <div>
                                            <label class="lbl">
                                                {{ __('Nama Pengasuh Pondok Pesantren') }}
                                                <span class="text-rose-500">*</span>
                                            </label>

                                            <input v-model="pondok.nama_pengasuh" class="inp inp-putih"
                                                placeholder="{{ __('Tulis dalam bahasa Arab atau Latin') }}" dir="auto"
                                                @input="validasiPondokField(index, 'nama_pengasuh'), validasiHurufArab(pondok.nama_pengasuh, `pondok_${index}_nama_pengasuh`)">
                                            <p class="err" v-if="e[`pondok_${index}_nama_pengasuh`]">
                                                @{{ e[`pondok_${index}_nama_pengasuh`] }}
                                            </p>
                                        </div>

                                        <!-- Nama Daerah -->
                                        <div>
                                            <label class="lbl">
                                                {{ __('Nama Daerah/Kota Pondok Pesantren') }}
                                                <span class="text-rose-500">*</span>
                                            </label>

                                            <input v-model="pondok.nama_daerah" class="inp inp-putih"
                                                placeholder="{{ __('Tulis dalam bahasa Arab atau Latin') }}" dir="auto"
                                                @input="validasiPondokField(index, 'nama_daerah'), validasiHurufArab(pondok.nama_daerah, `pondok_${index}_nama_daerah`)">

                                            <p class="err" v-if="e[`pondok_${index}_nama_daerah`]">
                                                @{{ e[`pondok_${index}_nama_daerah`] }}
                                            </p>
                                        </div>

                                        <!-- Lama Belajar -->
                                        <div>
                                            <label class="lbl">
                                                {{ __('Lama Belajar (tahun)') }}
                                                <span class="text-rose-500">*</span>
                                            </label>

                                            <input v-model="pondok.tahun_belajar" type="number" min="1"
                                                class="inp inp-putih" placeholder="{{ __('Contoh: 3') }}"
                                                @input="validasiPondokField(index, 'tahun_belajar')">
                                            <p class="err" v-if="e[`pondok_${index}_tahun_belajar`]">
                                                @{{ e[`pondok_${index}_tahun_belajar`] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" @click="tambahPondok" :disabled="pondoks.length >= 5"
                                    class="btn-tambah border-amber-200 text-amber-700 hover:bg-amber-50 hover:border-amber-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    {{ __('Tambah Pondok Pesantren') }}
                                </button>

                            </div>

                            <!-- B. Pendidikan formal -->
                            <div class="space-y-4">
                                <p class="sub-judul !mb-0"><span class="sub-ikon bg-sky-500">B</span> {{ __('Riwayat Pendidikan Formal') }}</p>
                                <div v-for="(pondok, index) in pondoks" :key="index"
                                    class="rounded-2xl ring-1 ring-sky-100 bg-gradient-to-br from-sky-50/70 to-white p-4 sm:p-5 space-y-4">

                                    <div class="grid sm:grid-cols-2 gap-x-5 gap-y-5">
                                        <!-- pendidikan terakhir -->
                                        <div>
                                            <label class="lbl">{{ __('Pendidikan Terakhir') }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <select v-model="f.pendidikan_terakhir" class="inp inp-putih">
                                                <option value="">{{ __('Pilih...') }}</option>
                                                <option value="K">إبتدائية</option>
                                                <option value="L">ثانوية</option>
                                                <option value="M">عالية</option>
                                                <option value="o">جامعة</option>
                                            </select>
                                            <p class="err" v-if="e.pendidikan_terakhir">@{{ e . pendidikan_terakhir }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="lbl">{{ __('Nama Sekolah/Lembaga Pendidikan') }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <input v-model="f.nama_sekolah" class="inp inp-putih"
                                                placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="rtl"
                                                @input="validasiHurufArab(f.nama_sekolah, 'nama_sekolah')">
                                            <p class="err" v-if="e.nama_sekolah">@{{ e . nama_sekolah }}</p>
                                        </div>
                                        <div>
                                            <label class="lbl">{{ __('Nama Daerah Lembaga Pendidikan') }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <input v-model="f.nama_daerah" class="inp inp-putih"
                                                placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="rtl"
                                                @input="validasiHurufArab(f.nama_daerah, 'nama_daerah')">
                                            <p class="err" v-if="e.nama_daerah">@{{ e . nama_daerah }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- C. Kitab-kitab yang pernah dipelajari -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="sub-judul !mb-0"><span class="sub-ikon bg-cyan-500">C</span> {{ __('Kitab-kitab yang Pernah Dipelajari') }}</p>
                                    <span class="text-xs font-semibold text-cyan-700 bg-cyan-50 ring-1 ring-cyan-100 rounded-full px-3 py-1 shrink-0">@{{ riwayatKitab.length }}/20</span>
                                </div>
                                <div v-for="(kitab, index) in riwayatKitab" :key="kitab.id"
                                    class="rounded-2xl ring-1 ring-cyan-100 bg-gradient-to-br from-cyan-50/60 to-white p-4 sm:p-5 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="flex items-center gap-2 font-bold text-slate-800">
                                            <span class="w-7 h-7 rounded-lg bg-cyan-500 text-white text-xs font-bold flex items-center justify-center">@{{ index + 1 }}</span>
                                            {{ __('Kitab') }} @{{ index + 1 }}
                                        </h3>

                                        <button v-if="riwayatKitab.length > 1" type="button" @click="hapusKitab(index)"
                                            class="btn-hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                            {{ __('Hapus') }}
                                        </button>
                                    </div>

                                    <div class="grid sm:grid-cols-3 gap-x-5 gap-y-5">

                                        <!-- Nama Kitab -->
                                        <div>
                                            <label class="lbl">
                                                {{ __('Nama Kitab') }}
                                                <span class="text-rose-500">*</span>
                                            </label>

                                            <input v-model="kitab.nama_kitab" class="inp inp-putih"
                                                placeholder="{{ __('Contoh: فتح القريب') }}" dir="auto"
                                                @input="validasiKitabField(index, 'nama_kitab') , validasiHurufArab(kitab.nama_kitab, `kitab_${index}_nama_kitab`)">

                                            <p class="err" v-if="e[`kitab_${index}_nama_kitab`]">
                                                @{{ e[`kitab_${index}_nama_kitab`] }}
                                            </p>
                                        </div>

                                        <!-- Fan Kitab -->
                                        <div>
                                            <label class="lbl">
                                                {{ __('Fan Kitab') }}
                                                <span class="text-rose-500">*</span>
                                            </label>

                                            <select v-model="kitab.fan_kitab" class="inp inp-putih"
                                                @change="validasiKitabField(index, 'fan_kitab')">
                                                <option value="">{{ __('-- Pilih Fan Kitab --') }}</option>

                                                <option v-for="fan in daftarFanKitab" :key="fan"
                                                    :value="fan">
                                                    @{{ fan }}
                                                </option>
                                            </select>

                                            <p class="err" v-if="e[`kitab_${index}_fan_kitab`]">
                                                @{{ e[`kitab_${index}_fan_kitab`] }}
                                            </p>
                                        </div>

                                        <!-- Status Ujian -->
                                        <div>
                                            <label class="lbl">
                                                {{ __('Sudah Diujikan?') }}
                                                <span class="text-rose-500">*</span>
                                            </label>

                                            <select v-model="kitab.sudah_diujikan" class="inp inp-putih"
                                                @change="validasiKitabField(index, 'sudah_diujikan')">
                                                <option value="">{{ __('-- Pilih Status --') }}</option>
                                                <option value="sudah">أختبر</option>
                                                <option value="belum">لا</option>
                                            </select>

                                            <p class="err" v-if="e[`kitab_${index}_sudah_diujikan`]">
                                                @{{ e[`kitab_${index}_sudah_diujikan`] }}
                                            </p>
                                        </div>

                                    </div>
                                </div>

                                <!-- Tombol tambah kitab -->
                                <button type="button" @click="tambahKitab" :disabled="riwayatKitab.length >= 20"
                                    class="btn-tambah border-cyan-200 text-cyan-700 hover:bg-cyan-50 hover:border-cyan-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    {{ __('Tambah Kitab') }}
                                </button>

                            </div>
                        </div>

                        <!-- STEP 5 -->
                        <div v-show="step === 4" class="space-y-6">
                            <div class="grid sm:grid-cols-2 gap-x-5 gap-y-5">
                                <div>
                                    <label class="lbl">{{ __('Marhalah Yang Diinginkan DI darul Musthofa') }}
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <input v-model="f.marhalah" @input="validasiHurufArab(f.marhalah, 'marhalah')"
                                        class="inp" placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto">
                                    <p class="err" v-if="e.marhalah">@{{ e . marhalah }}</p>
                                </div>
                                <div>
                                    <label class="lbl">{{ __('Berapa tahun anda ingin belajar di Darul Musthofa ?') }}
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <input v-model="f.lama_belajar" @input="validasiHurufArab(f.lama_belajar, 'lama_belajar')"
                                        class="inp" placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto">
                                    <p class="err" v-if="e.lama_belajar">@{{ e . lama_belajar }}</p>
                                </div>
                                <div>
                                    <label class="lbl">{{ __('Kesehatan') }}
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <input v-model="f.kesehatan" @input="validasiHurufArab(f.kesehatan, 'kesehatan')"
                                        class="inp" placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto">
                                    <p class="err" v-if="e.kesehatan">@{{ e . kesehatan }}</p>
                                </div>
                                <div>
                                    <label class="lbl">{{ __('Status Ekonomi') }}
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <input v-model="f.status_ekonomi"
                                        @input="validasiHurufArab(f.status_ekonomi, 'status_ekonomi')" class="inp"
                                        placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto">
                                    <p class="err" v-if="e.status_ekonomi">@{{ e . status_ekonomi }}</p>
                                </div>
                                <div>
                                    <label class="lbl">{{ __('Banyaknya Orang Muslim Didaerah Anda') }}
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input v-model="f.persentasimuslim" type="number" min="0" max="100"
                                            class="inp !pr-10" placeholder="{{ __('Persentase banyaknya orang muslim') }}" dir="auto">
                                        <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-sm font-semibold text-emerald-600">%</span>
                                    </div>
                                    <p class="err" v-if="e.persentasimuslim">@{{ e . persentasimuslim }}</p>
                                </div>
                                <div>
                                    <label class="lbl">{{ __('Banyaknya Alumni Darul Musthofa Didaerah Anda') }}
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <input v-model="f.alumni" class="inp"
                                        placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto">
                                    <p class="err" v-if="e.alumni">@{{ e . alumni }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="lbl">{{ __('Tujuan Anda Belajar Di Darul Musthofa') }}
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea v-model="f.tujuan" @input="validasiHurufArab(f.tujuan, 'tujuan')" class="inp h-28" maxlength="5000"
                                        placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto"></textarea>
                                    <p class="err" v-if="e.tujuan">@{{ e . tujuan }}</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="lbl">{{ __('Rencana Setelah Lulus Dari Darul Musthofa') }}
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <textarea v-model="f.rencana" @input="validasiHurufArab(f.rencana, 'rencana')" class="inp h-28" maxlength="5000"
                                        placeholder="{{ __('tulis dalam bahasa Arab atau Latin') }}" dir="auto"></textarea>
                                    <p class="err" v-if="e.rencana">@{{ e . rencana }}</p>
                                </div>
                                <!-- Pernah ikut Dauroh Qobul sebelumnya? (لا / نعم) -->
                                <div class="sm:col-span-2">
                                    <label class="lbl">{{ __('Apakah Anda pernah mengikuti Dauroh Qobul sebelumnya?') }}
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-2 gap-3 max-w-md">
                                        <button type="button" @click="f.pernah_dauroh = 'tidak'; delete e.pernah_dauroh"
                                            class="flex items-center justify-center gap-2 rounded-xl px-4 py-3 ring-1 transition-colors"
                                            :class="f.pernah_dauroh === 'tidak' ? 'bg-emerald-50 ring-2 ring-emerald-500 text-emerald-800' : 'bg-white ring-slate-200 text-slate-600 hover:bg-slate-50'">
                                            <span class="w-4 h-4 rounded-full ring-2 flex items-center justify-center" :class="f.pernah_dauroh === 'tidak' ? 'ring-emerald-500' : 'ring-slate-300'">
                                                <span v-if="f.pernah_dauroh === 'tidak'" class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            </span>
                                            <span class="text-xl font-bold" lang="ar">لا</span>
                                        </button>
                                        <button type="button" @click="f.pernah_dauroh = 'ya'; delete e.pernah_dauroh"
                                            class="flex items-center justify-center gap-2 rounded-xl px-4 py-3 ring-1 transition-colors"
                                            :class="f.pernah_dauroh === 'ya' ? 'bg-emerald-50 ring-2 ring-emerald-500 text-emerald-800' : 'bg-white ring-slate-200 text-slate-600 hover:bg-slate-50'">
                                            <span class="w-4 h-4 rounded-full ring-2 flex items-center justify-center" :class="f.pernah_dauroh === 'ya' ? 'ring-emerald-500' : 'ring-slate-300'">
                                                <span v-if="f.pernah_dauroh === 'ya'" class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            </span>
                                            <span class="text-xl font-bold" lang="ar">نعم</span>
                                        </button>
                                    </div>
                                    <p class="err" v-if="e.pernah_dauroh">@{{ e . pernah_dauroh }}</p>
                                </div>
                            </div>

                            <!-- Akun login -->
                            <div class="rounded-2xl bg-gradient-to-br from-[#0b332c] to-[#135347] p-4 sm:p-5 text-white">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="w-10 h-10 rounded-xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center text-emerald-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                    </span>
                                    <div>
                                        <p class="font-bold">{{ __('Akun Login') }}</p>
                                        <p class="text-xs text-white/60">{{ __('Dipakai untuk masuk ke akun siswa setelah diterima.') }}</p>
                                    </div>
                                </div>
                                <div class="grid sm:grid-cols-2 gap-x-5 gap-y-5">
                                    <!-- Email Login -->
                                    <div>
                                        <label class="lbl !text-white/80">
                                            {{ __('Email Login') }}
                                            <span class="text-rose-300">*</span>
                                        </label>

                                        <input v-model.trim="f.email_login" @input="validasiEmail" type="email" class="inp inp-putih"
                                            placeholder="contoh@email.com" autocomplete="email" dir="ltr">

                                        <p class="err !text-rose-200" v-if="e.email_login">
                                            @{{ e . email_login }}
                                        </p>
                                    </div>

                                    <!-- Password Login -->
                                    <div>
                                        <label class="lbl !text-white/80">
                                            {{ __('Password Login') }}
                                            <span class="text-rose-300">*</span>
                                        </label>

                                        <input v-model="f.password_login" @input="validasiPassword" type="password"
                                            class="inp inp-putih" placeholder="{{ __('Minimal 8 karakter') }}" autocomplete="new-password"
                                            dir="ltr">

                                        <p class="err !text-rose-200" v-if="e.password_login">
                                            @{{ e . password_login }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- STEP 6 -->
                        <div v-show="step === 5" class="space-y-5">
                            <div class="grid sm:grid-cols-2 gap-4">
                                <div v-for="b in berkas" :key="b.key"
                                    class="rounded-2xl border-2 border-dashed p-4 transition-colors"
                                    :class="e[b.key] ? 'border-rose-300 bg-rose-50/50' : 'border-emerald-200 bg-emerald-50/30 hover:border-emerald-400 hover:bg-emerald-50/60'">
                                    <div class="flex items-center gap-3 mb-3">
                                        <span class="w-11 h-11 shrink-0 rounded-xl bg-gradient-to-br from-teal-400 to-emerald-500 text-white flex items-center justify-center shadow-sm">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                        </span>
                                        <div class="min-w-0">
                                            <label class="lbl !mb-0 !text-slate-800 !font-bold">@{{ b . label }}
                                                <span class="text-rose-500">*</span>
                                            </label>
                                            <p class="text-xs text-slate-400">@{{ b . hint }}</p>
                                        </div>
                                    </div>
                                    <input type="file" :accept="b.accept" @change="pilihFile($event, b.key)"
                                        class="block w-full text-xs text-slate-500 cursor-pointer file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-[#0b332c] file:text-white file:font-semibold hover:file:bg-[#135347] file:cursor-pointer">
                                    <p class="err" v-if="e[b.key]">@{{ e[b . key] }}</p>
                                </div>
                            </div>

                            <!-- Catatan peserta untuk admin (opsional) -->
                            <div class="rounded-2xl ring-1 ring-sky-100 bg-gradient-to-br from-sky-50/70 to-white p-4 sm:p-5">
                                <p class="sub-judul">
                                    <span class="sub-ikon bg-sky-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
                                    </span>
                                    {{ __('Catatan untuk Admin') }}
                                    <span class="text-xs font-medium text-slate-400">{{ __('(opsional)') }}</span>
                                </p>
                                <textarea v-model="f.catatan_peserta" class="inp inp-putih h-28" maxlength="2000" dir="auto"
                                    placeholder="{{ __('Contoh: saya baru bisa berangkat bulan depan, mohon info jadwalnya.') }}"></textarea>
                                <p class="hint">@{{ (f.catatan_peserta || '').length }}/2000 {{ __('karakter') }} · {{ __('Catatan ini akan dibaca oleh admin.') }}</p>
                                <p class="err" v-if="e.catatan_peserta">@{{ e . catatan_peserta }}</p>
                            </div>

                            <label class="flex items-start gap-3 text-sm rounded-2xl bg-amber-50 ring-1 ring-amber-200/70 p-4 cursor-pointer hover:bg-amber-100/60 transition-colors">
                                <input type="checkbox" v-model="f.setuju" class="mt-0.5 w-5 h-5 shrink-0 rounded accent-emerald-600 cursor-pointer">
                                <span class="text-slate-700">{{ __('Saya menyatakan bahwa seluruh data yang diisi adalah benar dan dapat dipertanggungjawabkan.') }}</span>
                            </label>
                            <p class="err" v-if="e.setuju">@{{ e . setuju }}</p>
                        </div>
                        </div>

                        <!-- Navigasi -->
                        <div class="flex items-center justify-between gap-3 px-5 sm:px-8 py-4 sm:py-5 bg-[#f5f9f7] border-t border-slate-100">
                            <button type="button" @click="mundur" :disabled="step === 0 || loading"
                                class="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-2xl bg-white ring-1 ring-slate-200 text-slate-600 text-sm font-semibold hover:ring-slate-300 hover:text-slate-800 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                                {{ __('Kembali') }}</button>

                            <span class="hidden sm:inline text-xs text-slate-400">{{ __('Langkah') }} @{{ step + 1 }} / @{{ steps.length }}</span>

                            <button v-if="step < steps.length - 1" type="button" @click="maju"
                                class="inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 rounded-2xl bg-[#0b332c] text-white text-sm font-semibold hover:bg-[#135347] shadow-lg shadow-[#0b332c]/20 transition-colors">
                                {{ __('Lanjut') }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                            </button>

                            <button v-else type="button" @click="kirim" :disabled="loading"
                                class="inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold hover:from-emerald-600 hover:to-teal-700 shadow-lg shadow-emerald-500/30 transition disabled:opacity-60">
                                <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                <span v-if="loading">{{ __('Mengirim...') }}</span><span v-else>{{ __('Kirim Pendaftaran') }}</span>
                            </button>
                        </div>
                    </div>

                    <p class="mt-6 text-center text-xs text-slate-400">{{ __('Bertanda') }} <span class="text-rose-500 font-bold">*</span> {{ __('wajib diisi') }} · {{ __('Data Anda hanya dipakai untuk keperluan pendaftaran.') }}</p>
                </div>
        </main>

        <style>
            body {
                font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif;
            }

            .lbl {
                display: block;
                font-size: .8rem;
                font-weight: 600;
                margin-bottom: .4rem;
                color: #475569;
            }

            .inp {
                width: 100%;
                border: 1px solid #e2e8f0;
                border-radius: .85rem;
                padding: .7rem .95rem;
                font-size: .92rem;
                color: #1e293b;
                background: #f5f9f7;
                transition: border-color .15s, box-shadow .15s, background-color .15s;
            }

            .inp::placeholder {
                color: #94a3b8;
            }

            .inp:hover {
                border-color: #cbd5e1;
            }

            .inp:focus {
                outline: none;
                background: #fff;
                border-color: #34d399;
                box-shadow: 0 0 0 4px rgba(16, 185, 129, .15);
            }

            .inp-putih {
                background: #fff;
            }

            .hint {
                font-size: .75rem;
                color: #94a3b8;
                margin-top: .35rem;
            }

            .err {
                display: flex;
                align-items: center;
                gap: .35rem;
                color: #e11d48;
                font-size: .75rem;
                font-weight: 500;
                margin-top: .4rem;
            }

            .err::before {
                content: '!';
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                width: 1rem;
                height: 1rem;
                border-radius: 9999px;
                background: #ffe4e6;
                color: #e11d48;
                font-size: .65rem;
                font-weight: 800;
            }

            .sub-judul {
                display: flex;
                align-items: center;
                gap: .6rem;
                font-weight: 700;
                color: #1e293b;
                margin-bottom: 1rem;
            }

            .sub-ikon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 1.9rem;
                height: 1.9rem;
                border-radius: .7rem;
                color: #fff;
                font-size: .8rem;
                font-weight: 800;
                flex-shrink: 0;
            }

            .btn-hapus {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                font-size: .78rem;
                font-weight: 600;
                color: #e11d48;
                padding: .4rem .7rem;
                border-radius: .75rem;
                transition: background-color .15s;
            }

            .btn-hapus:hover {
                background: #fff1f2;
            }

            .btn-tambah {
                width: 100%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .5rem;
                padding: .7rem 1rem;
                border-radius: 1rem;
                border-width: 2px;
                border-style: dashed;
                font-size: .875rem;
                font-weight: 600;
                transition: background-color .15s, border-color .15s;
            }

            .btn-tambah:disabled {
                opacity: .5;
                cursor: not-allowed;
            }
        </style>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const {
                    createApp,
                    reactive,
                    ref,
                    watch
                } = Vue;

                createApp({
                    setup() {
                        const steps = [@js(__('Data Pribadi')), @js(__('Alamat Lengkap')), @js(__('Wali & Muarrif')), @js(__('Riwayat Pendidikan')),
                            @js(__('Lainnya')),
                            @js(__('Berkas & Konfirmasi'))
                        ];
                        const berkas = [{
                                key: 'berkas_foto',
                                label: @js(__('Pas Foto')),
                                accept: 'image/*',
                                hint: @js(__('JPG/PNG, maks. 5 MB'))
                            },
                            {
                                key: 'berkas_rekomendasi',
                                label: @js(__('Surat Rekomendasi')),
                                accept: '.jpg,.jpeg,.png,.pdf',
                                hint: @js(__('JPG/PNG/PDF, maks. 5 MB'))
                            },
                            {
                                key: 'berkas_raport_ijazah',
                                label: @js(__('Raport Akhir / Ijazah Terakhir')),
                                accept: '.jpg,.jpeg,.png,.pdf',
                                hint: @js(__('JPG/PNG/PDF, maks. 5 MB'))
                            },
                            {
                                key: 'berkas_izin_belajar',
                                label: @js(__('Surat Izin Belajar dari Orang Tua / Wali')),
                                accept: '.jpg,.jpeg,.png,.pdf',
                                hint: @js(__('JPG/PNG/PDF, maks. 5 MB'))
                            },
                        ];

                        const step = ref(
                            Number(sessionStorage.getItem('psb_step') ?? 0)
                        );
                        // Periksa apakah halaman di-refresh.
                        const navigasi = performance.getEntriesByType('navigation')[0];

                        if (navigasi?.type === 'reload') {
                            const langkahTersimpan = Number(
                                sessionStorage.getItem('psb_step') ?? 0
                            );

                            // Jika refresh dilakukan di Step 1, hapus data lama.
                            if (langkahTersimpan === 0) {
                                sessionStorage.removeItem('psb_form');
                                sessionStorage.removeItem('psb_step');
                            }
                        }
                        const loading = ref(false);
                        const serverError = ref('');
                        // Setiap pesan gagal kirim tampil sebagai notifikasi pojok kanan bawah (partials/notifikasi)
                        watch(serverError, (pesan) => {
                            if (pesan) window.notifikasi?.gagal(pesan, @js(__('Pendaftaran belum terkirim')));
                        });
                        const e = reactive({});
                        const files = {};
                        const f = reactive({
                            // step 1
                            nama_lengkap: '',
                            nama_lengkap_arab: '',
                            tempat_lahir: '',
                            tanggal_lahir: '',
                            status_pernikahan: '',
                            no_hp: '',
                            // step 2
                            provinsi: '',
                            kota: '',
                            kecamatan: '',
                            kelurahan: '',
                            alamat: '',
                            // step 3
                            nama_wali: '',
                            pekerjaan_wali: '',
                            hubungan_wali: '',
                            no_hp_wali: '',
                            nama_muarrif: '',
                            no_hp_muarrif: '',
                            nama_butuh: '',
                            no_hp_nya: '',
                            // step 4
                            asal_pondok: '',
                            nama_pengasuh: '',
                            nama_daerah: '',
                            tahun_belajar: 0,
                            pendidikan_terakhir: '',
                            nama_sekolah: '',
                            nama_daerah: '',
                            // step 5
                            marhalah: '',
                            lama_belajar: '',
                            kesehatan: '',
                            status_ekonomi: '',
                            persentasimuslim: '',
                            alumni: '',
                            tujuan: '',
                            rencana: '',
                            pernah_dauroh: '', // 'tidak' (لا) / 'ya' (نعم)
                            email_login: '',
                            password_login: '',
                            catatan_peserta: '',
                            setuju: false,
                        });


                        // Riwayat pondok dan kitab (step 4) - array of objects
                        const pondoks = ref([{
                            asal_pondok: '',
                            nama_pengasuh: '',
                            nama_daerah: '',
                            tahun_belajar: ''
                        }]);

                        const riwayatKitab = reactive([{
                            id: 1,
                            nama_kitab: '',
                            fan_kitab: '',
                            sudah_diujikan: ''
                        }]);



                        // Pulihkan data formulir, riwayat pondok, dan riwayat kitab.
                        try {
                            const dataTersimpan = sessionStorage.getItem('psb_form');

                            if (dataTersimpan) {
                                const data = JSON.parse(dataTersimpan);

                                // Pulihkan data formulir.
                                Object.assign(f, data.f ?? data);

                                // Pulihkan riwayat pondok.
                                if (Array.isArray(data.pondoks) && data.pondoks.length > 0) {
                                    pondoks.value = data.pondoks;
                                }

                                // Pulihkan riwayat kitab.
                                if (Array.isArray(data.riwayatKitab) && data.riwayatKitab.length > 0) {
                                    riwayatKitab.splice(
                                        0,
                                        riwayatKitab.length,
                                        ...data.riwayatKitab
                                    );
                                }
                            }
                        } catch (error) {
                            console.warn('Gagal memulihkan data formulir:', error);
                        }

                        // Fungsi menyimpan data formulir dan riwayat pondok.
                        const simpanData = () => {
                            try {
                                sessionStorage.setItem('psb_form', JSON.stringify({
                                    f: {
                                        ...f
                                    },
                                    pondoks: pondoks.value,
                                    riwayatKitab: riwayatKitab
                                }));
                            } catch (error) {
                                console.warn('Gagal menyimpan data formulir:', error);
                            }
                        };

                        // Simpan otomatis setiap kali data berubah.

                        function tambahPondok() {
                            if (pondoks.value.length >= 5) {
                                return;
                            }

                            pondoks.value.push({
                                asal_pondok: '',
                                nama_pengasuh: '',
                                nama_daerah: '',
                                tahun_belajar: ''
                            });
                        }

                        function hapusPondok(index) {
                            if (pondoks.value.length > 1) {
                                pondoks.value.splice(index, 1);
                            }
                        }

                        function validasiPondokField(index, field) {
                            const pondok = pondoks.value[index];

                            if (!pondok) return;

                            const key = `pondok_${index}_${field}`;

                            // Jangan tampilkan kesalahan sebelum validasi pertama.
                            if (!(key in e)) return;

                            if (field === 'tahun_belajar') {
                                const tahun = pondok.tahun_belajar;

                                if (
                                    tahun === '' ||
                                    tahun === null ||
                                    tahun === undefined ||
                                    !Number.isInteger(Number(tahun)) ||
                                    Number(tahun) < 0 || Number(tahun) > 15
                                ) {
                                    e[key] = @js(__('Isi lama belajar berupa bilangan bulat 0–15 tahun.'));
                                } else {
                                    delete e[key];
                                }

                                return;
                            }

                            if (!String(pondok[field] ?? '').trim()) {
                                e[key] = @js(__('Wajib diisi.'));
                            } else {
                                delete e[key];
                            }
                        }

                        const daftarFanKitab = [
                            'فقه',
                            'أصول الفقه',
                            'نحو',
                            'صرف',
                            'توحيد',
                            'مصطلح الحديث',
                            'تجويد',
                            'سيرة النبي',
                            'منطق',
                            'وغيرها',
                        ];

                        let nomorIdKitab = 1;



                        function tambahKitab() {
                            if (riwayatKitab.length >= 20) return;

                            riwayatKitab.push({
                                id: nomorIdKitab++,
                                nama_kitab: '',
                                fan_kitab: '',
                                sudah_diujikan: '',
                            });
                        }

                        function hapusKitab(index) {
                            if (riwayatKitab.length <= 1) {
                                return;
                            }
                            riwayatKitab.splice(index, 1);
                            validasiSemuaKitab();
                        }

                        function validasiKitabField(index, field) {
                            const kitab = riwayatKitab[index];

                            if (!kitab) {
                                return;
                            }

                            const key = `kitab_${index}_${field}`;

                            delete e[key];

                            if (field === 'nama_kitab') {
                                if (!String(kitab.nama_kitab ?? '').trim()) {
                                    e[key] = @js(__('Nama kitab wajib diisi.'));
                                }
                            }

                            if (field === 'fan_kitab') {
                                if (!daftarFanKitab.includes(kitab.fan_kitab)) {
                                    e[key] = @js(__('Silakan pilih fan kitab.'));
                                }
                            }

                            if (field === 'sudah_diujikan') {
                                if (!['sudah', 'belum'].includes(kitab.sudah_diujikan)) {
                                    e[key] = @js(__('Silakan pilih status ujian.'));
                                }
                            }
                        }

                        function validasiSemuaKitab() {
                            Object.keys(e).forEach((key) => {
                                if (key.startsWith('kitab_')) {
                                    delete e[key];
                                }
                            });

                            riwayatKitab.forEach((kitab, index) => {
                                validasiKitabField(index, 'nama_kitab');
                                validasiKitabField(index, 'fan_kitab');
                                validasiKitabField(index, 'sudah_diujikan');
                            });

                            return !Object.keys(e).some((key) => {
                                return key.startsWith('kitab_');
                            });
                        }
                        // Field wajib per step (validasi cepat di browser; validasi resmi tetap di server)
                        const wajib = [
                            ['nama_lengkap', 'nama_lengkap_arab', 'tempat_lahir', 'tanggal_lahir',
                                'status_pernikahan', 'no_hp'
                            ],
                            ['provinsi', 'kota', 'kecamatan', 'kelurahan', 'alamat'],
                            ['nama_wali', 'no_hp_wali', 'nama_muarrif', 'no_hp_muarrif', 'nama_butuh',
                                'no_hp_nya'
                            ],
                            ['pendidikan_terakhir', 'nama_sekolah', 'nama_daerah', ],
                            ['marhalah', 'lama_belajar', 'kesehatan', 'status_ekonomi', 'persentasimuslim',
                                'alumni', 'tujuan', 'rencana', 'pernah_dauroh', 'email_login', 'password_login'
                            ],
                            ['berkas_foto', 'berkas_rekomendasi', 'berkas_raport_ijazah', 'berkas_izin_belajar', 'setuju'],
                        ];
                        const stepOf = (field) => wajib.findIndex(g => g.includes(field));

                        const bersihkan = () => Object.keys(e).forEach(k => delete e[k]);

                        const cekStep = (i) => {
                            bersihkan();

                            // Memeriksa data wajib biasa pada setiap langkah.
                            wajib[i].forEach((k) => {
                                const v = k.startsWith('berkas_') ?
                                    files[k] :
                                    f[k];

                                if (v === undefined || v === null || v === '') {
                                    e[k] = @js(__('Wajib diisi.'));
                                }
                            });

                            // Validasi nomor WhatsApp pendaftar.
                            if (i === 0 && !/^\+62[0-9]+$/.test(f.no_hp)) {
                                e.no_hp = @js(__('Nomor WhatsApp harus diawali +62 dan diikuti angka.'));
                            }

                            // Validasi nomor WhatsApp wali.
                            if (
                                i === 2 &&
                                f.no_hp_wali &&
                                !/^(\+62|62)8\d{7,12}$/.test(f.no_hp_wali)
                            ) {
                                e.no_hp_wali = @js(__('Format nomor WhatsApp tidak valid (contoh: +6281234567890).'));
                            }

                            // Validasi nomor WhatsApp muarrif.
                            if (
                                i === 2 &&
                                f.no_hp_muarrif &&
                                !/^(\+62|62)8\d{7,12}$/.test(f.no_hp_muarrif)
                            ) {
                                e.no_hp_muarrif = @js(__('Format nomor WhatsApp tidak valid (contoh: +6281234567890).'));
                            }

                            // Validasi nomor WhatsApp yang bisa dihubungi.
                            if (
                                i === 2 &&
                                f.no_hp_nya &&
                                !/^(\+62|62)8\d{7,12}$/.test(f.no_hp_nya)
                            ) {
                                e.no_hp_nya = @js(__('Format nomor WhatsApp tidak valid (contoh: +6281234567890).'));
                            }

                            // Validasi riwayat pondok pesantren pada langkah ke-4.
                            if (i === 3) {
                                pondoks.value.forEach((pondok, index) => {
                                    ['asal_pondok', 'nama_pengasuh', 'nama_daerah']
                                    .forEach((field) => {
                                        const key = `pondok_${index}_${field}`;

                                        if (!String(pondok[field] ?? '').trim()) {
                                            e[key] = @js(__('Wajib diisi.'));
                                        }
                                    });

                                    const key = `pondok_${index}_tahun_belajar`;
                                    const tahun = pondok.tahun_belajar;

                                    if (
                                        tahun === '' ||
                                        tahun === null ||
                                        tahun === undefined ||
                                        !Number.isInteger(Number(tahun)) ||
                                        Number(tahun) < 0 || Number(tahun) > 30
                                    ) {
                                        e[key] = @js(__('Isi lama belajar antara 0 sampai 30 tahun.'));
                                    }
                                });
                            }
                            if (i === 3) {
                                validasiSemuaKitab();
                            }
                            if (i === 4) {
                                validasiEmail()
                                validasiPassword()

                                if (e.email_login || e.password_login) {
                                    return false
                                }
                            }
                            return Object.keys(e).length === 0;
                        };


                        // Isian langkah ini belum lengkap -> notifikasi ringkas (pesan rinci tetap di bawah tiap kolom)
                        const beriTahuBelumLengkap = () => {
                            const jumlah = Object.keys(e).length;
                            if (!jumlah) return;
                            const pertama = Object.values(e)[0];
                            window.notifikasi?.peringatan(
                                pertama === @js(__('Wajib diisi.')) ? @js(__('Lengkapi kolom yang ditandai merah, lalu lanjutkan.')) : pertama,
                                jumlah > 1 ? @js(__('Ada :jumlah kolom yang belum benar')).replace(':jumlah', jumlah) : @js(__('Ada 1 kolom yang belum benar'))
                            );
                            // Gulir ke kolom pertama yang salah
                            setTimeout(() => document.querySelector('.err')?.closest('div')?.scrollIntoView({ behavior: 'smooth', block: 'center' }), 50);
                        };

                        const maju = () => {
                            if (!cekStep(step.value)) {
                                beriTahuBelumLengkap();
                                return;
                            }
                            simpanData();
                            step.value++;
                            sessionStorage.setItem('psb_step', String(step.value));
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        };
                        const mundur = () => {
                            simpanData();
                            bersihkan();

                            if (step.value > 0) {
                                step.value--;

                                sessionStorage.setItem(
                                    'psb_step',
                                    String(step.value)
                                );
                            }
                        };
                        // Pilih berkas: cek ukuran & format, lalu BACA isinya sekarang dan simpan salinannya.
                        // Kenapa dibaca sekarang: di HP (terutama dari Google Drive / galeri awan) berkas bisa
                        // "hilang" sebelum dikirim, lalu browser gagal mengirim dan dulu tampil sebagai "Koneksi bermasalah".
                        // HEIC (foto iPhone) tidak diterima server; iPhone biasanya otomatis mengubahnya ke JPG
                        const FORMAT_GAMBAR = ['image/jpeg', 'image/png', 'image/webp'];
                        const FORMAT_BERKAS = ['image/jpeg', 'image/png', 'application/pdf'];
                        const pilihFile = async (ev, key) => {
                            const berkasDipilih = ev.target.files[0] || null;
                            const tolak = (pesan) => {
                                files[key] = null;
                                ev.target.value = '';
                                e[key] = pesan;
                            };
                            if (!berkasDipilih) {
                                files[key] = null;
                                return;
                            }

                            // Tolak berkas di atas 5 MB sebelum dikirim, supaya tidak gagal di server
                            if (berkasDipilih.size > 5 * 1024 * 1024) {
                                return tolak(@js(__('Ukuran berkas terlalu besar (maksimal 5 MB).')));
                            }
                            if (berkasDipilih.size === 0) {
                                return tolak(@js(__('Berkas kosong / rusak. Silakan pilih berkas lain.')));
                            }

                            // Format: pas foto harus gambar; berkas lain JPG/PNG/PDF
                            const ext = (berkasDipilih.name.split('.').pop() || '').toLowerCase();
                            const okFoto = FORMAT_GAMBAR.includes(berkasDipilih.type) || (!berkasDipilih.type && ['jpg', 'jpeg', 'png', 'webp'].includes(ext));
                            const okBerkas = FORMAT_BERKAS.includes(berkasDipilih.type) || (!berkasDipilih.type && ['jpg', 'jpeg', 'png', 'pdf'].includes(ext));
                            if (key === 'berkas_foto' ? !okFoto : !okBerkas) {
                                return tolak(key === 'berkas_foto'
                                    ? @js(__('Pas foto harus berupa gambar (JPG/PNG).'))
                                    : @js(__('Format berkas harus JPG, PNG, atau PDF.')));
                            }

                            // Baca isi berkas sekarang (bila gagal -> beri tahu di kolom berkas, bukan "koneksi")
                            try {
                                const isi = await berkasDipilih.arrayBuffer();
                                files[key] = new File([isi], berkasDipilih.name, { type: berkasDipilih.type || 'application/octet-stream' });
                                delete e[key];
                            } catch (_) {
                                tolak(@js(__('Berkas tidak bisa dibaca. Simpan dulu berkasnya ke HP (bukan dari Drive/awan), lalu pilih ulang.')));
                            }
                        };

                        const kirim = async () => {
                            if (!cekStep(step.value)) return beriTahuBelumLengkap();
                            serverError.value = '';
                            loading.value = true;

                            const fd = new FormData();
                            Object.entries(f).forEach(([k, v]) => fd.append(k, k === 'setuju' ? (v ? 1 :
                                0) : (v ??
                                '')));
                            // Mengirim riwayat pondok pesantren ke Laravel.
                            pondoks.value.forEach((pondok, index) => {
                                fd.append(
                                    `riwayat_pondok[${index}][asal_pondok]`,
                                    String(pondok.asal_pondok ?? '')
                                );

                                fd.append(
                                    `riwayat_pondok[${index}][nama_pengasuh]`,
                                    String(pondok.nama_pengasuh ?? '')
                                );

                                fd.append(
                                    `riwayat_pondok[${index}][nama_daerah]`,
                                    String(pondok.nama_daerah ?? '')
                                );

                                fd.append(
                                    `riwayat_pondok[${index}][tahun_belajar]`,
                                    String(pondok.tahun_belajar ?? '')
                                );
                            });

                            // TAMBAHAN: Mengirim riwayat kitab ke Laravel.
                            riwayatKitab.forEach((kitab, index) => {
                                fd.append(
                                    `riwayat_kitab[${index}][nama_kitab]`,
                                    String(kitab.nama_kitab ?? '').trim()
                                );

                                fd.append(
                                    `riwayat_kitab[${index}][fan_kitab]`,
                                    String(kitab.fan_kitab ?? '')
                                );

                                fd.append(
                                    `riwayat_kitab[${index}][sudah_diujikan]`,
                                    String(kitab.sudah_diujikan ?? '')
                                );
                            });

                            // Mengirim berkas (salinan yang sudah dibaca saat dipilih, lihat pilihFile).
                            Object.entries(files).forEach(
                                ([k, v]) => v && fd.append(k, v)
                            );
                            try {
                                const res = await fetch('{{ route('psb.store') }}', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector(
                                                'meta[name=csrf-token]')
                                            .content,
                                        'Accept': 'application/json',
                                    },
                                    body: fd,
                                });
                                const raw = await res.text();
                                let data = {};
                                try {
                                    data = JSON.parse(raw);
                                } catch (_) {}
                                if (res.status === 422) {
                                    bersihkan();
                                    let awal = 99;
                                    Object.entries(data.errors || {}).forEach(([k, msgs]) => {
                                        // Error bertingkat dari server -> nama kolom di formulir:
                                        // riwayat_pondok.0.asal_pondok -> pondok_0_asal_pondok, riwayat_kitab.1.fan_kitab -> kitab_1_fan_kitab
                                        const m = k.match(/^riwayat_(pondok|kitab)\.(\d+)\.(\w+)$/);
                                        const kunci = m ? `${m[1]}_${m[2]}_${m[3]}` : k;
                                        e[kunci] = msgs[0];
                                        const s = k.startsWith('riwayat_') ? 3 : stepOf(k); // riwayat ada di langkah 4
                                        if (s >= 0 && s < awal) awal = s;
                                    });
                                    if (awal !== 99) step.value = awal;
                                    serverError.value = Object.values(data.errors || {})[0]?.[0] ||
                                        @js(__('Ada data yang belum sesuai.'));
                                } else if (res.ok) {
                                    if (data.redirect) {
                                        window.location.href = data.redirect;
                                        return;
                                    }
                                    serverError.value =
                                        @js(__('Pendaftaran kemungkinan sudah tersimpan, tetapi balasan server tidak terbaca. Jangan kirim ulang; hubungi admin untuk memastikan.'));
                                } else {
                                    // Pesan sesuai jenis masalah (bukan pesan teknis untuk programmer)
                                    const pesanStatus = {
                                        403: data.message || @js(__('Mohon maaf, pendaftaran sedang ditutup.')),
                                        413: @js(__('Total ukuran berkas terlalu besar. Kecilkan ukuran berkas (maks. 5 MB per berkas) lalu kirim lagi.')),
                                        419: @js(__('Sesi halaman sudah habis. Muat ulang halaman (isian Anda tetap tersimpan), pilih ulang berkas, lalu kirim lagi.')),
                                        429: @js(__('Terlalu sering mencoba. Tunggu 1 menit lalu kirim lagi.')),
                                    };
                                    serverError.value = pesanStatus[res.status] || (res.status >= 500
                                        ? @js(__('Server sedang sibuk atau bermasalah (kode :status). Silakan coba beberapa menit lagi.')).replace(':status', res.status)
                                        : @js(__('Pendaftaran gagal dikirim (kode :status). Silakan coba lagi.')).replace(':status', res.status));
                                }
                            } catch (err) {
                                // Gagal mengirim sama sekali: bedakan internet mati dengan berkas yang tidak bisa dikirim
                                serverError.value = navigator.onLine
                                    ? @js(__('Gagal mengirim. Internet Anda tampak normal, jadi kemungkinan ada berkas yang tidak bisa dikirim. Buka langkah 6, pilih ulang semua berkas (simpan dulu ke HP bila dari Drive), lalu kirim lagi.'))
                                    : @js(__('Tidak ada koneksi internet. Periksa internet Anda lalu kirim lagi — isian Anda tetap tersimpan.'));
                            }
                            loading.value = false;
                        };

                        function validasiEmail() {
                            const email = String(f.email_login ?? '').trim();

                            if (!email) {
                                e.email_login = @js(__('Email login wajib diisi.'));
                            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                                e.email_login = @js(__('Format email tidak valid.'));
                            } else {
                                delete e.email_login;
                            }
                        }

                        function validasiPassword() {
                            const password = String(f.password_login ?? '');

                            if (!password) {
                                e.password_login = @js(__('Password login wajib diisi.'));
                            } else if (password.length < 8) {
                                e.password_login = @js(__('Password minimal 8 karakter.'));
                            } else {
                                delete e.password_login;
                            }
                        }

                        function validasiHurufArab(nilai, namaField) {
                            const teks = String(nilai ?? '').trim();

                            // Huruf Arab, harakat, spasi, dan tanda baca umum.
                            const polaArab = /^[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s،؛؟.'-]+$/u;

                            if (!teks) {
                                e[namaField] = @js(__('Kolom ini wajib diisi.'));
                            } else if (!polaArab.test(teks)) {
                                e[namaField] = @js(__('Harus menggunakan huruf Arab.'));
                            } else {
                                delete e[namaField];
                            }
                        }
                        return {
                            steps,

                            berkas,
                            step,
                            loading,
                            serverError,
                            e,
                            f,
                            maju,
                            mundur,
                            pilihFile,
                            kirim,
                            pondoks,
                            tambahPondok,
                            hapusPondok,
                            validasiPondokField,
                            daftarFanKitab,
                            riwayatKitab,
                            tambahKitab,
                            hapusKitab,
                            validasiKitabField,
                            validasiHurufArab,
                            validasiEmail,
                            validasiPassword
                        };
                    },
                }).mount('#app');
            });
        </script>
    {{-- Status koneksi internet + tombol muat ulang --}}
    @include('partials.status-koneksi')
    {{-- Notifikasi pojok kanan bawah (pesan sukses/error, menutup sendiri) --}}
    @include('partials.notifikasi')
    </body>

</html>
