<!-- nav -->
<div id="nav-main" class="ui grid">
    <div class="computer only row">
        <div class="ui fixed borderless menu">
            <a href="{{ url('/') }}" class="header item">{{ setting('site.title') }}</a>
            <div class="item">
                <div class="ui action left icon input">
                    <i class="search icon"></i>
                    <input type="text" placeholder="Search" value="{{ $words or '' }}">
                    <button id="search-nav" class="ui primary button">Search</button>
                </div>
            </div>

            {{ menu('site','semantic_menu',['icon'=>true]) }}

            <div class="right menu">
                @if (Auth::guest())
                    <a href="{{ url('/login') }}" class="item no-pjax">
                        <i class="sign in icon"></i> 登录
                    </a>
                    <a href="{{ url('/register') }}" class="item no-pjax">
                        <i class="users icon"></i> 注册
                    </a>
                @else
                    <div class="ui simple dropdown item">
                        <i class="user icon"></i> {{ Auth::user()->name }}
                        <i class="dropdown icon"></i>
                        <div class="menu">
                            @if (Voyager::can('browse_admin'))
                                <a href="{{ url('/write') }}" class="item no-pjax">
                                    <i class="write icon"></i>撰写
                                </a>
                                <a href="{{ url('/admin') }}" target="_blank" class="item no-pjax">
                                    <i class="dashboard icon"></i> 后台
                                </a>
                            @endif
                            <a class="item"
                               onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                                <i class="sign out icon"></i> 退出
                            </a>
                        </div>
                    </div>
                    <form id="logout-form" action="{{ url('/logout') }}" method="post" style="display: none;">
                        {{ csrf_field() }}
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="mobile only row">
        <div class="ui fixed borderless menu">
            <a href="{{ url('/') }}" class="header item">{{ setting('site.title') }}</a>
            <div class="right menu">
                <a class="browse item"><i class="list layout icon"></i></a>
                <div id="popup-menu" class="ui fluid popup">
                    {{ menu('site','semantic_menu_mobile',['icon'=>true]) }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('script-footer')
    <script>
        function changeURLArg(url, arg, arg_val) {
            var pattern = arg + '=([^&]*)';
            var replaceText = arg + '=' + arg_val;
            if (url.match(pattern)) {
                var tmp = '/(' + arg + '=)([^&]*)/gi';
                tmp = url.replace(eval(tmp), replaceText);
                return tmp;
            } else {
                if (url.match('[\?]')) {
                    return url + '&' + replaceText;
                } else {
                    return url + '?' + replaceText;
                }
            }
        }

        $(function () {
            $('.computer .dropdown').on('click', function () {
                if ($('.computer .dropdown > .menu:hover').length < 1) {
                    location.href = $(this).attr('data-url');
                }
            });
            $('.mobile .browse').popup({
                position: 'bottom right',
                target: '#nav-main',
                popup: $('#popup-menu'),
                on: 'click'
            });
            $('#search-nav').click(function () {
                var word = $(this).prev().val();
                location.href = changeURLArg(location.href, 's', word);
            });
        });
    </script>
@endpush


