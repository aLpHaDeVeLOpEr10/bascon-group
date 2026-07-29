{{--
    Replaces application/views/side_bar.php.

    The original ran its own query inside the view (side_bar.php:37) to fetch
    the signed-in user's role. That is now taken from the auth guard.

    BEHAVIOUR CHANGE (deliberate): the original had two independent tests,
    `if ($role == 'Worker')` and `if ($role == 'Client')`, so a user whose role
    is neither — user 28, "Ahsan Bukhari", the busiest data-entry account —
    rendered an EMPTY sidebar with no navigation at all. Construction.php:24
    meanwhile let that same user into the whole construction module, because it
    only excluded the exact string 'Client'. The menu now follows the access
    rules: anyone who is not a Client gets the worker menu.
--}}
@php($user = auth('web')->user())

<div class="sidebar-menu">
    <div class="sidebar-menu-inner">

        <header class="logo-env">
            <div class="logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('assets/images/bg.png') }}" width="120" alt="" />
                </a>
            </div>

            <div class="sidebar-collapse">
                <a href="#" class="sidebar-collapse-icon">
                    <i class="entypo-menu"></i>
                </a>
            </div>

            <div class="sidebar-mobile-menu visible-xs">
                <a href="#" class="with-animation">
                    <i class="entypo-menu"></i>
                </a>
            </div>
        </header>

        <ul id="main-menu" class="main-menu">
            @if ($user && ! $user->isClient())
                <li class="opened active has-sub">
                    <a href="">
                        <i class="entypo-gauge"></i>
                        <span class="title">Construction</span>
                    </a>
                    <ul class="visible">
                        <li>
                            <a href="{{ url('construction/show_site') }}">
                                <span class="title">Show Site</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            @if ($user && $user->isClient())
                <li class="opened active has-sub">
                    <a href="#">
                        <i class="entypo-flow-tree"></i>
                        <span class="title">Payment Detail</span>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ url('client/show_payments') }}">
                                <i class="entypo-flow-line"></i>
                                <span class="title">Civil Material Payments</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('client/finish_payments') }}">
                                <i class="entypo-flow-line"></i>
                                <span class="title">Finishing Material Payments</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('client/labour_payment') }}">
                                <i class="entypo-flow-line"></i>
                                <span class="title">Labour Payments</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('client/misc_payment') }}">
                                <i class="entypo-flow-line"></i>
                                <span class="title">Miscellaneous Payments</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('client/project_mangement') }}">
                                <i class="entypo-flow-line"></i>
                                <span class="title">Project Management Fee</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('client/architect_management') }}">
                                <i class="entypo-flow-line"></i>
                                <span class="title">Architect</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('client/payment_recieved') }}">
                                <i class="entypo-flow-line"></i>
                                <span class="title">Construction Payments</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('client/total_payment') }}">
                                <i class="entypo-flow-line"></i>
                                <span class="title">Grand Total</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
        </ul>

    </div>
</div>
