<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Dropy CRM MVP dashboard">
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/png" href="/favicon.png">
@php
    $seoTitle = $title ?? 'Dropy CRM';
    $seoDescription = 'Dropy CRM MVP dashboard';
    $seoImage = url('/ru/ru-en/dist/images/logo/updadte-icon.png');
    $seoUrl = url()->current();
@endphp
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $seoTitle }}">
<meta property="og:description" content="{{ $seoDescription }}">
<meta property="og:url" content="{{ $seoUrl }}">
<meta property="og:image" content="{{ $seoImage }}">
<meta property="og:image:secure_url" content="{{ $seoImage }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seoTitle }}">
<meta name="twitter:description" content="{{ $seoDescription }}">
<meta name="twitter:image" content="{{ $seoImage }}">
<title>{{ $title ?? 'Dropy CRM' }}</title>
