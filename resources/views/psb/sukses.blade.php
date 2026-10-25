<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pendaftaran Berhasil | Ribath</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center text-3xl">
            ✓</div>
        <h1 class="mt-4 text-xl font-bold text-slate-800">Pendaftaran Berhasil!</h1>
        <p class="mt-2 text-sm text-slate-500">
            Data atas nama <strong>{{ $pendaftar->nama_lengkap }}</strong> sudah masuk ke admin dan akan diverifikasi.
        </p>

        <div class="mt-5 p-4 rounded-xl bg-teal-50 border border-teal-100">
            <p class="text-xs text-teal-700">Nomor Pendaftaran</p>
            <p class="text-2xl font-bold tracking-wider text-teal-800">{{ $pendaftar->nomor_pendaftaran }}</p>
        </div>

        <p class="mt-4 text-sm text-slate-500">
            Jika dinyatakan <strong>diterima</strong>, kami akan mengirim chat WhatsApp ke
            <strong>{{ $pendaftar->no_hp_wali }}</strong> berisi email dan kode akses akun siswa.
            Mohon simpan nomor pendaftaran di atas.
        </p>

        <a href="{{ url('/login') }}" class="mt-6 inline-block text-sm text-teal-700 hover:underline">Sudah diterima?
            Masuk akun siswa</a>
    </div>
</body>

</html>
