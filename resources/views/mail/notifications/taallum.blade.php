@component('mail::message')
# {{ $title }}

আসসালামু আলাইকুম {{ $user->name }},

{{ $message }}

@if($url)
@component('mail::button', ['url' => $url])
বিস্তারিত দেখুন
@endcomponent
@endif

আল্লাহ আপনাকে উপকারী জ্ঞান দান করুন।

শুভেচ্ছান্তে,<br>
{{ config('app.name', 'Taallum BD') }} টিম
@endcomponent
