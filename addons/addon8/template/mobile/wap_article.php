<?php defined('SYSTEM_IN') or exit('Access Denied');?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <style>
        *{
            padding: 0;
            margin: 0;
        }
        .content img{
            display: block;
            width: 100%;
        }
        p{
            line-height:24px;
        }
    </style>
    <title><?php echo $article['title'];?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="format-detection" content="telephone=no">
    <link href="<?php echo WEBSITE_ROOT . 'themes/wap/__RESOURCE__'; ?>/recouse/css/bjdetail.css" rel="stylesheet"  type="text/css" />
    <script type="text/javascript" src="<?php echo WEBSITE_ROOT . 'themes/wap/__RESOURCE__'; ?>/script/jquery-1.7.2.min.js"></script>
    <!--引入懒加载的js文件-->
    <script type="text/javascript"	src="<?php echo WEBSITE_ROOT . 'themes/wap/__RESOURCE__'; ?>/recouse/js/jquery.lazyload.min.js"></script>
    <script>
        var open_id = "<?php echo checkIsLogin();?>";
        var get_ajax_url = "";
        <?php if($_GP['is_app']){  ?>
        var is_app   = true;
        <?php }else{ ?>
        var is_app   = false;
        <?php } ?>

        function browserFun(){
            var ua = navigator.userAgent.toLowerCase();
            if(navigator.userAgent.match(/(iPhone|iPod|iPad);?/i)){
                return 'ios';
            }
            if(navigator.userAgent.match(/android/i)){
                return 'android';
            }
        }
		
		//懒加载的初始化
		$(function(){
			$("img.lazy").lazyload({
				 threshold : 50,
				 failure_limit : 10,
				 effect : "fadeIn"
			});
		})	

    </script>
</head>
<body>

<?php if(empty($_GP['is_app'])){  ?>
    <div style="height: 20px;padding: 7px 15px;background: #f8f8f8;text-align: center;font-weight: bolder;">
        <a href="javascript:;" class="return"><img src="<?php echo WEBSITE_ROOT . 'themes/wap/__RESOURCE__'; ?>/openshop/images/return.png" width="8px" height="13px" style="float: left;margin-top: 2px;"></a>
        <span style="display: inline-block;width: 90%;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;"></span>
    </div>
    <div style="height: 2px;background: #EEEEEE;margin-bottom: 1px;"></div>
<?php } ?>
<div class="content">	 
    <?php echo $article['content'];?>
</div>

</body>
<script>
    $(".return").click(function(){
        //没有上一页就返回首页
        if(document.referrer.length == 0){
            window.location.href = "index.php";
        }else{
            var newHref = document.referrer;
            $(".return").attr("href",newHref);
        }

    })
    //自定义页面有时候是一个宣传页面有时候是一个内容
    //宣传页不需要间隙但是内容需要间隙
    var obj = document.getElementsByTagName("p")[0];
    $(obj).css('margin-top','8px');

    function tip(msg,autoClose){
        var div = $("#poptip");
        var content =$("#poptip_content");
        if(div.length<=0){
            div = $("<div id='poptip'></div>").appendTo(document.body);
            content =$("<div id='poptip_content'>" + msg + "</div>").appendTo(document.body);
        }
        else{
            content.html(msg);
            content.show(); div.show();
        }
        if(autoClose) {
            setTimeout(function(){
                content.fadeOut(500);
                div.fadeOut(500);

            },1000);
        }
    }
    function tip_close(){
        $("#poptip").fadeOut(500);
        $("#poptip_content").fadeOut(500);
    }

    function isLogin(msg){
        var ua = browserFun();
        if( ua == "ios" ){
            if( msg.openid !=""){
                openid_val = msg.openid;
            }else{
                return;
            }
        }
    }

    function AppLogin(){
        var app = browserFun();
        if( app == "ios" ){
            window.webkit.messageHandlers.mihaiapp.postMessage({login:""});
        }else if( app=="android" ){
           window.JsInterface.login("get_ajax_android");
        }
    }

    function callLogin(){
        if(is_app){
            AppLogin();
        }else{
            tip('请先登录！');
            setTimeout(function(){
                var url = "index.php?mod=mobile&name=shopwap&do=login";
                window.location.href = url;
            },2000)
        }
    }
    
    function get_ajax_android(msg){
        if( msg.openid !=""){
            open_id = msg.openid;
        }else{
            return;
        }
    }


</script>

</html>