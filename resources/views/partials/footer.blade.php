<footer class="bg-linear-to-b from-[#0B1B2B] to-[#0b345c] text-slate-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-8">

        <div class="grid md:grid-cols-3 gap-12">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="bg-[#f5faf5] py-2 px-1 rounded-xl" >
                        <img src="{{ asset('images/Logo dar4.png') }}" alt="Logo Ribath" class="h-11 w-16">
                    </div>
                    <div>
                        <p class="text-white font-bold leading-tight">Dauro Qobul Darul Musthofa</p>
                        <p class="text-xs text-slate-400">Program Penerimaan Santri Baru</p>
                    </div>
                </div>
                <p class="text-sm text-slate-400 leading-relaxed max-w-xs">
                    Darul Musthofa Tarim Menetapkan Bahwa Setiap PELAJAR ASIA TENGGARA Yang Ingin Melanjutkan Studi Di Darul Musthofa Wajib
                    Mengikuti Program Dauroh Persiapan Dan Ujian Penerimaan (Dauroh Qobul) Yang Akan Diselenggarakan Di Salah Satu Ma'had
                    Yang Telah Ditunjuk Di Indonesia.
                </p>
                <div class="flex gap-3 mt-5">
                    <a href="#" aria-label="WhatsApp" class="w-9 h-9 rounded-full bg-teal-700 flex items-center justify-center hover:opacity-90">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.39 1.26 4.81L2 22l5.42-1.36a9.9 9.9 0 0 0 4.62 1.14h.01c5.46 0 9.9-4.45 9.9-9.91 0-2.64-1.03-5.13-2.9-6.99A9.82 9.82 0 0 0 12.04 2z"/></svg>
                    </a>
                    <a href="#" aria-label="Instagram" class="w-9 h-9 rounded-full bg-pink-600 flex items-center justify-center hover:opacity-90">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2c2.72 0 3.06.01 4.12.06 1.06.05 1.79.22 2.43.47.66.26 1.21.6 1.76 1.15.55.55.9 1.1 1.15 1.76.25.64.42 1.37.47 2.43.05 1.06.06 1.4.06 4.12s-.01 3.06-.06 4.12c-.05 1.06-.22 1.79-.47 2.43a4.9 4.9 0 0 1-1.15 1.76 4.9 4.9 0 0 1-1.76 1.15c-.64.25-1.37.42-2.43.47-1.06.05-1.4.06-4.12.06s-3.06-.01-4.12-.06c-1.06-.05-1.79-.22-2.43-.47a4.9 4.9 0 0 1-1.76-1.15 4.9 4.9 0 0 1-1.15-1.76c-.25-.64-.42-1.37-.47-2.43C2.01 15.06 2 14.72 2 12s.01-3.06.06-4.12c.05-1.06.22-1.79.47-2.43.26-.66.6-1.21 1.15-1.76A4.9 4.9 0 0 1 5.45 2.53c.64-.25 1.37-.42 2.43-.47C8.94 2.01 9.28 2 12 2z"/></svg>
                    </a>
                    <a href="#" aria-label="YouTube" class="w-9 h-9 rounded-full bg-red-600 flex items-center justify-center hover:opacity-90">
                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.5 12 3.5 12 3.5s-7.5 0-9.4.6A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.6 9.4.6 9.4.6s7.5 0 9.4-.6a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8zM9.6 15.6V8.4l6.3 3.6z"/></svg>
                    </a>
                </div>
            </div>

            <div>
                <p class="text-white font-bold mb-4">Menu Cepat</p>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="hover:text-teal-400 transition-colors">Pendaftaran Santri</a></li>
                    <li><a href="#" class="hover:text-teal-400 transition-colors">Berita & Artikel</a></li>
                    <li><a href="#" class="hover:text-teal-400 transition-colors">Galeri Foto</a></li>
                </ul>
            </div>

            <div>
                <p class="text-white font-bold mb-4">Hubungi Kami</p>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-center gap-2"><span>📍</span> Solo, Jawa Tengah</li>
                    <li class="flex items-center gap-2"><span>✉️</span> {{ $kontak['email'] ?? 'info@ribathriyadh.com' }}</li>
                    <li class="flex items-center gap-2"><span>📱</span> WhatsApp: {{ $kontak['whatsapp'] ?? '+62 877-8397-5110' }}</li>
                </ul>
            </div>
        </div>

        <div class="mt-14 rounded-2xl bg-linear-to-b from-[#0B1B2B] to-[#0b345c border border-[#f8f9f740] px-8 py-10 text-center">
            <p class="text-2xl md:text-3xl text-amber-400 leading-loose" dir="rtl" style="font-family: 'Amiri', serif;">
                مَنْ سَلَكَ طَرِيقًا يَلْتَمِسُ فِيهِ عِلْمًا، سَهَّلَ اللَّهُ لَهُ بِهِ طَرِيقًا إِلَى الْجَنَّةِ
            </p>
            <p class="mt-4 text-slate-200 italic">“Barang siapa menempuh suatu jalan untuk mencari ilmu, Allah akan memudahkan baginya dengan ilmu tersebut jalan menuju
            surga.”</p>
            <p class="mt-2 text-sm text-slate-500">— HR Imam Muslim —</p>
        </div>

        <div class="mt-10 pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-slate-400">
            <p>&copy; {{ date('Y') }} Dauroh Qobul Darul Musthofa Tarim. Didirikan sejak 2014.</p>
        </div>
    </div>
</footer>
