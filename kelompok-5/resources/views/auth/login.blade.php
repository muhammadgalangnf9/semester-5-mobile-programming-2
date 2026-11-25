<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SIPEKAT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    
    <div class="bg-white p-8 rounded shadow w-full max-w-sm"
         x-data="{ 
             sisa: {{ session('sisa_detik') ?? 0 }}, 
             timer: null 
         }"
         x-init="
             if (sisa > 0) {
                 timer = setInterval(() => {
                     if (sisa > 0) { sisa--; }
                     else { 
                         clearInterval(timer); 
                         // Jangan reload paksa, biarkan user klik manual jika sudah 0
                     }
                 }, 1000);
             }
         ">
        
        <h1 class="text-2xl font-bold mb-1">SIPEKAT</h1>
        <p class="text-sm text-gray-500 mb-6">Sistem Informasi Pengelolaan RT</p>

        @if ($errors->any() || session('sisa_detik'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm">
                @if (session('sisa_detik'))
                    <div x-text="'Anda diblokir. Silakan coba lagi dalam ' + sisa + ' detik.'"></div>
                @else
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <label class="block mb-4">
                <span class="text-sm font-medium text-gray-700">Username</span>
                <input type="text" name="username" 
                       value="{{ old('username') }}"
                       :disabled="sisa > 0"
                       autofocus required
                       class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500 disabled:bg-gray-100 disabled:text-gray-500">
            </label>

            <label class="block mb-6">
                <span class="text-sm font-medium text-gray-700">Password</span>
                <input type="password" name="password" required
                       :disabled="sisa > 0"
                       class="mt-1 w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-blue-500 disabled:bg-gray-100">
            </label>

            <button type="submit" 
                    :disabled="sisa > 0"
                    :class="sisa > 0 ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700'"
                    class="w-full text-white py-2 rounded transition">
                <span x-show="sisa === 0">Login</span>
                <span x-show="sisa > 0" x-text="'Tunggu (' + sisa + 's)'"></span>
            </button>
        </form>
    </div>

</body>
</html>