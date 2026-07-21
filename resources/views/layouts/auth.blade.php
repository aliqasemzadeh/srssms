<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('general.direction') }}">
@include('layouts.shared.head')
<body>
{{ $slot }}

@include('layouts.shared.foot')
</body>
</html>
