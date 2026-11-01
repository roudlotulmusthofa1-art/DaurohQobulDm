<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Pendaftaran Masuk Dauroh Qobul Darul Musthofa Tarim</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])

    </head>

    <body class="bg-slate-50 min-h-screen text-slate-800">

        <div class="max-w-5xl mx-auto px-4 py-8">
            <div class="text-center mb-6">
                <h1 class="text-2xl sm:text-4xl font-bold text-teal-700 uppercase mb-1.5">Pendaftaran Peserta Baru</h1>
                <p class="text-[15px] sm:text-[18px] text-slate-500">Dauroh Qobul Darul Musthofa Tarim — isi 6 langkah
                    berikut dengan data
                    yang benar</p>
            </div>

            @verbatim
                <div id="app">
                    <!-- Indikator step -->
                    <ol class="flex items-center justify-between mb-6">
                        <li v-for="(judulLangkah, index) in steps" :key="index"
                            class="flex-1 flex flex-col items-center relative">
                            <div v-if="index > 0" class="absolute top-4 sm:top-6 right-1/2 w-full h-0.5 sm:h-1"
                                :class="step >= index ?
                                    'bg-teal-600' :
                                    'bg-slate-200'">
                            </div>
                            <div class="relative z-10 w-10 h-10 sm:w-13 sm:h-13 rounded-full flex items-center justify-center text-sm sm:text-lg font-semibold border-2"
                                :class="step > index ?
                                    'bg-teal-600 border-teal-600 text-white' :
                                    step === index ?
                                    'bg-white border-3 border-teal-600 text-teal-700' :
                                    'bg-white border-slate-300 text-slate-400'">
                                {{ step > index ? '✓' : index + 1 }}
                            </div>
                            <span class="mt-1 text-[13px] text-center hidden sm:block"
                                :class="step === index ?
                                    'text-teal-700 font-semibold' :
                                    'text-slate-400'">{{ judulLangkah }}</span>
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
                                <input v-model="f.nama_lengkap" class="inp"
                                    placeholder="Sesuai nama yang tertera di paspor">
                                <p class="err" v-if="e.nama_lengkap">{{ e . nama_lengkap }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="lbl">
                                    Nama Lengkap Bahasa Arab (sesuai paspor)
                                    <span class="text-red-500">*</span>
                                </label>

                                <input v-model="f.nama_lengkap_arab" class="inp"
                                    placeholder="اكتب الاسم الكامل باللغة العربية" dir="auto"
                                    @input="validasiHurufArab(f.nama_lengkap_arab, 'nama_lengkap_arab')">

                                <p class="err" v-if="e.nama_lengkap_arab">
                                    {{ e . nama_lengkap_arab }}
                                </p>
                            </div>

                            <div>
                                <label class="lbl">Tempat Lahir
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.tempat_lahir" @input="validasiHurufArab(f.tempat_lahir, 'tempat_lahir')"
                                    class="inp" placeholder="اكتب مكان الميلاد باللغة العربية" dir="auto">
                                <p class="err" v-if="e.tempat_lahir">{{ e . tempat_lahir }}</p>
                            </div>
                            <div>
                                <label class="lbl">Tanggal Lahir
                                    <span class="text-red-500">*</span>
                                </label>
                                <input type="date" v-model="f.tanggal_lahir" class="inp">
                                <p class="err" v-if="e.tanggal_lahir">{{ e . tanggal_lahir }}</p>
                            </div>
                            <div>
                                <label class="lbl">Status Pernikahan
                                    <span class="text-red-500">*</span>
                                </label>
                                <select v-model="f.status_pernikahan" class="inp">
                                    <option value="">Pilih...</option>
                                    <option value="K">متزوج</option>
                                    <option value="L">عازب</option>
                                </select>
                                <p class="err" v-if="e.status_pernikahan">{{ e . status_pernikahan }}</p>
                            </div>
                            <div>
                                <label class="lbl">Nomer WhatsApp
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.no_hp" type="tel" inputmode="tel" placeholder="+6281234567890"
                                    class="inp">
                                <p class="err" v-if="e.no_hp">{{ e . no_hp }}</p>
                            </div>
                        </div>

                        <!-- STEP 2 -->
                        <div v-show="step === 1" class="grid sm:grid-cols-2 gap-4">

                            <div>
                                <label class="lbl">Provinsi
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.provinsi" @input="validasiHurufArab(f.provinsi, 'provinsi')"
                                    class="inp" placeholder="tulis dalam bahasa Arab atau Latin" dir="auto">
                                <p class="err" v-if="e.provinsi">{{ e . provinsi }}</p>
                            </div>
                            <div>
                                <label class="lbl">Kota/Kabupaten
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.kota" @input="validasiHurufArab(f.kota, 'kota')" class="inp"
                                    placeholder="tulis dalam bahasa Arab atau Latin" dir="auto">
                                <p class="err" v-if="e.kota">{{ e . kota }}</p>
                            </div>
                            <div>
                                <label class="lbl">Kecamatan
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.kecamatan" @input="validasiHurufArab(f.kecamatan, 'kecamatan')"
                                    class="inp" placeholder="tulis dalam bahasa Arab atau Latin" dir="auto">
                                <p class="err" v-if="e.kecamatan">{{ e . kecamatan }}</p>
                            </div>
                            <div>
                                <label class="lbl">Kelurahan/Desa</label>
                                <input v-model="f.kelurahan" @input="validasiHurufArab(f.kelurahan, 'kelurahan')"
                                    class="inp" placeholder="tulis dalam bahasa Arab atau Latin" dir="auto">
                                <p class="err" v-if="e.kelurahan">{{ e . kelurahan }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="lbl">Alamat Lengkap
                                    <span class="text-red-500">*</span>
                                </label>
                                <textarea v-model="f.alamat" @input="validasiHurufArab(f.alamat, 'alamat')" class="inp h-24"
                                    placeholder="tulis dalam bahasa Arab atau Latin" dir="auto"></textarea>
                                <p class="err" v-if="e.alamat">{{ e . alamat }}</p>
                            </div>
                        </div>

                        <!-- STEP 3 -->
                        <div v-show="step === 2" class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="lbl">Nama Wali
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.nama_wali" @input="validasiHurufArab(f.nama_wali, 'nama_wali')"
                                    class="inp" placeholder="tulis dalam bahasa Arab atau Latin" dir="auto">
                                <p class="err" v-if="e.nama_wali">{{ e . nama_wali }}</p>
                            </div>
                            <div>
                                <label class="lbl">Pekerjaan Wali</label>
                                <input v-model="f.pekerjaan_wali"
                                    @input="validasiHurufArab(f.pekerjaan_wali, 'pekerjaan_wali')" class="inp"
                                    placeholder="tulis dalam bahasa Arab atau Latin" dir="auto">
                                <p class="err" v-if="e.pekerjaan_wali">{{ e . pekerjaan_wali }}</p>
                            </div>
                            <div>
                                <label class="lbl">Hubungan Dengan Wali</label>
                                <input v-model="f.hubungan_wali"
                                    @input="validasiHurufArab(f.hubungan_wali, 'hubungan_wali')"
                                    placeholder="tulis dalam bahasa Arab atau Latin" class="inp">
                                <p class="err" v-if="e.hubungan_wali">{{ e . hubungan_wali }}</p>
                            </div>

                            <div>
                                <label class="lbl">No. WhatsApp Wali
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.no_hp_wali" inputmode="tel" placeholder="081234567890" class="inp">
                                <p class="text-xs text-slate-400 mt-1">Info penerimaan akan dikirim ke nomor ini.</p>
                                <p class="err" v-if="e.no_hp_wali">{{ e . no_hp_wali }}</p>
                            </div>

                            <div>
                                <label class="lbl">Nama Muarrif
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.nama_muarrif" @input="validasiHurufArab(f.nama_muarrif, 'nama_muarrif')"
                                    class="inp" placeholder="tulis dalam bahasa Arab atau Latin" dir="auto">
                                <p class="err" v-if="e.nama_muarrif">{{ e . nama_muarrif }}</p>
                            </div>
                            <div>
                                <label class="lbl">No. WhatsApp Muarrif
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.no_hp_muarrif" inputmode="tel" placeholder="081234567890"
                                    class="inp">
                                <p class="err" v-if="e.no_hp_muarrif">{{ e . no_hp_muarrif }}</p>
                            </div>
                            <div>
                                <label class="lbl">Nama Yang Bisa Di Hubungi Jika Diperlukan
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.nama_butuh" @input="validasiHurufArab(f.nama_butuh, 'nama_butuh')"
                                    class="inp" placeholder="tulis dalam bahasa Arab atau Latin" dir="auto">
                                <p class="err" v-if="e.nama_butuh">{{ e . nama_butuh }}</p>
                            </div>
                            <div>
                                <label class="lbl">No. WhatsApp Nya
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.no_hp_nya" inputmode="tel" placeholder="081234567890" class="inp">
                                <p class="err" v-if="e.no_hp_nya">{{ e . no_hp_nya }}</p>
                            </div>
                        </div>
                        <!-- STEP 4 -->
                        <div v-show="step === 3" class="grid sm:grid-cols-1 gap-4">

                            <div class="space-y-5  col-span-2 " >
                                <p class="text-sm font-semibold col-span-2 ">A. Riwayat Pendidikan Pondok Pesantren </p>
                                <div v-for="(pondok, index) in pondoks" :key="index"
                                    class="rounded-xl border border-gray-200 p-4 space-y-4 ">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-semibold text-gray-800">
                                            Pondok Pesantren @{{ index + 1 }}
                                        </h3>

                                        <button v-if="pondoks.length > 1" type="button" @click="hapusPondok(index)"
                                            class="text-red-600 hover:text-red-800">
                                            Hapus
                                        </button>
                                    </div>
                                    <div class="grid sm:grid-cols-2 gap-4">
                                        <!-- Asal Pondok -->
                                        <div>
                                            <label class="lbl">
                                                Asal Pondok Pesantren
                                                <span class="text-red-500">*</span>
                                            </label>

                                            <input v-model="pondok.asal_pondok" class="inp"
                                                placeholder="Tulis dalam bahasa Arab atau Latin" dir="auto"
                                                @input="validasiPondokField(index, 'asal_pondok'), validasiHurufArab(pondok.asal_pondok, `pondok_${index}_asal_pondok`)">

                                            <p class="err" v-if="e[`pondok_${index}_asal_pondok`]">
                                                {{ e[`pondok_${index}_asal_pondok`] }}
                                            </p>
                                        </div>

                                        <!-- Nama Pengasuh -->
                                        <div>
                                            <label class="lbl">
                                                Nama Pengasuh Pondok Pesantren
                                                <span class="text-red-500">*</span>
                                            </label>

                                            <input v-model="pondok.nama_pengasuh" class="inp"
                                                placeholder="Tulis dalam bahasa Arab atau Latin" dir="auto"
                                                @input="validasiPondokField(index, 'nama_pengasuh'), validasiHurufArab(pondok.nama_pengasuh, `pondok_${index}_nama_pengasuh`)">
                                            <p class="err" v-if="e[`pondok_${index}_nama_pengasuh`]">
                                                {{ e[`pondok_${index}_nama_pengasuh`] }}
                                            </p>
                                        </div>

                                        <!-- Nama Daerah -->
                                        <div>
                                            <label class="lbl">
                                                Nama Daerah/Kota Pondok Pesantren
                                                <span class="text-red-500">*</span>
                                            </label>

                                            <input v-model="pondok.nama_daerah" class="inp"
                                                placeholder="Tulis dalam bahasa Arab atau Latin" dir="auto"
                                                @input="validasiPondokField(index, 'nama_daerah'), validasiHurufArab(pondok.nama_daerah, `pondok_${index}_nama_daerah`)">

                                            <p class="err" v-if="e[`pondok_${index}_nama_daerah`]">
                                                {{ e[`pondok_${index}_nama_daerah`] }}
                                            </p>
                                        </div>

                                        <!-- Lama Belajar -->
                                        <div>
                                            <label class="lbl">
                                                Lama Belajar (tahun)
                                                <span class="text-red-500">*</span>
                                            </label>

                                            <input v-model="pondok.tahun_belajar" type="number" min="1"
                                                class="inp" placeholder="Contoh: 3"
                                                @input="validasiPondokField(index, 'tahun_belajar')">
                                            <p class="err" v-if="e[`pondok_${index}_tahun_belajar`]">
                                                {{ e[`pondok_${index}_tahun_belajar`] }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" @click="tambahPondok" :disabled="pondoks.length >= 5"
                                    class="flex items-center gap-2 rounded-lg
                                           border border-emerald-600 px-4 py-2
                                           text-emerald-700 hover:bg-emerald-50
                                           disabled:opacity-50">
                                    <span class="text-xl">+</span>
                                    Tambah Pondok Pesantren
                                </button>

                            </div>

                            <div class="space-y-5  col-span-2">
                                <p class="text-sm font-semibold mt-5 col-span-2">B. Riwayat Pendidikan Formal </p>
                                <div v-for="(pondok, index) in pondoks" :key="index"
                                    class="rounded-xl border border-gray-200 p-4 space-y-4 ">

                                    <div class="grid sm:grid-cols-2 gap-4">
                                        <!-- pendidikan terakhir -->
                                        <div>
                                            <label class="lbl">Pendidikan Terakhir
                                                <span class="text-red-500">*</span>
                                            </label>
                                            <select v-model="f.pendidikan_terakhir" class="inp">
                                                <option value="">Pilih...</option>
                                                <option value="K">إبتدائية</option>
                                                <option value="L">ثانوية</option>
                                                <option value="M">عالية</option>
                                                <option value="o">جامعة</option>
                                            </select>
                                            <p class="err" v-if="e.pendidikan_terakhir">{{ e . pendidikan_terakhir }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="lbl">Nama Sekolah/Lembaga Pendidikan
                                                <span class="text-red-500">*</span>
                                            </label>
                                            <input v-model="f.nama_sekolah" class="inp"
                                                placeholder="tulis dalam bahasa Arab atau Latin" dir="rtl"
                                                @input="validasiHurufArab(f.nama_sekolah, 'nama_sekolah')">
                                            <p class="err" v-if="e.nama_sekolah">{{ e . nama_sekolah }}</p>
                                        </div>
                                        <div>
                                            <label class="lbl">Nama Daerah Lembaga Pendidikan
                                                <span class="text-red-500">*</span>
                                            </label>
                                            <input v-model="f.nama_daerah" class="inp"
                                                placeholder="tulis dalam bahasa Arab atau Latin" dir="rtl"
                                                @input="validasiHurufArab(f.nama_daerah, 'nama_daerah')">
                                            <p class="err" v-if="e.nama_daerah">{{ e . nama_daerah }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- C. Kitab-kitab yang pernah dipelajari -->


                            <div class="space-y-5 col-span-2">
                                <p class="text-sm font-semibold col-span-2">C. Kitab-kitab yang pernah dipelajari </p>
                                <div v-for="(kitab, index) in riwayatKitab" :key="kitab.id"
                                    class="rounded-xl border border-gray-200 p-4 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-semibold text-gray-800">
                                            Kitab @{{ index + 1 }}
                                        </h3>

                                        <button v-if="riwayatKitab.length > 1" type="button" @click="hapusKitab(index)"
                                            class="text-red-600 hover:text-red-800">
                                            Hapus
                                        </button>
                                    </div>

                                    <div class="grid sm:grid-cols-2 gap-4">

                                        <!-- Nama Kitab -->
                                        <div>
                                            <label class="lbl">
                                                Nama Kitab
                                                <span class="text-red-500">*</span>
                                            </label>

                                            <input v-model="kitab.nama_kitab" class="inp"
                                                placeholder="Contoh: فتح القريب" dir="auto"
                                                @input="validasiKitabField(index, 'nama_kitab') , validasiHurufArab(kitab.nama_kitab, `kitab_${index}_nama_kitab`)">

                                            <p class="err" v-if="e[`kitab_${index}_nama_kitab`]">
                                                {{ e[`kitab_${index}_nama_kitab`] }}
                                            </p>
                                        </div>

                                        <!-- Fan Kitab -->
                                        <div>
                                            <label class="lbl">
                                                Fan Kitab
                                                <span class="text-red-500">*</span>
                                            </label>

                                            <select v-model="kitab.fan_kitab" class="inp"
                                                @change="validasiKitabField(index, 'fan_kitab')">
                                                <option value="">-- Pilih Fan Kitab --</option>

                                                <option v-for="fan in daftarFanKitab" :key="fan"
                                                    :value="fan">
                                                    {{ fan }}
                                                </option>
                                            </select>

                                            <p class="err" v-if="e[`kitab_${index}_fan_kitab`]">
                                                {{ e[`kitab_${index}_fan_kitab`] }}
                                            </p>
                                        </div>

                                        <!-- Status Ujian -->
                                        <div class="sm:col-span-2">
                                            <label class="lbl">
                                                Sudah Diujikan?
                                                <span class="text-red-500">*</span>
                                            </label>

                                            <select v-model="kitab.sudah_diujikan" class="inp"
                                                @change="validasiKitabField(index, 'sudah_diujikan')">
                                                <option value="">-- Pilih Status --</option>
                                                <option value="sudah">أختبر</option>
                                                <option value="belum">لا</option>
                                            </select>

                                            <p class="err" v-if="e[`kitab_${index}_sudah_diujikan`]">
                                                {{ e[`kitab_${index}_sudah_diujikan`] }}
                                            </p>
                                        </div>

                                    </div>
                                </div>

                                <!-- Tombol tambah kitab -->
                                <div class="flex items-center gap-3 flex-wrap">
                                    <button type="button" @click="tambahKitab" :disabled="riwayatKitab.length >= 20"
                                        class="flex items-center gap-2 rounded-lg
                                       border border-emerald-600 px-4 py-2
                                       text-emerald-700 hover:bg-emerald-50
                                       disabled:opacity-50">
                                        <span class="text-xl">+</span>
                                        Tambah Kitab
                                    </button>

                                    <span class="text-sm text-gray-500">
                                        @{{ riwayatKitab.length }}/20 kitab
                                    </span>
                                </div>

                            </div>
                        </div>

                        <!-- STEP 5 -->
                        <div v-show="step === 4" class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <label class="lbl">Marhalah Yang Diinginkan DI darul Musthofa
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.marhalah" @input="validasiHurufArab(f.marhalah, 'marhalah')"
                                    class="inp" placeholder="tulis dalam bahasa Arab atau Latin" dir="auto">
                                <p class="err" v-if="e.marhalah">{{ e . marhalah }}</p>
                            </div>
                            <div>
                                <label class="lbl">Berapa tahun anda ingin belajar di Darul Musthofa ?
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.lama_belajar" @input="validasiHurufArab(f.lama_belajar, 'lama_belajar')"
                                    class="inp" placeholder="tulis dalam bahasa Arab atau Latin" dir="auto">
                                <p class="err" v-if="e.lama_belajar">{{ e . lama_belajar }}</p>
                            </div>
                            <div>
                                <label class="lbl">Kesehatan
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.kesehatan" @input="validasiHurufArab(f.kesehatan, 'kesehatan')"
                                    class="inp" placeholder="tulis dalam bahasa Arab atau Latin" dir="auto">
                                <p class="err" v-if="e.kesehatan">{{ e . kesehatan }}</p>
                            </div>
                            <div>
                                <label class="lbl">Status Ekonomi
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.status_ekonomi"
                                    @input="validasiHurufArab(f.status_ekonomi, 'status_ekonomi')" class="inp"
                                    placeholder="tulis dalam bahasa Arab atau Latin" dir="auto">
                                <p class="err" v-if="e.status_ekonomi">{{ e . status_ekonomi }}</p>
                            </div>
                            <div>
                                <label class="lbl">Banyaknya Orang Muslim Didaerah Anda
                                    <span class="text-red-500">*</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <input v-model="f.persentasimuslim" type="number" min="0" max="100"
                                        class="inp" placeholder="Persentase banyaknya orang muslim" dir="auto">
                                    <span class="text-sm text-gray-500">%</span>
                                </div>
                                <p class="err" v-if="e.persentasimuslim">{{ e . persentasimuslim }}</p>
                            </div>
                            <div>
                                <label class="lbl">Banyaknya Alumni Darul Musthofa Didaerah Anda
                                    <span class="text-red-500">*</span>
                                </label>
                                <input v-model="f.alumni" class="inp"
                                    placeholder="tulis dalam bahasa Arab atau Latin" dir="auto">
                                <p class="err" v-if="e.alumni">{{ e . alumni }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="lbl">Tujuan Anda Belajar Di Darul Musthofa
                                    <span class="text-red-500">*</span>
                                </label>
                                <textarea v-model="f.tujuan" @input="validasiHurufArab(f.tujuan, 'tujuan')" class="inp h-24"
                                    placeholder="tulis dalam bahasa Arab atau Latin" dir="auto"></textarea>
                                <p class="err" v-if="e.tujuan">{{ e . tujuan }}</p>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="lbl">Rencana Setelah Lulus Dari Darul Musthofa
                                    <span class="text-red-500">*</span>
                                </label>
                                <textarea v-model="f.rencana" @input="validasiHurufArab(f.rencana, 'rencana')" class="inp h-24"
                                    placeholder="tulis dalam bahasa Arab atau Latin" dir="auto"></textarea>
                                <p class="err" v-if="e.rencana">{{ e . rencana }}</p>
                            </div>
                            <!-- Email Login -->
                            <div>
                                <label class="lbl">
                                    Email Login
                                    <span class="text-red-500">*</span>
                                </label>

                                <input v-model.trim="f.email_login" @input="validasiEmail" type="email" class="inp"
                                    placeholder="contoh@email.com" autocomplete="email" dir="ltr">

                                <p class="err" v-if="e.email_login">
                                    {{ e . email_login }}
                                </p>
                            </div>

                            <!-- Password Login -->
                            <div>
                                <label class="lbl">
                                    Password Login
                                    <span class="text-red-500">*</span>
                                </label>

                                <input v-model="f.password_login" @input="validasiPassword" type="password"
                                    class="inp" placeholder="Minimal 8 karakter" autocomplete="new-password"
                                    dir="ltr">

                                <p class="err" v-if="e.password_login">
                                    {{ e . password_login }}
                                </p>
                            </div>

                        </div>
                        <!-- STEP 6 -->
                        <div v-show="step === 5" class="space-y-4">
                            <div v-for="b in berkas" :key="b.key">
                                <label class="lbl">{{ b . label }}
                                    <span class="text-red-500">*</span>
                                </label>
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
            document.addEventListener('DOMContentLoaded', function() {
                const {
                    createApp,
                    reactive,
                    ref
                } = Vue;

                createApp({
                    setup() {
                        const steps = ['Data Pribadi', 'Alamat Lengkap', 'Wali & Muarrif', 'Riwayat Pendidikan',
                            'Lainnya',
                            'Berkas & Konfirmasi'
                        ];
                        const berkas = [{
                                key: 'berkas_foto',
                                label: 'Pas Foto',
                                accept: 'image/*',
                                hint: 'JPG/PNG, maks. 2 MB'
                            },
                            {
                                key: 'berkas_rekomendasi',
                                label: 'Surat Rekomendasi',
                                accept: '.jpg,.jpeg,.png,.pdf',
                                hint: 'JPG/PNG/PDF, maks. 3 MB'
                            },
                            {
                                key: 'berkas_raport_ijazah',
                                label: 'Raport Akhir / Ijazah Terakhir',
                                accept: '.jpg,.jpeg,.png,.pdf',
                                hint: 'JPG/PNG/PDF, maks. 3 MB'
                            },
                            {
                                key: 'berkas_izin_belajar',
                                label: 'Surat Izin Belajar dari Orang Tua / Wali',
                                accept: '.jpg,.jpeg,.png,.pdf',
                                hint: 'JPG/PNG/PDF, maks. 3 MB'
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
                            email_login: '',
                            password_login: '',
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
                                    e[key] = 'Isi lama belajar berupa bilangan bulat 0–15 tahun.';
                                } else {
                                    delete e[key];
                                }

                                return;
                            }

                            if (!String(pondok[field] ?? '').trim()) {
                                e[key] = 'Wajib diisi.';
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
                                    e[key] = 'Nama kitab wajib diisi.';
                                }
                            }

                            if (field === 'fan_kitab') {
                                if (!daftarFanKitab.includes(kitab.fan_kitab)) {
                                    e[key] = 'Silakan pilih fan kitab.';
                                }
                            }

                            if (field === 'sudah_diujikan') {
                                if (!['sudah', 'belum'].includes(kitab.sudah_diujikan)) {
                                    e[key] = 'Silakan pilih status ujian.';
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
                                'alumni', 'tujuan', 'rencana', 'email_login', 'password_login'
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
                                    e[k] = 'Wajib diisi.';
                                }
                            });

                            // Validasi nomor WhatsApp pendaftar.
                            if (i === 0 && !/^\+62[0-9]+$/.test(f.no_hp)) {
                                e.no_hp = 'Nomor WhatsApp harus diawali +62 dan diikuti angka.';
                            }

                            // Validasi nomor WhatsApp wali.
                            if (
                                i === 2 &&
                                f.no_hp_wali &&
                                !/^(\+62|62)8\d{7,12}$/.test(f.no_hp_wali)
                            ) {
                                e.no_hp_wali = 'Format nomor WhatsApp tidak valid.';
                            }

                            // Validasi nomor WhatsApp muarrif.
                            if (
                                i === 2 &&
                                f.no_hp_muarrif &&
                                !/^(\+62|62)8\d{7,12}$/.test(f.no_hp_muarrif)
                            ) {
                                e.no_hp_muarrif = 'Format nomor WhatsApp tidak valid.';
                            }

                            // Validasi riwayat pondok pesantren pada langkah ke-4.
                            if (i === 3) {
                                pondoks.value.forEach((pondok, index) => {
                                    ['asal_pondok', 'nama_pengasuh', 'nama_daerah']
                                    .forEach((field) => {
                                        const key = `pondok_${index}_${field}`;

                                        if (!String(pondok[field] ?? '').trim()) {
                                            e[key] = 'Wajib diisi.';
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
                                        e[key] = 'Isi lama belajar antara 0 sampai 30 tahun.';
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


                        const maju = () => {
                            console.log('Tombol Lanjut diklik');
                            console.log('Step sekarang:', step.value);

                            const hasil = cekStep(step.value);

                            console.log('Hasil validasi:', hasil);
                            console.log('Error validasi:', JSON.stringify(e));
                            if (cekStep(step.value)) {
                                simpanData();

                                step.value++;

                                sessionStorage.setItem(
                                    'psb_step',
                                    String(step.value)
                                );

                                window.scrollTo({
                                    top: 0,
                                    behavior: 'smooth'
                                });
                            }
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
                        const pilihFile = (ev, key) => {
                            files[key] = ev.target.files[0] || null;
                        };

                        const kirim = async () => {
                            if (!cekStep(step.value)) return;
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

                            console.log(
                                'DATA PONDOK:',
                                JSON.stringify(pondoks.value)
                            );

                            console.log(
                                'DATA KITAB:',
                                JSON.stringify(riwayatKitab)
                            );
                            console.log(
                                'ISI RIWAYAT KITAB SEBELUM DIKIRIM:',
                                JSON.stringify(riwayatKitab)
                            );
                            // Debug untuk memastikan data kitab ikut terkirim.
                            for (const [key, value] of fd.entries()) {
                                if (
                                    key.startsWith('riwayat_pondok') ||
                                    key.startsWith('riwayat_kitab')
                                ) {
                                    console.log(key, value);
                                }
                            }

                            // Mengirim berkas seperti sebelumnya.
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
                                console.log('DEBUG', res.status, res.redirected, res.url, raw.slice(0,
                                    500));
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

                        function validasiEmail() {
                            const email = String(f.email_login ?? '').trim();

                            if (!email) {
                                e.email_login = 'Email login wajib diisi.';
                            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                                e.email_login = 'Format email tidak valid.';
                            } else {
                                delete e.email_login;
                            }
                        }

                        function validasiPassword() {
                            const password = String(f.password_login ?? '');

                            if (!password) {
                                e.password_login = 'Password login wajib diisi.';
                            } else if (password.length < 8) {
                                e.password_login = 'Password minimal 8 karakter.';
                            } else {
                                delete e.password_login;
                            }
                        }

                        function validasiHurufArab(nilai, namaField) {
                            const teks = String(nilai ?? '').trim();

                            // Huruf Arab, harakat, spasi, dan tanda baca umum.
                            const polaArab = /^[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\s،؛؟.'-]+$/u;

                            if (!teks) {
                                e[namaField] = 'Kolom ini wajib diisi.';
                            } else if (!polaArab.test(teks)) {
                                e[namaField] = 'Harus menggunakan huruf Arab.';
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
    </body>

</html>
