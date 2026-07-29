{{--
    The topbar block that was copy-pasted verbatim into ~30 CodeIgniter views.
    $logoutUrl differs between the two areas.
--}}
@php($logoutUrl = $logoutUrl ?? url('Login/logout'))
@php($displayName = $displayName ?? (auth('web')->user()->name ?? auth('admin')->user()->name ?? ''))

<div class="row">
    <div class="col-md-6 col-sm-8 clearfix">
        <ul class="user-info pull-left pull-none-xsm">
            <li class="profile-info dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                    {{-- The avatar image the theme referenced
                         (assets/images/thumb-1@2x.png) has never existed in this
                         asset bundle — it 404s in the CodeIgniter app too. The
                         <img> is dropped rather than kept as a broken request. --}}
                    {{ $displayName }}
                </a>
            </li>
        </ul>
    </div>

    <div class="col-md-6 col-sm-4 clearfix hidden-xs">
        <ul class="list-inline links-list pull-right">
            <li class="sep"></li>
            <li>
                <a href="{{ $logoutUrl }}">
                    Log Out <i class="entypo-logout right"></i>
                </a>
            </li>
        </ul>
    </div>
</div>

<hr />
