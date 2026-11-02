{{-- Tampilan <dialog> (pop-up form) di halaman Akademik --}}
<style>
    /* margin: auto -> di tengah layar (Tailwind mereset margin bawaan dialog) */
    .dialog-akademik { margin: auto; inset: 0; max-height: calc(100vh - 2rem); width: min(560px, calc(100vw - 2rem)); border-radius: 1.5rem; padding: 0; border: 0; box-shadow: 0 30px 80px -20px rgba(11, 51, 44, .45); }
    .dialog-akademik::backdrop { background: rgba(15, 23, 42, .45); backdrop-filter: blur(3px); }
    .dialog-akademik[open] { animation: dialog-muncul .2s ease-out; }
    @keyframes dialog-muncul { from { opacity: 0; transform: translateY(12px) scale(.98); } to { opacity: 1; transform: none; } }
</style>
