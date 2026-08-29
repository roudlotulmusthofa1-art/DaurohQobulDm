@extends('layouts.app')

@section('title', 'Beranda - Dauroh Qobul Darul Musthofa Tarim')

@section('content')

<style>
    .card {

        border-radius: 10px;

        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }
</style>
{{-- ============ HERO ============ --}}
<div>
    <section
        class="relative min-h-screen overflow-hidden bg-no-repeat bg-center bg-cover bg-scroll lg:bg-fixed flex items-center justify-center px-4"
        style="background-image: url('{{ asset('images/darul musthofa.jpg') }}');">

        {{-- Overlay teal transparan --}}
        <div class="absolute inset-0 bg-teal-900/80"></div>

        {{-- Efek cahaya tambahan --}}
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,white,transparent_35%)] opacity-10"></div>

        {{-- Semua konten berada di atas overlay --}}
        <div
            class="relative z-10 w-full max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 sm:pt-20 lg:pt-24 pb-12 sm:pb-16 text-center text-white scale-100 lg:scale-110 translate-y-0 lg:-translate-y-10">

            <div class="mx-auto w-40 h-40 rounded-2xl bg-white/80 shadow-lg flex items-center justify-center mb-8 mt-24">
                <img src="{{ asset('images/Logo Dar.png') }}" alt="Logo" class=" w-32 h-32">
            </div>

            {{-- Judul --}}
            <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-relaxed tracking-wide">
                DAUROH QOBUL dm
            </h1>
            <h2
                class="text-6xl font-bold mb-1 bg-linear-to-r from-white via-yellow-500 to-white bg-clip-text text-transparent">
                Darul Musthofa</h2>
            <h2 class="text-3xl font-semibold">Tarim, Hadromaut, Yaman</h2>

            {{-- Subjudul --}}
            <p class="mt-6 text-lg sm:text-xl font-semibold text-amber-400">
                Situs Resmi Dauroh Darul Musthofa Tarim
            </p>

            <p class="mt-2 text-teal-50/90">
                Bagi para pelajar dari wilayah <span class="text-teal-50/90 font-bold">Asia Tenggara</span> yang ingin
                melanjutkan pendidikannya <br> di Darul Musthafa,
                Tarim, Hadramaut, Yaman.
            </p>

            {{-- Tombol --}}
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-8">

                <a href="#"
                    class="card w-full sm:w-auto px-7 py-3 rounded-lg border border-white/60 font-semibold hover:bg-white/20 transition-colors">
                    Daftar Sekarang
                </a>

                <a href="#tentang"
                    class="card w-full sm:w-auto px-7 py-3 rounded-lg border border-white/60 font-semibold hover:bg-white/20 transition-colors">
                    Informasi Pondok
                </a>

            </div>

            {{-- Statistik --}}
            <div id="hero-stats"
                class="mt-12 flex flex-wrap items-center justify-center gap-x-10 gap-y-4 text-sm font-medium">

                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                    </svg>

                    <span data-target="200" class="stat-number">0</span>+
                    Santri Aktif
                </span>

                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 1l2.39 5.23L18 7.24l-4.36 3.98L14.9 17 10 13.9 5.1 17l1.26-5.78L2 7.24l5.61-1.01L10 1z"
                            clip-rule="evenodd" />
                    </svg>

                    <span data-target="15" class="stat-number">0</span>
                    Tahun Berpengalaman
                </span>

                <span class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                        <path
                            d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0z" />
                    </svg>

                    Program Terpadu
                </span>

            </div>

        </div>

    </section>

    {{-- ============ TENTANG KAMI ============ --}}
    <section id="tentang" class="bg-linear-to-b from-slate-50 to-white py-20">
        <div class="max-w-4xl mx-auto px-6">
            <span
                class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-teal-50 text-teal-700 text-xs font-semibold">
                ♡ TENTANG KAMI
            </span>
            <h2 class="mt-5 text-4xl sm:text-5xl font-extrabold">
                <span class="text-teal-700">Dauroh Qobul <i class="mdi mdi-alpha-q-box-outline:"></i></span><br>
                <span class="text-amber-500">Darul Muthofa Tarim</span>
            </h2>

            <p class="mt-6 text-slate-600 leading-relaxed">
                Dauroh Qobul <strong class="text-slate-800">Darul MUsthofa Tarim</strong>
                adalah pondok pesantren kilat, yang menenerima dan mendidik para pelajar
                <strong class="text-slate-800">Asia tenggara</strong> yang ingin melanjutkan belajarnya di
                <strong class="text-slate-800">Darul Musthofa Tarim , hadromaut , yaman</strong>
            </p>


            <div class="mt-8 rounded-2xl border border-slate-200 p-7">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-lg bg-teal-700 flex items-center justify-center">
                        <x-ribath-icon name="target" class="w-5 h-5 text-white" />
                    </div>
                    <h3 class="font-bold text-lg">Visi Kami</h3>
                </div>
                <p class="text-slate-600 italic border-l-2 border-teal-200 pl-4">
                    "Memilih para pelajar terbaik yang layak untuk belajar di Darul Musthofa, dan yang diharapkan dapat
                    memberikan manfaat
                    secara umum maupun khusus bagi umat setelah mereka kembali ke negeri masing-masing."
                </p>
            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 p-7">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-lg bg-amber-500 flex items-center justify-center">
                        <x-ribath-icon name="award" class="w-5 h-5 text-white" />
                    </div>
                    <h3 class="font-bold text-lg">Misi Kami</h3>
                </div>
                <ul class="space-y-2 text-slate-600">
                    <li>• Tujuan ini diwujudkan dengan menyediakan suasana ilmiah dan ruhani Darul Musthofa bagi para
                        calon pelajar sebelum mereka
                        datang ke Tarim. Dengan demikian, calon pelajar dapat menguji dirinya sendiri; melihat dan
                        memperlihatkan sejauh mana
                        komitmennya terhadap adab, tata tertib, serta kesungguhannya dalam menuntut ilmu.</li>
                    <li>• Melalui proses ini, pihak pengelola dapat melakukan seleksi dengan lebih baik terhadap para
                        pelajar yang benar-benar
                        layak dan memenuhi syarat untuk melanjutkan studi di Darul Musthofa.</li>
                </ul>
            </div>

            <div class="mt-6 grid sm:grid-cols-2 gap-4">
                <div class="rounded-2xl bg-blue-50 p-6">
                    <div class="flex items-center gap-2 text-blue-700 font-bold mb-2">
                        <x-ribath-icon name="clock" class="w-5 h-5" /> Informasi Waktu Dauroh
                    </div>
                    <p class="text-sm text-slate-600">
                        Sistem unik 5 tahun: <strong>Tamhidi → Ibtida → Tsanawiyah</strong>
                    </p>
                    <a href="#kurikulum"
                        class="inline-flex items-center gap-1 mt-3 text-sm font-semibold text-blue-700">
                        Struktur Komprehensif <span aria-hidden="true">→</span>
                    </a>
                </div>
                <div class="rounded-2xl bg-amber-50 p-6">
                    <div class="flex items-center gap-2 text-amber-700 font-bold mb-2">
                        <i data-lucide="book-open"></i>
                        Kurikulum Marhalah Ibtidaiyyah
                    </div>
                    <p class="text-sm text-slate-600">
                        Aktivitas <strong>03:30 - 22:30</strong> dengan pembinaan menyeluruh
                    </p>
                    <a href="#" class="inline-flex items-center gap-1 mt-3 text-sm font-semibold text-amber-700">
                        19 Jam Pembelajaran <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                <a href="#kurikulum"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-teal-700 text-white font-semibold hover:bg-teal-800 transition-colors">
                    <x-ribath-icon name="book-open" class="w-5 h-5" /> Pelajari Program
                </a>
                <a href="#kontak"
                    class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg border border-slate-300 font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <x-ribath-icon name="users" class="w-5 h-5" /> Hubungi Kami
                </a>
            </div>
        </div>
    </section>

    {{-- ============ program dauroh ============ --}}
    <section id="program-dauroh" class="bg-linear-to-b from-[#073504] to-[#0e7407] text-[#f8fffb] py-20 min-h-screen">
        <div class="max-w-6xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto">
                <span
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-white/30 text-sm font-semibold tracking-wide text-slate-300 mb-2">
                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                            d="M12.1429 11v9m0-9c-2.50543-.7107-3.19099-1.39543-6.13657-1.34968-.48057.00746-.86348.38718-.86348.84968v7.2884c0 .4824.41455.8682.91584.8617 2.77491-.0362 3.45995.6561 6.08421 1.3499m0-9c2.5053-.7107 3.1067-1.39542 6.0523-1.34968.4806.00746.9477.38718.9477.84968v7.2884c0 .4824-.4988.8682-1 .8617-2.775-.0362-3.3758.6561-6 1.3499m2-14c0 1.10457-.8955 2-2 2-1.1046 0-2-.89543-2-2s.8954-2 2-2c1.1045 0 2 .89543 2 2Z" />
                    </svg>
                    PROGRAM DAUROH
                </span>
                <h2 class="mt-5 text-3xl sm:text-4xl font-extrabold">
                    Persiapan <span class="text-amber-400">Dauroh</span><br>
                    <span class="text-[#80daa7]">&</span> <br>
                    <span class="text-emerald-400">Ujian</span> Penerimaan
                </h2>
                <p class="mt-4 text-slate-400">
                    Darul Musthofa Tarim Menetapkan Bahwa Setiap <span class="text-[#95c92e] font-semibold">PELAJAR ASIA
                        TENGGARA</span> Yang
                    Ingin Melanjutkan Studi Di Darul Musthofa Wajib Mengikuti Program Dauroh Persiapan Dan
                    Ujian Penerimaan (Dauroh Qobul) Yang Akan Diselenggarakan Di Salah Satu Ma'had Yang
                    Telah Ditunjuk Di Indonesia.
                </p>
            </div>

            <div class="mt-14 grid md:grid-cols-3 gap-6">
                @foreach ($fitur as $item)
                <div
                    class=" card rounded-2xl bg-white/5 border border-white/20 border-r-white/80 border-b-[#95c92e] p-7 hover:bg-white/20 transition-colors">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5 bg-[#519834] p-3">
                        <i data-lucide="clipboard-list" class="w-12 h-12 text-white"></i>
                    </div>
                    <h3 class="text-lg font-bold">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-400 leading-relaxed">{{ $item['desc'] }}</p>
                    <ul class="mt-4 space-y-2">
                        @foreach ($item['points'] as $point)
                        <li class="flex items-center gap-2 text-sm text-slate-300">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            {{ $point }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
    </section>



    {{-- ============ persiapan dan persyaratan dauroh ============ --}}
    <section id="persiapan-dauroh" class="bg-linear-to-b from-[#fbfbfb] to-[#e9ece6] text-white py-20 min-h-screen">
        <div class="max-w-6xl sm-max-w-12xl mx-auto px-6">
            <div class="text-center max-w-4xl mx-auto">
                <span
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-[#332245]/50 text-sm font-semibold tracking-wide text-slate-500">
                    🎓 PERSIAPAN DAUROH
                </span>
                <h2 class="mt-5 text-3xl sm:text-4xl font-extrabold">
                    <span class="text-[#e4a914] leading-relaxed">Hal-Hal Yang Perlu Di Siapkan </span> <br>
                    <span class="text-emerald-400">Sebelum Mendaftar Online di Dauroh Qobul</span>
                </h2>
                <p class="mt-4 leading-relaxed ">
                    <strong class="text-[#190644]">PETUNJUK UMUM</strong>
                    <span class="text-[#866300]">Disarankan Mengisi Formulir Pendaftaran Menggunakan Komputer Atau
                        Laptop Agar
                        Proses Pengisian Lebih Mudah Dan Nyaman.</span>
                    <span class="text-[#067002]">Pastikan Seluruh Dokumen Berikut Telah Disiapkan Dan Tersimpan Dengan
                        Baik Di Galeri
                        Hp Atau Pada Komputer/Laptop Anda Sebelum Memulai Proses Pengisian Formulir
                        Pendaftaran.</span>
                </p>
            </div>

            {{-- <div class="mt-14 grid md:grid-cols-2 gap-6">
                @foreach ($kurikulum as $item)
                <div @class([ 'rounded-2xl p-7 border'=> true,
                    'bg-gradient-to-br from-amber-50 to-stone-100 text-slate-800 border-amber-200' =>
                    !empty($item['highlight']),
                    'bg-white/5 border-white/10' => empty($item['highlight']),
                    'md:col-span-2' => $item['title'] === '45 Kitab Kuning Klasik',
                    ])>
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0
                                @class([
                                    'bg-blue-500' => $item['color'] === 'blue',
                                    'bg-purple-500' => $item['color'] === 'purple',
                                    'bg-emerald-500' => $item['color'] === 'green',
                                    'bg-orange-500' => $item['color'] === 'orange',
                                    'bg-teal-500' => $item['color'] === 'teal',
                                ])">
                                <x-ribath-icon :name="$item['icon']" class="w-6 h-6 text-white" />
                            </div>
                            <h3 class="text-xl font-bold">{{ $item['title'] }}</h3>
                        </div>
                        <div class="text-right shrink-0">
                            <p
                                class="text-xs uppercase tracking-wide {{ !empty($item['highlight']) ? 'text-amber-700' : 'text-slate-400' }}">
                                {{ $item['level'] }}</p>
                            <p
                                class="mt-1 text-sm font-semibold {{ !empty($item['highlight']) ? 'text-amber-700' : 'text-amber-400' }}">
                                ⏱ {{ $item['duration'] }}</p>
                        </div>
                    </div>

                    <p
                        class="mt-4 text-sm leading-relaxed {{ !empty($item['highlight']) ? 'text-slate-700' : 'text-slate-400' }}">
                        {{ $item['desc'] }}
                    </p>

                    <ul class="mt-4 space-y-2">
                        @foreach ($item['points'] as $point)
                        <li
                            class="flex items-center gap-2 text-sm {{ !empty($item['highlight']) ? 'text-slate-700' : 'text-slate-300' }}">
                            <x-ribath-icon name="check"
                                class="w-4 h-4 {{ !empty($item['highlight']) ? 'text-amber-600' : 'text-amber-400' }} shrink-0" />
                            {{ $point }}
                        </li>
                        @endforeach
                    </ul>

                    <div class="mt-5 flex items-center gap-1.5">
                        @for ($i = 1; $i <= 5; $i++) <span @class([ 'h-1.5 rounded-full'=> true,
                            'w-6 bg-amber-500' => $i <= $item['progress'], 'w-1.5 bg-white/15'=> $i > $item['progress']
                                && empty($item['highlight']),
                                'w-1.5 bg-slate-300' => $i > $item['progress'] && !empty($item['highlight']),
                                ])></span>
                                @endfor
                    </div>
                </div>
                @endforeach
            </div> --}}
            <div class="mt-14 grid md:grid-cols-2 gap-6 ">
                @foreach ($fitur as $item)
                <div
                    class=" card rounded-4xl bg-[#06472b00] border border-[#013e2432] border-r-[#013e249b] border-b-[#013e249b] p-7 hover:bg-white/20 transition-colors">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-5 bg-[#519834] p-3">
                        <i data-lucide="clipboard-list" class="w-12 h-12 text-white"></i>
                    </div>
                    <h3 class="text-lg font-bold text-[#190644]">{{ $item['title'] }}</h3>
                    <p class="mt-2 text-sm text-slate-400 leading-relaxed">{{ $item['desc'] }}</p>
                    <ul class="mt-4 space-y-2">
                        @foreach ($item['points'] as $point)
                        <li class="flex items-center gap-2 text-sm text-slate-500">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            {{ $point }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============ HUBUNGI KAMI ============ --}}
    <section id="kontak" class="bg-linear-to-b from-white to-slate-300 py-20">
        <div class="max-w-5xl mx-auto px-6">
            <div class="text-center max-w-xl mx-auto">
                <span
                    class="inline-flex items-center gap-3 px-6 py-2 rounded-xl bg-linear-to-b from-teal-100 to-teal-200 border border-teal-500 text-teal-700 text-sm font-semibold">
                    📞 KONTAK
                </span>
                <h2
                    class="mt-5 text-4xl sm:text-5xl font-extrabold bg-linear-to-r from-[#7066f3f0] via-[#09894ef0] to-[#7066f3f0] bg-clip-text text-transparent">
                    Hubungi Kami
                </h2>
                <p class="mt-4 text-slate-800">
                    Mari bergabung dengan kami, untuk anda yang ingin melanjutkan belajarnya ke <strong
                        class=" text-amber-700">Darul Musthofa Tarim</strong>
                </p>
            </div>

            <div class="mt-12 grid lg:grid-cols-2 gap-6">
                <div
                    class="rounded-2xl overflow-hidden min-h-80 bg-[#fafdfb] border border-[#44774455] shadow-xl transition-shadow p-2">
                    <iframe class=" w-full h-80 rounded-lg border border-[#2244551c]" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q=Jl.+Ibu+Pertiwi,+Ps.+Kliwon,+Kota+Surakarta&output=embed">
                    </iframe>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl bg-[#fafdfb] border border-[#44774455] p-7 shadow-xl transition-shadow ">
                        <div class="flex items-center gap-2 font-bold text-lg mb-5">
                            <x-ribath-icon name="chat" class="w-5 h-5 text-teal-700" /> Informasi Kontak
                        </div>
                        <div class="space-y-5 text-sm">
                            <div class="flex gap-3">
                                <x-ribath-icon name="map-pin" class="w-5 h-5 text-teal-700 shrink-0 mt-0.5" />
                                <div>
                                    <p class="font-semibold text-slate-800">Alamat</p>
                                    <p class="text-slate-600 mt-0.5">{{ $kontak['alamat'] }}</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <x-ribath-icon name="phone" class="w-5 h-5 text-teal-700 shrink-0 mt-0.5" />
                                <div>
                                    <p class="font-semibold text-slate-800">WhatsApp</p>

                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $kontak['whatsapp']) }}"
                                        target="_blank" class="text-teal-700 mt-0.5 inline-block hover:underline">
                                        {{ $kontak['whatsapp'] }}
                                    </a>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <x-ribath-icon name="envelope" class="w-5 h-5 text-teal-700 shrink-0 mt-0.5" />
                                <div>
                                    <p class="font-semibold text-slate-800">Email</p>
                                    <p class="text-teal-700 mt-0.5">{{ $kontak['email'] }}</p>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <x-ribath-icon name="chat" class="w-5 h-5 text-teal-700 shrink-0 mt-0.5" />
                                <div>
                                    <p class="font-semibold text-slate-800">Jam Layanan</p>
                                    <p class="text-slate-500 mt-0.5">{{ $kontak['jam'] }}</p>
                                    <p class="text-slate-400 text-xs">Kami siap melayani pertanyaan Anda</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-[#fafdfb] border border-[#44774455] p-7 shadow-xl transition-shadow">
                        <p class="font-bold text-lg mb-4">Media Sosial</p>
                        <div class="flex gap-3">
                            <a href="#" class="w-10 h-10 rounded-full bg-teal-700 flex items-center justify-center">
                                <x-ribath-icon name="chat" class="w-4 h-4 text-white" />
                            </a>
                            <a href="#" class="w-10 h-10 rounded-full bg-pink-600 flex items-center justify-center">
                                <x-ribath-icon name="instagram" class="w-4 h-4 text-white" />
                            </a>
                            <a href="#" class="w-10 h-10 rounded-full bg-red-600 flex items-center justify-center">
                                <x-ribath-icon name="youtube" class="w-4 h-4 text-white" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @endsection