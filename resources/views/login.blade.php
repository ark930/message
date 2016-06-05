
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>Login Page | Message</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <link rel="stylesheet" href="http://cdn.amazeui.org/amazeui/2.7.0/css/amazeui.min.css"/>
    <style>
        .header {
            text-align: center;
        }
        .header h1 {
            font-size: 200%;
            color: #333;
            margin-top: 30px;
        }
        .header p {
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="header">
    <div class="am-g">
        <h1>Message</h1>
    </div>
    <hr />
</div>
<div class="am-g">
    <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered">
        <form method="post" class="am-form am-form-horizontal" action="#">
            <div class="am-form-group am-form-group-sm">
                <div class="am-u-sm-12">
                    <input type="text" name="" id="username" value="" placeholder="手机号" required autofocus>
                </div>
            </div>
            <br>
            <div class="am-form-group am-form-group-sm">
                <div class="am-u-sm-10">
                    <input type="text" name="" id="verifyCode" value="" placeholder="验证码" required>
                </div>
                <div class="am-u-sm-2">
                    <button id="requireVerifyCode" class="am-btn am-btn-primary am-radius am-btn-sm am-fl">获 取</button>
                </div>
            </div>
            <br>
            <div class="am-cf">
                <input id="submit" type="submit" name="" value="登 录" class="am-btn am-btn-primary am-radius am-btn-block">
            </div>
        </form>
    </div>
</div>
<script src="http://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>

<script>
    $(function() {
        $('#requireVerifyCode').click(function(e) {
            e.preventDefault();

            var username = $('#username').val();
            if(!username) {
                alert('请输入手机号');
                return;
            }

            $.ajax({
                url:'api/v1/verifycode',
                data: {
                    username : username
                },
                type:'post',
                dataType:'json',
                success: function(data) {
                    console.log('success');
                    countdown(60, $('#requireVerifyCode'));
                },
                error : errorHandler
            });

        });

        $('#submit').click(function(e) {
            e.preventDefault();

            var username = $('#username').val();
            if(!username) {
                alert('请输入手机号');
                return;
            }

            var verifyCode = $('#verifyCode').val();
            if(!verifyCode) {
                alert('请输入验证码');
                return;
            }

            $.ajax({
                url:'api/v1/login',
                data: {
                    username : username,
                    verify_code : verifyCode
                },
                type:'post',
                dataType:'json',
                success:  function(data) {
                    console.log('login success');
                    localStorage.api_token = data.api_token;
                    localStorage.nick_name = data.nick_name;

                    window.location.href = 'im';
                },
                error : errorHandler
            });
        });

        function errorHandler(data)
        {
            var error = JSON.parse(data.responseText);
            alert(error.msg);
        }

        function countdown(time, button) {
            console.log(time);
            if (time == 0) {
                button.attr("disabled", false);
                button.text("获取");
            } else {
                button.attr("disabled", true);
                button.text("重新发送(" + time + ")");
                time--;
                setTimeout(function() {
                    countdown(time, button)
                }, 1000);
            }
        }

    });
</script>
</body>
</html>
