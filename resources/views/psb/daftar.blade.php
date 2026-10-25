<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Pendaftaran Masuk Dauroh Qobul Darul Musthofa Tarim</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
    </head>

    <body class="bg-slate-50 min-h-screen text-slate-800">

        <div class="max-w-5xl mx-auto px-4 py-8">
            <div class="text-center mb-6">
                <h1 class="text-2xl sm:text-4xl font-bold text-teal-700 uppercase mb-1.5">Pendaftaran Peserta Baru</h1>
                <p class="text-[15px] sm:text-[18px] text-slate-500">Dauroh Qobul Darul Musthofa Tarim — isi 5 langkah berikut dengan data
                    yang benar</p>
            </div>

            @verbatim
                <div id="app">
                    <!-- Indikator step -->
                    <ol class="flex items-center justify-between mb-6">
                        <li v-for="(judulLangkah, index) in steps" :key="index"
                            class="flex-1 flex flex-col items-center relative">
                            <div v-if="index > 0" class="absolute top-4 sm:top-6 right-1/2 w-full h-0.5 sm:h-1"
                                :class="step >= index
                                ? 'bg-teal-600'
                                : 'bg-slate-200'"></div>
                            <div class="relative z-10 w-10 h-10 sm:w-13 sm:h-13 rounded-full flex items-center justify-center text-sm sm:text-lg font-semibold border-2"
                                :class="step > index
                                ? 'bg-teal-600 border-teal-600 text-white'
                                : step === index
                                    ? 'bg-white border-3 border-teal-600 text-teal-700'
                                    : 'bg-white border-slate-300 text-slate-400'">
                                {{ step > index ? '✓' : index + 1 }}
                            </div>
                            <span class="mt-1 text-[13px] text-center hidden sm:block"
                                :class="step === index
                                    ? 'text-teal-700 font-semibold'
                                    : 'text-slate-400'">{{ judulLangkah }}</span>
                        </li>
                    </ol>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                        <h2 class="text-lg font-semibold mb-4">Langkah {{ step + 1 }}: {{ steps[step] }}</h2>

                        <div v-if="serverError" class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 text-sm">
                            {{ serverError }}
                        </div>

                        <!-- STEP 1 -->
                        <div v-show="step === 0" class="grid sm:grid-cols-2 gap-4 ">
                            <div class="sm:col-span-2">
                                <label class="lbl">Nama Lengkap Bahasa Indonesia (sesuai paspor)
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.nama_lengkap" class="inp" placeholder="Sesuai nama yang tertera di paspor">
                                <p class="err" v-if="e.nama_lengkap">{{ e . nama_lengkap }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="lbl">
                                    Nama Lengkap Bahasa Arab (sesuai paspor)
                                    <span class="text-red-500">*</span>
                                </label>

                                <input v-model="f.nama_lengkap_arab" class="inp" placeholder="اكتب الاسم الكامل باللغة العربية" dir="rtl">

                                <p class="err" v-if="e.nama_lengkap_arab">
                                    {{ e.nama_lengkap_arab }}
                                </p>
                            </div>
                            <div>
                                <label class="lbl">NIK (16 digit) *</label>
                                <input v-model="f.nik" maxlength="16" inputmode="numeric" class="inp">
                                <p class="err" v-if="e.nik">{{ e . nik }}</p>
                            </div>
                            <div>
                                <label class="lbl">NISN</label>
                                <input v-model="f.nisn" maxlength="10" inputmode="numeric" class="inp">
                                <p class="err" v-if="e.nisn">{{ e . nisn }}</p>
                            </div>
                            <div>
                                <label class="lbl">Jenis Kelamin *</label>
                                <select v-model="f.jenis_kelamin" class="inp">
                                    <option value="">Pilih...</option>
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                                <p class="err" v-if="e.jenis_kelamin">{{ e . jenis_kelamin }}</p>
                            </div>
                            <div>
                                <label class="lbl">Tempat Lahir *</label>
                                <input v-model="f.tempat_lahir" class="inp">
                                <p class="err" v-if="e.tempat_lahir">{{ e . tempat_lahir }}</p>
                            </div>
                            <div>
                                <label class="lbl">Tanggal Lahir *</label>
                                <input type="date" v-model="f.tanggal_lahir" class="inp">
                                <p class="err" v-if="e.tanggal_lahir">{{ e . tanggal_lahir }}</p>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="lbl">Anak ke-</label>
                                    <input type="number" min="1" v-model="f.anak_ke" class="inp">
                                </div>
                                <div>
                                    <label class="lbl">Jml. Saudara</label>
                                    <input type="number" min="0" v-model="f.jumlah_saudara" class="inp">
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2 -->
                        <div v-show="step === 1" class="grid sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="lbl">Alamat Lengkap *</label>
                                <textarea v-model="f.alamat" rows="3" class="inp" placeholder="Jalan, RT/RW, dusun"></textarea>
                                <p class="err" v-if="e.alamat">{{ e . alamat }}</p>
                            </div>
                            <div>
                                <label class="lbl">Provinsi *</label>
                                <input v-model="f.provinsi" class="inp">
                                <p class="err" v-if="e.provinsi">{{ e . provinsi }}</p>
                            </div>
                            <div>
                                <label class="lbl">Kota/Kabupaten *</label>
                                <input v-model="f.kota" class="inp">
                                <p class="err" v-if="e.kota">{{ e . kota }}</p>
                            </div>
                            <div>
                                <label class="lbl">Kecamatan *</label>
                                <input v-model="f.kecamatan" class="inp">
                                <p class="err" v-if="e.kecamatan">{{ e . kecamatan }}</p>
                            </div>
                            <div>
                                <label class="lbl">Kelurahan/Desa</label>
                                <input v-model="f.kelurahan" class="inp">
                            </div>
                            <div>
                                <label class="lbl">Kode Pos</label>
                                <input v-model="f.kode_pos" maxlength="10" class="inp">
                            </div>
                        </div>

                        <!-- STEP 3 -->
                        <div v-show="step === 2" class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="lbl">Nama Ayah *</label>
                                <input v-model="f.nama_ayah" class="inp">
                                <p class="err" v-if="e.nama_ayah">{{ e . nama_ayah }}</p>
                            </div>
                            <div>
                                <label class="lbl">Pekerjaan Ayah</label>
                                <input v-model="f.pekerjaan_ayah" class="inp">
                            </div>
                            <div>
                                <label class="lbl">Nama Ibu *</label>
                                <input v-model="f.nama_ibu" class="inp">
                                <p class="err" v-if="e.nama_ibu">{{ e . nama_ibu }}</p>
                            </div>
                            <div>
                                <label class="lbl">Pekerjaan Ibu</label>
                                <input v-model="f.pekerjaan_ibu" class="inp">
                            </div>
                            <div>
                                <label class="lbl">Nama Wali (jika ada)</label>
                                <input v-model="f.nama_wali" class="inp">
                            </div>
                            <div>
                                <label class="lbl">No. WhatsApp Wali *</label>
                                <input v-model="f.no_hp_wali" inputmode="tel" placeholder="081234567890" class="inp">
                                <p class="text-xs text-slate-400 mt-1">Info penerimaan akan dikirim ke nomor ini.</p>
                                <p class="err" v-if="e.no_hp_wali">{{ e . no_hp_wali }}</p>
                            </div>
                        </div>

                        <!-- STEP 4 -->
                        <div v-show="step === 3" class="grid sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <label class="lbl">Asal Sekolah *</label>
                                <input v-model="f.asal_sekolah" class="inp">
                                <p class="err" v-if="e.asal_sekolah">{{ e . asal_sekolah }}</p>
                            </div>
                            <div>
                                <label class="lbl">Tahun Lulus</label>
                                <input v-model="f.tahun_lulus" maxlength="4" inputmode="numeric" class="inp">
                            </div>
                            <div>
                                <label class="lbl">Hafalan Al-Qur'an (juz)</label>
                                <input type="number" min="0" max="30" v-model="f.hafalan_juz"
                                    class="inp">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="lbl">Program Pilihan *</label>
                                <select v-model="f.program_pilihan" class="inp">
                                    <option value="">Pilih program...</option>
                                    <option v-for="p in programs" :key="p" :value="p">
                                        {{ p }}</option>
                                </select>
                                <p class="err" v-if="e.program_pilihan">{{ e . program_pilihan }}</p>
                            </div>
                        </div>

                        <!-- STEP 5 -->
                        <div v-show="step === 4" class="space-y-4">
                            <div v-for="b in berkas" :key="b.key">
                                <label class="lbl">{{ b . label }} *</label>
                                <input type="file" :accept="b.accept" @change="pilihFile($event, b.key)"
                                    class="block w-full text-sm file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                                <p class="text-xs text-slate-400 mt-1">{{ b . hint }}</p>
                                <p class="err" v-if="e[b.key]">{{ e[b . key] }}</p>
                            </div>

                            <label class="flex items-start gap-2 text-sm pt-2">
                                <input type="checkbox" v-model="f.setuju" class="mt-1 rounded">
                                <span>Saya menyatakan bahwa seluruh data yang diisi adalah benar dan dapat
                                    dipertanggungjawabkan.</span>
                            </label>
                            <p class="err" v-if="e.setuju">{{ e . setuju }}</p>
                        </div>

                        <!-- Navigasi -->
                        <div class="flex justify-between mt-6 pt-4 border-t border-slate-100">
                            <button type="button" @click="mundur" :disabled="step === 0 || loading"
                                class="px-5 py-2 rounded-lg border border-slate-300 text-sm disabled:opacity-40">Kembali</button>

                            <button v-if="step < steps.length - 1" type="button" @click="maju"
                                class="px-5 py-2 rounded-lg bg-teal-600 text-white text-sm hover:bg-teal-700">Lanjut</button>

                            <button v-else type="button" @click="kirim" :disabled="loading"
                                class="px-5 py-2 rounded-lg bg-teal-600 text-white text-sm hover:bg-teal-700 disabled:opacity-60">
                                {{ loading ? 'Mengirim...' : 'Kirim Pendaftaran' }}
                            </button>
                        </div>
                    </div>
                </div>
            @endverbatim
        </div>

        <style>
            .lbl {
                display: block;
                font-size: .8rem;
                font-weight: 500;
                margin-bottom: .25rem;
                color: #475569;
            }

            .inp {
                width: 100%;
                border: 1px solid #cbd5e1;
                border-radius: .5rem;
                padding: .55rem .75rem;
                font-size: .9rem;
                background: #fff;
            }

            .inp:focus {
                outline: 2px solid #14b8a6;
                outline-offset: -1px;
                border-color: #14b8a6;
            }

            .err {
                color: #dc2626;
                font-size: .75rem;
                margin-top: .25rem;
            }
        </style>

        <script>
            const {
                createApp,
                reactive,
                ref
            } = Vue;

            createApp({
                setup() {
                    const steps = ['Data Pribadi', 'Alamat', 'Orang Tua/Wali', 'Pendidikan & Program',
                        'Berkas & Konfirmasi'
                    ];
                    // Sesuaikan daftar program dengan program di pesantren
                    const programs = ['Tahfidz Al-Qur\'an', 'Kitab Kuning', 'Reguler'];
                    const berkas = [{
                            key: 'berkas_foto',
                            label: 'Pas Foto',
                            accept: 'image/*',
                            hint: 'JPG/PNG, maks. 2 MB'
                        },
                        {
                            key: 'berkas_kk',
                            label: 'Kartu Keluarga',
                            accept: '.jpg,.jpeg,.png,.pdf',
                            hint: 'JPG/PNG/PDF, maks. 3 MB'
                        },
                        {
                            key: 'berkas_akta',
                            label: 'Akta Kelahiran',
                            accept: '.jpg,.jpeg,.png,.pdf',
                            hint: 'JPG/PNG/PDF, maks. 3 MB'
                        },
                    ];

                    const step = ref(0);
                    const loading = ref(false);
                    const serverError = ref('');
                    const e = reactive({});
                    const files = {};
                    const f = reactive({
                        nama_lengkap: '',
                        nama_lengkap_arab: '',
                        nik: '',
                        nisn: '',
                        jenis_kelamin: '',
                        tempat_lahir: '',
                        tanggal_lahir: '',
                        anak_ke: '',
                        jumlah_saudara: '',
                        alamat: '',
                        provinsi: '',
                        kota: '',
                        kecamatan: '',
                        kelurahan: '',
                        kode_pos: '',
                        nama_ayah: '',
                        pekerjaan_ayah: '',
                        nama_ibu: '',
                        pekerjaan_ibu: '',
                        nama_wali: '',
                        no_hp_wali: '',
                        asal_sekolah: '',
                        tahun_lulus: '',
                        program_pilihan: '',
                        hafalan_juz: 0,
                        setuju: false,
                    });

                    // Field wajib per step (validasi cepat di browser; validasi resmi tetap di server)
                    const wajib = [
                        ['nama_lengkap', 'nama_lengkap_arab', 'nik', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir'],
                        ['alamat', 'provinsi', 'kota', 'kecamatan'],
                        ['nama_ayah', 'nama_ibu', 'no_hp_wali'],
                        ['asal_sekolah', 'program_pilihan'],
                        ['berkas_foto', 'berkas_kk', 'berkas_akta', 'setuju'],
                    ];
                    const stepOf = (field) => wajib.findIndex(g => g.includes(field));

                    const bersihkan = () => Object.keys(e).forEach(k => delete e[k]);

                    const cekStep = (i) => {
                        bersihkan();
                        wajib[i].forEach(k => {
                            const v = k.startsWith('berkas_') ? files[k] : f[k];
                            if (!v) e[k] = 'Wajib diisi.';
                        });
                        if (i === 0 && f.nik && !/^\d{16}$/.test(f.nik)) e.nik = 'NIK harus 16 digit angka.';
                        if (i === 2 && f.no_hp_wali && !/^(\+62|62|0)8\d{7,12}$/.test(f.no_hp_wali)) e.no_hp_wali =
                            'Format nomor WhatsApp tidak valid.';
                        return Object.keys(e).length === 0;
                    };

                    const maju = () => {
                        if (cekStep(step.value)) {
                            step.value++;
                            window.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                        }
                    };
                    const mundur = () => {
                        bersihkan();
                        if (step.value > 0) step.value--;
                    };
                    const pilihFile = (ev, key) => {
                        files[key] = ev.target.files[0] || null;
                    };

                    const kirim = async () => {
                        if (!cekStep(step.value)) return;
                        serverError.value = '';
                        loading.value = true;

                        const fd = new FormData();
                        Object.entries(f).forEach(([k, v]) => fd.append(k, k === 'setuju' ? (v ? 1 : 0) : (v ??
                            '')));
                        Object.entries(files).forEach(([k, v]) => v && fd.append(k, v));

                        try {
                            const res = await fetch('{{ route('psb.store') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')
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
                            console.log('DEBUG', res.status, res.redirected, res.url, raw.slice(0, 500));
                            if (res.status === 422) {
                                bersihkan();
                                let awal = 99;
                                Object.entries(data.errors || {}).forEach(([k, msgs]) => {
                                    e[k] = msgs[0];
                                    const s = stepOf(k);
                                    if (s >= 0 && s < awal) awal = s;
                                });
                                if (awal !== 99) step.value = awal;
                                serverError.value = Object.values(data.errors || {})[0]?.[0] ||
                                    'Ada data yang belum sesuai.';
                            } else if (res.ok) {
                                if (data.redirect) {
                                    window.location.href = data.redirect;
                                    return;
                                }
                                console.log('Respons server:', raw);
                                serverError.value =
                                    'Pendaftaran kemungkinan tersimpan, tetapi balasan server tidak terbaca. Cek menu Data Masuk.';
                            } else {
                                serverError.value = data.message ||
                                    `Server error (${res.status}). Cek storage/logs/laravel.log`;
                            }
                        } catch (err) {
                            serverError.value = 'Koneksi bermasalah. Silakan coba lagi.';
                        }
                        loading.value = false;
                    };

                    return {
                        steps,
                        programs,
                        berkas,
                        step,
                        loading,
                        serverError,
                        e,
                        f,
                        maju,
                        mundur,
                        pilihFile,
                        kirim
                    };
                },
            }).mount('#app');
        </script>
    </body>

</html>
