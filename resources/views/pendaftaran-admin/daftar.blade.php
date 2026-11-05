<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('Pendaftaran Admin') }} · {{ __('Dauroh Qobul Darul Musthofa Tarim') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen text-slate-800 bg-[#eef4f1] antialiased">

    {{-- ================= HEADER ================= --}}
    <header class="relative overflow-hidden bg-gradient-to-br from-[#135347] via-[#0b332c] to-[#071f1a] text-white pt-10 pb-28 sm:pt-14 sm:pb-32">
        <div class="pointer-events-none absolute inset-0 opacity-60"
            style="background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 18px 18px;"></div>
        <div class="pointer-events-none absolute -top-32 -right-24 w-96 h-96 rounded-full bg-emerald-400/25 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-40 -left-20 w-96 h-96 rounded-full bg-amber-300/10 blur-3xl"></div>

        <div class="relative max-w-3xl mx-auto px-4 text-center">
            <div class="mx-auto w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-[#eff5f0] ring-4 ring-white/10 shadow-xl shadow-black/30 flex items-center justify-center overflow-hidden">
                <img src="{{ asset('images/Logo dar4.png') }}" alt="{{ __('Logo Dauroh Qobul') }}" class="w-full h-full object-contain">
            </div>
            <p class="mt-5 text-[11px] sm:text-xs font-semibold uppercase tracking-[0.22em] text-amber-200/90">{{ __('Dauroh Qobul · Darul Musthofa Tarim') }}</p>
            <h1 class="mt-2 text-3xl sm:text-5xl font-extrabold tracking-tight">{{ __('Pendaftaran Admin') }}</h1>
            <p class="mt-3 text-sm sm:text-lg text-emerald-50/75 max-w-xl mx-auto">{{ __('Isi data berikut dengan benar. Setelah diverifikasi, Anda bisa masuk ke panel admin.') }}</p>
        </div>
    </header>

    <main class="relative -mt-20 sm:-mt-24 max-w-3xl mx-auto px-4 pb-14">
        <div id="app" class="bg-white rounded-3xl ring-1 ring-[#0b332c]/[0.06] shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(11,51,44,0.25)] overflow-hidden">

            <div class="px-5 sm:px-8 py-5 sm:py-6 bg-gradient-to-r from-emerald-50 via-white to-amber-50/50 border-b border-slate-100 flex items-center gap-4">
                <span class="w-12 h-12 sm:w-14 sm:h-14 shrink-0 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white flex items-center justify-center shadow-lg shadow-emerald-500/25">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342"/></svg>
                </span>
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-emerald-600">{{ __('Formulir Admin') }}</p>
                    <h2 class="text-lg sm:text-2xl font-extrabold text-slate-800 leading-tight">{{ __('Data Diri & Akun') }}</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">{{ __('Semua isian dalam satu halaman.') }}</p>
                </div>
            </div>

            <div class="p-5 sm:p-8 space-y-6">
                <div v-if="serverError" class="flex items-start gap-3 p-4 rounded-2xl bg-rose-50 ring-1 ring-rose-100 text-rose-800 text-sm">
                    <span class="w-7 h-7 shrink-0 rounded-full bg-rose-500 text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008z"/></svg>
                    </span>
                    <span class="pt-1">@{{ serverError }}</span>
                </div>

                <!-- Data diri -->
                <section>
                    <p class="sub-judul"><span class="sub-ikon bg-emerald-500">1</span> {{ __('Data Diri') }}</p>
                    <div class="grid sm:grid-cols-2 gap-x-5 gap-y-5">
                        <div class="sm:col-span-2">
                            <label class="lbl">{{ __('Nama Lengkap Bahasa Indonesia') }} <span class="text-rose-500">*</span></label>
                            <input v-model="f.nama_lengkap" class="inp" placeholder="{{ __('Nama lengkap sesuai paspor/KTP') }}">
                            <p class="err" v-if="e.nama_lengkap">@{{ e.nama_lengkap }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="lbl">{{ __('Nama Lengkap Bahasa Arab') }} <span class="text-rose-500">*</span></label>
                            <input v-model="f.nama_lengkap_arab" class="inp" dir="auto" placeholder="اكتب الاسم الكامل باللغة العربية"
                                @input="validasiHurufArab(f.nama_lengkap_arab, 'nama_lengkap_arab')">
                            <p class="err" v-if="e.nama_lengkap_arab">@{{ e.nama_lengkap_arab }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="lbl">{{ __('Alamat Lengkap') }} <span class="text-rose-500">*</span></label>
                            <textarea v-model="f.alamat" class="inp h-28" dir="auto" placeholder="اكتب العنوان الكامل باللغة العربية"
                                @input="validasiHurufArab(f.alamat, 'alamat')"></textarea>
                            <p class="err" v-if="e.alamat">@{{ e.alamat }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="lbl">{{ __('Nomor WhatsApp') }} <span class="text-rose-500">*</span></label>
                            <input v-model="f.no_hp" type="tel" inputmode="tel" class="inp" placeholder="+6281234567890">
                            <p class="hint">{{ __('Info penerimaan akan dikirim ke nomor ini.') }}</p>
                            <p class="err" v-if="e.no_hp">@{{ e.no_hp }}</p>
                        </div>
                    </div>
                </section>

                <!-- Akun login -->
                <section class="rounded-2xl bg-gradient-to-br from-[#0b332c] to-[#135347] p-4 sm:p-5 text-white">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-10 h-10 rounded-xl bg-white/10 ring-1 ring-white/15 flex items-center justify-center text-emerald-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        </span>
                        <div>
                            <p class="font-bold">{{ __('2. Akun Login') }}</p>
                            <p class="text-xs text-white/60">{{ __('Dipakai untuk masuk ke panel admin setelah diterima.') }}</p>
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-x-5 gap-y-5">
                        <div>
                            <label class="lbl !text-white/80">{{ __('Email Login') }} <span class="text-rose-300">*</span></label>
                            <input v-model.trim="f.email_login" @input="validasiEmail" type="email" class="inp inp-putih"
                                placeholder="contoh@email.com" autocomplete="email" dir="ltr">
                            <p class="err !text-rose-200" v-if="e.email_login">@{{ e.email_login }}</p>
                        </div>
                        <div>
                            <label class="lbl !text-white/80">{{ __('Password Login') }} <span class="text-rose-300">*</span></label>
                            <input v-model="f.password_login" @input="validasiPassword" type="password" class="inp inp-putih"
                                placeholder="{{ __('Minimal 8 karakter') }}" autocomplete="new-password" dir="ltr">
                            <p class="err !text-rose-200" v-if="e.password_login">@{{ e.password_login }}</p>
                        </div>
                    </div>
                </section>

                <!-- Pas foto -->
                <section>
                    <p class="sub-judul"><span class="sub-ikon bg-teal-500">3</span> {{ __('Pas Foto Profil') }}</p>
                    <div class="rounded-2xl border-2 border-dashed p-4 transition-colors flex flex-col sm:flex-row sm:items-center gap-4"
                        :class="e.berkas_foto ? 'border-rose-300 bg-rose-50/50' : 'border-emerald-200 bg-emerald-50/30'">
                        <div class="w-20 h-24 shrink-0 rounded-xl overflow-hidden bg-white ring-1 ring-slate-200 flex items-center justify-center text-slate-300 self-center sm:self-auto">
                            <img v-if="pratinjau" :src="pratinjau" alt="{{ __('Pratinjau foto') }}" class="w-full h-full object-cover">
                            <svg v-else class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="lbl !text-slate-800 !font-bold">{{ __('Pas Foto') }} <span class="text-rose-500">*</span></label>
                            <p class="text-xs text-slate-400 mb-3">{{ __('JPG/PNG, maks. 5 MB. Dipakai sebagai foto profil.') }}</p>
                            <input type="file" accept="image/*" @change="pilihFoto"
                                class="block w-full text-xs text-slate-500 cursor-pointer file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-[#0b332c] file:text-white file:font-semibold hover:file:bg-[#135347] file:cursor-pointer">
                            <p class="err" v-if="e.berkas_foto">@{{ e.berkas_foto }}</p>
                        </div>
                    </div>
                </section>

            </div>

            <div class="flex items-center justify-between gap-3 px-5 sm:px-8 py-4 sm:py-5 bg-[#f5f9f7] border-t border-slate-100">
                <span class="text-xs text-slate-400">{{ __('Bertanda') }} <span class="text-rose-500 font-bold">*</span> {{ __('wajib diisi') }}</span>
                <button type="button" @click="kirim" :disabled="loading"
                    class="inline-flex items-center gap-2 px-5 sm:px-6 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white text-sm font-bold hover:from-emerald-600 hover:to-teal-700 shadow-lg shadow-emerald-500/30 transition disabled:opacity-60">
                    <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    <span v-if="loading">{{ __('Mengirim...') }}</span><span v-else>{{ __('Kirim Pendaftaran') }}</span>
                </button>
            </div>
        </div>
    </main>

    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        .lbl { display: block; font-size: .8rem; font-weight: 600; margin-bottom: .4rem; color: #475569; }
        .inp { width: 100%; border: 1px solid #e2e8f0; border-radius: .85rem; padding: .7rem .95rem; font-size: .92rem; color: #1e293b; background: #f5f9f7; transition: border-color .15s, box-shadow .15s, background-color .15s; }
        .inp::placeholder { color: #94a3b8; }
        .inp:hover { border-color: #cbd5e1; }
        .inp:focus { outline: none; background: #fff; border-color: #34d399; box-shadow: 0 0 0 4px rgba(16, 185, 129, .15); }
        .inp-putih { background: #fff; }
        .hint { font-size: .75rem; color: #94a3b8; margin-top: .35rem; }
        .err { display: flex; align-items: center; gap: .35rem; color: #e11d48; font-size: .75rem; font-weight: 500; margin-top: .4rem; }
        .err::before { content: '!'; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; width: 1rem; height: 1rem; border-radius: 9999px; background: #ffe4e6; color: #e11d48; font-size: .65rem; font-weight: 800; }
        .sub-judul { display: flex; align-items: center; gap: .6rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem; }
        .sub-ikon { display: inline-flex; align-items: center; justify-content: center; width: 1.9rem; height: 1.9rem; border-radius: .7rem; color: #fff; font-size: .8rem; font-weight: 800; flex-shrink: 0; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const { createApp, reactive, ref } = Vue;

            createApp({
                setup() {
                    const loading = ref(false);
                    const serverError = ref('');
                    const pratinjau = ref('');
                    const e = reactive({});
                    let foto = null;

                    const f = reactive({
                        nama_lengkap: '',
                        nama_lengkap_arab: '',
                        alamat: '',
                        no_hp: '',
                        email_login: '',
                        password_login: '',
                    });

                    const wajib = ['nama_lengkap', 'nama_lengkap_arab', 'alamat', 'no_hp', 'email_login', 'password_login'];

                    // Sama dengan formulir siswa: huruf Arab, harakat, spasi, dan tanda baca umum
                    function validasiHurufArab(nilai, namaField) {
                        const teks = String(nilai ?? '').trim();
                        const polaArab = /^[؀-ۿݐ-ݿࢠ-ࣿ\s،؛؟.'-]+$/u;

                        if (!teks) {
                            e[namaField] = @js(__('Kolom ini wajib diisi.'));
                        } else if (!polaArab.test(teks)) {
                            e[namaField] = @js(__('Harus menggunakan huruf Arab.'));
                        } else {
                            delete e[namaField];
                        }
                    }

                    function validasiEmail() {
                        const email = String(f.email_login ?? '').trim();
                        if (!email) e.email_login = @js(__('Email login wajib diisi.'));
                        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) e.email_login = @js(__('Format email tidak valid.'));
                        else delete e.email_login;
                    }

                    function validasiPassword() {
                        const password = String(f.password_login ?? '');
                        if (!password) e.password_login = @js(__('Password login wajib diisi.'));
                        else if (password.length < 8) e.password_login = @js(__('Password minimal 8 karakter.'));
                        else delete e.password_login;
                    }

                    // Foto: tolak di atas 5 MB sebelum dikirim
                    function pilihFoto(ev) {
                        const berkas = ev.target.files[0] || null;
                        if (berkas && berkas.size > 5 * 1024 * 1024) {
                            foto = null;
                            pratinjau.value = '';
                            ev.target.value = '';
                            e.berkas_foto = @js(__('Ukuran berkas terlalu besar (maksimal 5 MB).'));
                            return;
                        }
                        foto = berkas;
                        pratinjau.value = berkas ? URL.createObjectURL(berkas) : '';
                        delete e.berkas_foto;
                    }

                    function cekSemua() {
                        Object.keys(e).forEach((k) => delete e[k]);

                        wajib.forEach((k) => {
                            if (!String(f[k] ?? '').trim()) e[k] = @js(__('Wajib diisi.'));
                        });

                        ['nama_lengkap_arab', 'alamat'].forEach((k) => {
                            if (f[k]) validasiHurufArab(f[k], k);
                        });

                        if (f.no_hp && !/^\+62[0-9]+$/.test(f.no_hp)) {
                            e.no_hp = @js(__('Nomor WhatsApp harus diawali +62 dan diikuti angka.'));
                        }
                        if (f.email_login) validasiEmail();
                        if (f.password_login) validasiPassword();
                        if (!foto) e.berkas_foto = @js(__('Wajib diisi.'));

                        return Object.keys(e).length === 0;
                    }

                    async function kirim() {
                        serverError.value = '';
                        if (!cekSemua()) {
                            serverError.value = @js(__('Masih ada isian yang belum benar. Periksa kolom bertanda merah.'));
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                            return;
                        }

                        loading.value = true;
                        const fd = new FormData();
                        Object.entries(f).forEach(([k, v]) => fd.append(k, v ?? ''));
                        fd.append('berkas_foto', foto);

                        try {
                            const res = await fetch(window.location.pathname, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                    'Accept': 'application/json',
                                },
                                body: fd,
                            });
                            let data = {};
                            try { data = await res.json(); } catch (_) {}

                            if (res.status === 422) {
                                Object.entries(data.errors || {}).forEach(([k, msgs]) => { e[k] = msgs[0]; });
                                serverError.value = Object.values(data.errors || {})[0]?.[0] || @js(__('Ada data yang belum sesuai.'));
                                window.scrollTo({ top: 0, behavior: 'smooth' });
                            } else if (res.ok && data.redirect) {
                                window.location.href = data.redirect;
                                return;
                            } else if (res.status === 404) {
                                serverError.value = @js(__('Link pendaftaran sudah tidak berlaku. Minta link terbaru kepada admin.'));
                            } else if (res.status === 413) {
                                serverError.value = @js(__('Ukuran foto terlalu besar. Gunakan foto maksimal 5 MB.'));
                            } else {
                                serverError.value = data.message || @js(__('Server error (:status). Silakan coba lagi.')).replace(':status', res.status);
                            }
                        } catch (err) {
                            serverError.value = @js(__('Koneksi bermasalah. Silakan coba lagi.'));
                        }
                        loading.value = false;
                    }

                    return { f, e, loading, serverError, pratinjau, validasiHurufArab, validasiEmail, validasiPassword, pilihFoto, kirim };
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
