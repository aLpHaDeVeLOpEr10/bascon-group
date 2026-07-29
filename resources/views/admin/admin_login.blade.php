{{-- Port of application/views/admin/admin_login.php (back-office login). --}}
<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head')
</head>

<body class="page-body login-page login-form-fall">

    <div class="login-container">

        <div class="login-header login-caret">

            <div class="login-content">
                <a href="{{ url('admin') }}" class="logo">
                    <img src="{{ asset('assets/images/bg.png') }}" width="220" alt="" />
                </a>

                <p class="description">Admin login</p>

                @if ($errors->any() || session('error'))
                    <div class="alert alert-danger" style="margin-bottom:15px;">
                        {{ session('error') ?: $errors->first() }}
                    </div>
                @endif

                <form action="{{ url('admin/login_admin') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="entypo-user"></i>
                            </div>
                            <input type="text" class="form-control" name="username" id="username"
                                   value="{{ old('username') }}" placeholder="Email" autocomplete="off" />
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="entypo-key"></i>
                            </div>
                            <input type="password" class="form-control" name="password" id="password"
                                   placeholder="Password" autocomplete="off" />
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block btn-login">
                            <i class="entypo-login"></i>
                            Login In
                        </button>
                    </div>
                </form>

            </div>

        </div>

    </div>

    @include('partials.scripts')

</body>

</html>
