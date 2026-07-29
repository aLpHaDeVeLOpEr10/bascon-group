{{-- Replaces application/views/admin_sidebar.php. --}}
<div class="sidebar-menu">
    <div class="sidebar-menu-inner">

        <header class="logo-env">
            <div class="logo">
                <a href="{{ url('admin_setting/add_user') }}">
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

            <li class="opened active has-sub">
                <a href="#">
                    <i class="entypo-gauge"></i>
                    <span class="title">Registration</span>
                </a>
                <ul class="visible">
                    <li><a href="{{ url('admin_setting/add_user') }}"><span class="title">Add user</span></a></li>
                    <li><a href="{{ url('admin_setting/show_user') }}"><span class="title">Show User</span></a></li>
                </ul>
            </li>

            <li class="has-sub">
                <a href="#">
                    <i class="entypo-flow-tree"></i>
                    <span class="title">Category</span>
                </a>
                <ul>
                    <li><a href="{{ url('admin_setting/add_civil') }}"><span class="title">Civil Materials</span></a></li>
                    <li><a href="{{ url('admin_setting/add_finishing') }}"><span class="title">Finishing Materials</span></a></li>
                    <li><a href="{{ url('admin_setting/add_labour') }}"><span class="title">Labour</span></a></li>
                </ul>
            </li>

            <li class="has-sub">
                <a href="#">
                    <i class="entypo-flow-tree"></i>
                    <span class="title">Project Management</span>
                </a>
                <ul>
                    <li><a href="{{ url('admin_setting/add_con_site') }}"><span class="title">Add site</span></a></li>
                    <li><a href="{{ url('admin_setting/show_con_site') }}"><span class="title">Show site</span></a></li>
                </ul>
            </li>

            <li class="has-sub">
                <a href="#">
                    <i class="entypo-flow-tree"></i>
                    <span class="title">Architecture</span>
                </a>
                <ul>
                    <li><a href="{{ url('admin_setting/add_site') }}"><span class="title">Add site</span></a></li>
                    <li><a href="{{ url('admin_setting/show_site') }}"><span class="title">Show site</span></a></li>
                </ul>
            </li>

            <li class="has-sub">
                <a href="#">
                    <i class="entypo-flow-tree"></i>
                    <span class="title">Request</span>
                </a>
                <ul>
                    <li><a href="{{ url('admin_setting/civil_requets') }}"><span class="title">Civil Request</span></a></li>
                    <li><a href="{{ url('admin_setting/finish_requets') }}"><span class="title">Finish Request</span></a></li>
                </ul>
            </li>

            <li class="has-sub">
                <a href="#">
                    <i class="entypo-flow-tree"></i>
                    <span class="title">Setiing</span>
                </a>
                <ul>
                    <li><a href="{{ url('admin_setting/set_role') }}"><span class="title">Manage</span></a></li>
                </ul>
            </li>

            <li class="has-sub">
                <a href="#">
                    <i class="entypo-flow-tree"></i>
                    <span class="title">Money mangement</span>
                </a>
                <ul>
                    <li><a href="{{ url('admin_setting/add_expense') }}"><span class="title">Add Expense</span></a></li>
                    <li><a href="{{ url('admin_setting/show_expense') }}"><span class="title">Show Expense</span></a></li>
                </ul>
            </li>

        </ul>
    </div>
</div>
