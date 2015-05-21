$(document).ready(function(){
	var o = $("#vuln-trup [selected]" ).html();
	$('.select-type span').html(o);
	o = $("#vuln-encoding [selected]").html();
	$('.select-coding span').html(o);
})

$(function(){
	// select的相关操作 -----------------------------------------------------
    $(".select-type select").change(function(){
    	var o;
    	var opt = $('.select-type select option');
    	opt.each(function(i){
    		if (opt[i].selected == true){
    			o = opt[i].innerHTML;
    		}
    	})
    	$('.select-type span').html(o);
    	$("#vuln-trup").blur();
    })
    $(".select-type select option").click(function(){
    	$(".select-type select").trigger('change');
    })

    $(".select-coding select").change(function(){
        var o;
        var opt = $('.select-coding select option');
        opt.each(function(i){
            if (opt[i].selected == true){
                o = opt[i].innerHTML;
            }
        })
        $('.select-coding span').html(o);
        $("#vuln-encoding").blur();
    })
    $(".select-coding select option").click(function(){
    	$(".select-coding select").trigger('change');
    })
    // select的相关操作 ----------------------------------------/**/

    // Scan 按钮的提交操作 ------------------------------------------
	$('#sub-path').click(function(){
		if( !scanCheck() ){
			return false;
		}
        var path = $('#file-path').val();
        // $('#path').attr('href',path.replace('\\','/'));
        $('.filepath a abbr').attr('title',path);
        $('.filepath a abbr').html(path);
        $('.filepath').css({'opacity':'1'});

        $('.waiting').css({'display':'block','opacity':'1'});
		sendScanReq();
        return false;
	})

    // #err_cont的显示关闭 ---------------------------------------------
    $('#err_cont a').click(function(){
        $('#err_cont').animate({'opacity':'0'}, 0.8);
        setTimeout(disappear_err_cont, 800);
        return false;
    })
    function disappear_err_cont(){
        $('#err_cont').css({'display':'none'});
        $('#err_cont span').remove();
    }

    /*/ 隐藏导航栏的控制函数 ---------------------------------------------
    $('.menu').mousedown(function(){
        var menu = $('.menu');
        if( menu.height() == 100 && menu.width() == $(window).width() ){
            menu.css({'margin-top':'0px','margin-left':'0px'});
        }
        else{
            menu.css({'margin-left':'0px','margin-top':'0px'});
        }
    })
    $('.menu').mouseleave(function(){
        var menu = $('.menu');
        if( menu.height() == 100 && menu.width() == $(window).width() ){
            menu.css({'margin-top':'-60px','margin-left':'0px'});
        }
        else{
            menu.css({'margin-left':'-200px','margin-top':'0px'});
        }
    })
    // ---------------------------------------------------------------------/**/
}) // 外层函数结尾


// 对输入的文件名进行检查 -----------------------------------------
function scanCheck(){
	if( !$('#file-path').val() ){
		$('#err_cont').append('<span>请输入完整路径！</span>');
        $('#err_cont').css({'display':'block'}).animate({'opacity':'1'},0.8);
		return false;
	}
	return true;
}
// -----------------------------------------------------------/**/
 
// 发送Scan的AJAX请求 ----------------------------------------
function sendScanReq()
{
    $.ajax({
        type : "POST",
        url : "main.php",
        dataType : "text",
        data: {    // 使用post方法，需要设置data属性，指定传递的参数
            path :  $("#file-path").val(),
            type :　$("#vuln-trup").val(),
            encoding : $("#vuln-encoding").val()
        },
        success : function( data ){
            if( !data ){ // 判断是否有数据返回
                $('#err_cont').append('<span>'+data+'</span>');
                $('#err_cont').css({'display':'block'}).animate({'opacity':'1'},0.8);
            }
            $('.content-panel').html(data);
            $('.waiting').css({'opacity':'0'});
            setTimeout(disappear_waiting, 600);
            function disappear_waiting(){
                $('.waiting').css({'display':'none'});
            }
        },
        error :  function( jqXHR ){
            alert("发生错误：" + jqXHR.status);
        }
    });
}
// ----------------------------------------------------------/**/
// 发送re搜索的AJAX请求 -----------------------------------------
function sendSearchReq()
{
    $.ajax({
        type : "POST",
        url : "index.php",
        dataType : "json",
        data: {    // 使用post方法，需要设置data属性，指定传递的参数
            path :  $("#file-path").val(),
            regex : $("reg-ex").val()
        },
        success : function( data ){
            // option    
        },
        error :  function( jqXHR ){
            alert("发生错误：" + jqXHR.status);
        }
    });
}
// ------------------------------------------------------------/**/