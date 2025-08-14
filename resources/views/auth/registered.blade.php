@extends('layouts.app')

@push('styles')
    <script src="https://cdn.tailwindcss.com"></script>
@endpush

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-900 font-sans">
    <div class="w-full max-w-md p-8 space-y-6 text-center bg-gray-800 rounded-2xl shadow-2xl">
        
        <div class="flex justify-center">
            <svg class="w-16 h-16 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>

        <h1 class="text-3xl font-bold text-white">
            Pendaftaran Berhasil!
        </h1>
        <p class="text-gray-400">
            Akun Anda telah berhasil dibuat. Anda akan diarahkan ke halaman Home dalam beberapa detik.
        </p>
        
        <div class="pt-4">
            <a href="{{ route('login') }}" class="w-full inline-block px-4 py-3 font-bold text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-indigo-500 transition-transform transform hover:scale-105">
                Ke Halaman Home
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    setTimeout(function() {
        window.location.href = "{{ route('login') }}";
    }, 5000); // Redirect after 5 seconds
</script>
@endpush
