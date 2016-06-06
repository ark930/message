
<!doctype html>
<html class="no-js fixed-layout">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>IM | Message</title>
    <meta name="description" content="聊天主页面">
    <meta name="keywords" content="index">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="apple-touch-icon-precomposed" href="/i/app-icon72x72@2x.png">
    <meta name="apple-mobile-web-app-title" content="Amaze UI" />
    <link rel="stylesheet" href="http://cdn.amazeui.org/amazeui/2.7.0/css/amazeui.min.css"/>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body>

<header class="am-topbar am-topbar-inverse admin-header">
    <div class="am-topbar-brand">
        <strong id="title">Message</strong>
    </div>

    <button class="am-topbar-btn am-topbar-toggle am-btn am-btn-sm am-btn-success am-show-sm-only" data-am-collapse="{target: '#topbar-collapse'}"><span class="am-sr-only">导航切换</span> <span class="am-icon-bars"></span></button>

    <div class="am-collapse am-topbar-collapse" id="topbar-collapse">

        <ul class="am-nav am-nav-pills am-topbar-nav am-topbar-right admin-header-list">
            <li class="am-dropdown" data-am-dropdown>
                <a class="am-dropdown-toggle" data-am-dropdown-toggle href="javascript:;">
                    <span class="am-icon-cog"></span> 设置 <span class="am-icon-caret-down"></span>
                </a>
                <ul class="am-dropdown-content">
                    <li><a href="#"><span class="am-icon-user"></span> 资料</a></li>
                    <li id="quitButton"><a href="#"><span class="am-icon-power-off"></span> 退出</a></li>
                </ul>
            </li>
        </ul>
    </div>
</header>

<div class="am-cf admin-main">
    <!-- sidebar start -->
    <div class="admin-sidebar am-offcanvas" id="admin-offcanvas">

        <div id="doc-dropdown-justify-js">
            <div class="am-dropdown" id="doc-dropdown-js">
                <div class="am-input-group am-input-group-sm">
                    <input type="text" class="am-form-field" id="searchInput" placeholder="昵称 / 手机号">
                    <span class="am-input-group-btn">
                        <button class="am-btn am-btn-default" type="button" id="searchButton">搜索</button>
                    </span>
                </div>

                <ul class="am-dropdown-content" id="searchDropdownList"></ul>
            </div>
        </div>

        <div class="am-offcanvas-bar admin-offcanvas-bar">
            <ul class="am-list admin-sidebar-list">
                <li>
                    <a class="am-cf" data-am-collapse="{target: '#collapse-followee'}"><span class="am-icon-file"></span> 关注的人 <span class="am-icon-angle-right am-fr am-margin-right"></span></a>
                    <ul class="am-list am-collapse admin-sidebar-sub am-in" id="collapse-followee">
                    </ul>
                </li>
                <li>
                    <a class="am-cf" data-am-collapse="{target: '#collapse-group'}"><span class="am-icon-file"></span> 用户组 <span class="am-icon-angle-right am-fr am-margin-right"></span></a>
                    <ul class="am-list am-collapse admin-sidebar-sub" id="collapse-group">
                    </ul>
                </li>
            </ul>
        </div>
    </div>
    <!-- sidebar end -->

    <!-- content start -->
    <div class="admin-content" id="dialogContainer" style="display:block">
        <div class="admin-content-body" style="display:none">
            <div class="am-g am-padding">
                <div class="am-panel am-panel-default">
                    <div class="am-panel-hd am-cf" data-am-collapse="{target: '#collapse-dialog'}"><span id="collapse-dialog-title"></span><span class="am-icon-chevron-down am-fr" ></span></div>
                    <div class="am-panel-bd am-collapse am-in am-cf" id="collapse-dialog">
                        <ul class="am-comments-list admin-content-comment" id="commentContainer">
                            <li class="am-comment">
                                <a href="#"><img src="http://s.amazeui.org/media/i/demos/bw-2014-06-19.jpg?imageView/1/w/96/h/96" alt="" class="am-comment-avatar" width="48" height="48"></a>
                                <div class="am-comment-main">
                                    <header class="am-comment-hd">
                                        <div class="am-comment-meta"><a href="#" class="am-comment-author">某人</a> <time class="am-fr">2014-7-12 15:30</time></div>
                                    </header>
                                    <div class="am-comment-bd"><p>遵循 “移动优先（Mobile First）”、“渐进增强（Progressive enhancement）”的理念，可先从移动设备开始开发网站，逐步在扩展的更大屏幕的设备上，专注于最重要的内容和交互，很好。</p></div>
                                </div>
                            </li>

                        </ul>
                    </div>

                </div>
            </div>
        </div>
        <div class="am-g" style="display: none;" id="displayWall">
            <div class="am-u-sm-12">
                {{--<textarea style="width:100%;margin-bottom: 20px;" rows="12" id="messageBox"></textarea>--}}
                <div id="messageBox">

                </div>
            </div>
            <div class="am-u-sm-12">
                <textarea style="width:100%;margin-bottom: 20px;" rows="3" id="messageEditor" placeholder="请输入消息"></textarea>
            </div>
            <div class="am-u-sm-12">
                <button type="button" class="am-btn am-btn-primary am-radius am-fr" id="messageSendButton">发 送</button>
            </div>
        </div>
    </div>
    <!-- content end -->

</div>

<script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
<script src="http://cdn.staticfile.org/modernizr/2.8.3/modernizr.js"></script>
<script src="http://cdn.amazeui.org/amazeui/2.7.0/js/amazeui.ie8polyfill.min.js"></script>

<script src="http://cdn.amazeui.org/amazeui/2.7.0/js/amazeui.min.js"></script>


<script src="/js/realtime.browser.js"></script>
<script src="https://cdn1.lncld.net/static/js/av-mini-1.0.0-rc5.js"></script>
<script src="/js/typed-messages.js"></script>
<script src="/js/test.js"></script>

<script>
    $(function() {
        $('#title').text('Message | ' + localStorage.nick_name);
        var api_token = localStorage.api_token;
        if(!api_token) {
            window.location.href = '/';
        }


        $('#doc-dropdown-js').dropdown({justify: '#doc-dropdown-justify-js'});
        var $dropdown = $('#doc-dropdown-js');
        var data = $dropdown.data('amui.dropdown');
        $dropdown.on('open.dropdown.amui', function (e) {
            console.log('open event triggered');
        });

        $('#searchInput').keydown(function() {
            $dropdown.dropdown('close');
        });
        $('#searchInput').focus(function () {
            $dropdown.dropdown('close');
        });
        $('#searchButton').unbind('click').bind('click', function () {
            var search = $('#searchInput').val();
            if(!search) {
                return;
            }
            $.ajax({
                url: 'api/v1/user/find?name=' + search,
                type: 'get',
                dataType: 'json',
                beforeSend: function (xhr, settings) {
                    xhr.setRequestHeader('Authorization', 'Bearer ' + api_token);
                },
                success: function (data) {
                    $('#searchDropdownList').html('');

                    if(data.length > 0) {
                        $.each(data, function(index, value) {
                            $('#searchDropdownList').append('<li><a href="#'+ value.id
                                    + '">' + value.nick_name + '</a></li>');
                        });

                        $('#searchDropdownList li').unbind('click').bind('click', function() {
                            var id = $(this).children('a').attr('href').substr(1);
                            var nick_name = $(this).children('a').text();
                            console.log(id, nick_name);

                            if (confirm('要关注用户' + nick_name + '吗?'))
                            {
                                $.ajax({
                                    url: 'api/v1/user/follow/' + id,
                                    type: 'post',
                                    dataType: 'json',
                                    beforeSend: function (xhr, settings) {
                                        xhr.setRequestHeader('Authorization', 'Bearer ' + api_token);
                                    },
                                    success: function (data) {
                                        console.log("关注用户" + nick_name);
                                    },
                                    error: errorHandler
                                });
                                getFollowees();
                            }
                            $dropdown.dropdown('close');
                        });

                        if(!data.active) {
                            $dropdown.dropdown('open');
                        }
                    }
                },
                error: errorHandler
            });
        });

        getFollowees();

        $('#quitButton').click(function() {
            localStorage.clear();
            window.location.href = '/';
        });

        $('#messageSendButton').click(sendMsg);

        function errorHandler(data)
        {
            var error = JSON.parse(data.responseText);
            alert(error.msg);
        }

        function getFollowees()
        {
            $.ajax({
                url:'api/v1/user/follow',
                type:'get',
                dataType:'json',
                beforeSend: function(xhr, settings) { xhr.setRequestHeader('Authorization','Bearer ' + api_token); },
                success: function(data) {
                    $('#collapse-followee').html('');
                    $.each(data, function(index, value) {
                        var nickName = value.nick_name;
                        $('#collapse-followee').append('<li><a href="#' + value.id + '"> ' + nickName +
                                '<span class="am-badge am-badge-warning am-round am-margin-right am-fr"></span></a></li>');

                        // 将数据存入 local storage
                        var followees = localStorage.followees;
                        if(followees === undefined) {
                            followees = [];
                        } else {
                            followees = JSON.parse(followees);
                        }

                        var exist = false;
                        $.each(followees, function(i, v) {
                            if(v.id == value.id) {
                                exist = true;
                                return false;
                            }
                        });
                        if(exist == false) {
                            followees.push(value);
                        }
                        localStorage.followees = JSON.stringify(followees);
                    });
                    $('#collapse-followee li a').unbind(followee_click).click(followee_click);
                },
                error : errorHandler
            });
        }

        function followee_click()
        {
            var followee_id = $(this).attr('href').substr(1);
            var followees = JSON.parse(localStorage.followees);

            var followee = null;
            $.each(followees, function(index, value) {
                if(value.id == followee_id) {
                    followee = value;
                    return false;
                }
            });

            $('#displayWall').css('display', 'block');
            $('#messageBox').html('');

            roomId = followee.conv_id;
            console.log('room id: ' + roomId);
            console.log(localStorage.nick_name);
            clientId = localStorage.nick_name;
            follower_name = localStorage.nick_name;
            main();

//            $('#commentContainer').html('');
//
//            $('#commentContainer').append('<li class="am-comment">');
//            $('#commentContainer').append('<a href="#"><img src="http://s.amazeui.org/media/i/demos/bw-2014-06-19.jpg?imageView/1/w/96/h/96" alt="" class="am-comment-avatar" width="48" height="48"></a>');
//            $('#commentContainer').append('<div class="am-comment-main">');
//            $('#commentContainer').append('<header class="am-comment-hd">');
//            $('#commentContainer').append('<div class="am-comment-meta"><a href="#" class="am-comment-author">某人</a> <time class="am-fr">2014-7-12 15:30</time></div>');
//            $('#commentContainer').append('</header">');
//            $('#commentContainer').append('<div class="am-comment-bd"><p>遵循 “移动优先（Mobile First）”、“渐进增强（Progressive enhancement）”的理念，可先从移动设备开始开发网站，逐步在扩展的更大屏幕的设备上，专注于最重要的内容和交互，很好。</p></div>');
//            $('#commentContainer').append('</div>');
//            $('#commentContainer').append('</li>');
//
//            $('#collapse-dialog-title').text(followee.nick_name);
//            $('#dialogContainer').css('display', 'block');
        }

    });
</script>
</body>
</html>
