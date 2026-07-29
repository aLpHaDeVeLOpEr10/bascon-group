<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
</head>

<body class="page-body page-fade gray">

    <div class="page-container">

        @include('partials.sidebar')

        <div class="main-content">

            @include('partials.topbar', ['logoutUrl' => url('Login/logout')])

            @yield('content')

            @include('partials.scripts')

        </div>
    </div>

</body>

</html>
