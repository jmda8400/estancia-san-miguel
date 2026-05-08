@extends('layout')

@section('content')
<section class="internal-layout flex h-full flex-col">
    <div class="internal-page-head">
        <h1 class="internal-page-title">@yield('internal_title', 'Panel interno')</h1>
        @hasSection('internal_subtitle')
            <p class="internal-page-subtitle">@yield('internal_subtitle')</p>
        @endif
    </div>

    @yield('internal_content')
</section>
@endsection
