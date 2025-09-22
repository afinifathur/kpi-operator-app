{{-- resources/views/components/app-layout.blade.php --}}
{{-- Alias agar <x-app-layout> merender layouts.app yang pakai $slot --}}
@include('layouts.app', ['slot' => $slot])
