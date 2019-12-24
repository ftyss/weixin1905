<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>促销</title>
</head>
<body>
    <h3>新年大促销</h3>
    <script type="text/javascript" src="http://res2.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
    <script type="text/javascript">
        wx.config({
                debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
                appId: '{{$appid}}', // 必填，公众号的唯一标识
                timestamp: '{{$timestamp}}', // 必填，生成签名的时间戳
                nonceStr: '{{$noncestr}}', // 必填，生成签名的随机串
                signature: '{{$signature}}',// 必填，签名
                jsApiList: ['updateAppMessageShareData','updateTimelineShareData'] // 必填，需要使用的JS接口列表
        });

        wx.ready(function(){
            // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，则可以直接调用，不需要放在ready函数中。
            //分享给好友
            wx.ready(function () {   //需在用户可能点击分享按钮前就先调用
                wx.updateAppMessageShareData({ 
                    title: '元旦大促销', // 分享标题
                    desc: '听说的', // 分享描述
                    link: 'http://1905.fangtaoys.com/wx/newyear', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                    imgUrl: 'http://1905.fangtaoys.com/img/duang.jpg', // 分享图标
                    success: function () {
                    // 设置成功
                    }
                })
            });

            //分享给朋友圈
            wx.ready(function () {      //需在用户可能点击分享按钮前就先调用
                wx.updateTimelineShareData({ 
                    title: '元旦大促销', // 分享标题
                    link: 'http://1905.fangtaoys.com/wx/newyear', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
                    imgUrl: 'http://1905.fangtaoys.com/img/duang.jpg', // 分享图标
                    success: function () {
                    // 设置成功
                    }
                })
            }); 
        });
    </script>
</body>
</html>