@extends('layout')

@section('content')
<section class="internal-layout flex h-full flex-col">
    <div class="dashboard-tabs-shell internal-dashboard-shell">
        <div class="internal-page-head internal-dashboard-head">
            <h1 class="internal-page-title">@yield('internal_title', 'Panel interno')</h1>
            @hasSection('internal_tabs')
                <div class="dashboard-tabs-header internal-tabs-wrap">
                    @yield('internal_tabs')
                </div>
            @endif
        </div>

        <div class="dashboard-tabs-body internal-dashboard-body">
            @yield('internal_content')
        </div>
    </div>
</section>
@endsection
