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
        $('.count').css({'opacity':'1'});
        $('.waiting').css({'display':'block','opacity':'1'});
        startTimeCounter();
        timeDisplay();
		sendScanReq();
        return false;
	})

    // #err_cont的显示关闭 ---------------------------------------------
    $('#err_cont a').click(function(){
        $('#err_cont').animate({'opacity':'0'}, 0.8);
        setTimeout(function(){
        $('#err_cont').css({'display':'none'});
        $('#err_cont span').remove();
    }, 800);
        return false;
    })

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
function scanCheck()
{
	if( !$('#file-path').val() ){
		$('#err_cont').append('<span>请输入完整文件路径！</span>');
        $('#err_cont').css({'display':'block'}).animate({'opacity':'1'},0.8);
		return false;
	}
    if( !$('#project-path').val() ){
        $('#err_cont').append('<span>请输入文件所在工程路径！<br/><font size="3">若没有工程路径，则文件路径与工程路径相同。</font></span>');
        $('#err_cont').css({'display':'block'}).animate({'opacity':'1'},0.8);
        return false;
    }
	return true;
}
// -----------------------------------------------------------/**/
        
// 发送code viewer的AJAX请求 ----------------------------------------
function sendCodeViewReq( tag_a )
{
    var grandparent = tag_a.parentNode.parentNode;
    var codeFile = grandparent.getElementsByTagName('span');
    $.ajax({
        type : "POST",
        url : "CodeViewer.php",
        dataType : "json",
        data: {    // post的参数
            sinkPath :  $(codeFile[0]).html(),
            argPath : $(codeFile[6]).html()
        },
        success : function( data ){
            if( data.flag ){

                var sink_start = parseInt( $(codeFile[3]).html() );
                var sink_end = parseInt( $(codeFile[4]).html() );

                var arg_start = parseInt( $(codeFile[7]).html() );
                var arg_end = parseInt( $(codeFile[8]).html() );

                var code = findNext( tag_a );
                var line = /.+?\r?\n|.*?\?>|\r?[\s]+?/ig;
                var fomart = document.createElement('pre');

                var code_box = document.createElement('table');
                code_box.className = "code-viewer-sink";
                for (var i = 1; (result = line.exec(data.msg_sink)) != null; i++){
                    var code_line = document.createElement('tr');
                    var code_column_1 = document.createElement('td');
                    var code_column_2 = document.createElement('td');
                    code_column_1.appendChild( document.createTextNode(i) );
                    code_column_2.appendChild( document.createTextNode(result[0]) );

                    if( i >= sink_start && i <= sink_end ){//( HL_Call == cutstr(result[0]) ){
                        code_line.className = "highLight-call";
                    }
                    if( i >= arg_start && i <= arg_end && !data.msg_arg ){//( HL_Arg == cutstr(result[0]) && !data.msg_arg ){
                        code_line.className = "highLight-arg";
                    }
                    code_line.appendChild( code_column_1 );
                    code_line.appendChild( code_column_2 );
                    code_box.appendChild( code_line );
                }
                fomart.appendChild( code_box )
                code.appendChild( fomart );
                if( data.msg_arg ){
                    var code_selecter = document.createElement('div');
                    code_selecter.className = "code-selecter";
                    var sink_select = document.createElement('a');
                    var arg_select = document.createElement('a');
                    sink_select.appendChild( document.createTextNode('Sink Call') );
                    arg_select.appendChild( document.createTextNode('Sensitive Arg') );
                    sink_select.setAttribute('href', 'javascript:;');
                    arg_select.setAttribute('href', 'javascript:;');
                    sink_select.setAttribute('onclick', 'sinkCodeShow(this);return false;');
                    arg_select.setAttribute('onclick', 'argCodeShow(this);return false;');
                    sink_select.className = "sink-select";
                    arg_select.className = "arg-select";
                    code_selecter.appendChild( sink_select );
                    code_selecter.appendChild( arg_select );
                    code.appendChild( code_selecter );
                    var code_box = document.createElement('table');
                    code_box.className = "code-viewer-arg";
                    for (var i = 1; (result = line.exec(data.msg_arg)) != null; i++){
                        var code_line = document.createElement('tr');
                        var code_column_1 = document.createElement('td');
                        var code_column_2 = document.createElement('td');
                        code_column_1.appendChild( document.createTextNode(i) );
                        code_column_2.appendChild( document.createTextNode(result[0]) );
                        if( i >= arg_start && i <= arg_end ){
                            code_line.className = "highLight-arg";
                        }
                        code_line.appendChild( code_column_1 );
                        code_line.appendChild( code_column_2 );
                        code_box.appendChild( code_line );
                    }
                    fomart.appendChild( code_box );
                    code.appendChild( fomart );
                }
            }
            else{
                $(code).css({'height':'0px'});
                $(tag_a).html('Code Viewr');
                tag_a.flag = false;
                $('#err_cont').append('<span>'+data.msg+'</span>');
                $('#err_cont').css({'display':'block'}).animate({'opacity':'1'},0.8);
        return false;
            }
        },
        error :  function( jqXHR ){
            $('#err_cont').append('<span>Error, error code : '+jqXHR.status+'!</span>');
            $('#err_cont').css({'display':'block'}).animate({'opacity':'1'},0.8);
            return false;
        }
    });
}
// ----------------------------------------------------------/**/

// 发送Scan的AJAX请求 ----------------------------------------
function sendScanReq()
{
    $.ajax({
        type : "POST",
        url : "main.php",
        dataType : "text",
        data: {    // post的参数
            path :  $("#file-path").val(),
            prj_path : $("#project-path").val(),
            type :　$("#vuln-trup").val(),
            encoding : $("#vuln-encoding").val()
        },
        success : function( data ){
            stopTimeCounter();
            $('.timeused').html( 'Time Used : '+h+':'+m+':'+s+':'+ms );
            $('.waiting').css({'opacity':'0'});
            setTimeout(function(){
                $('.waiting').css({'display':'none'});
            }, 600);
            if( /工程不存在!/.test(data) ){ // 判断是否有数据返回
                $('#err_cont').append('<span>'+data+'</span>');
                $('#err_cont').css({'display':'block'}).animate({'opacity':'1'},0.8);
                return false;
            }
            var data_content = /<ul id="content-box">[\s\S]+?<\/ul>/.exec(data);
            var data_count = /<div class="count-box">[\s\S]+?<\/div>/.exec(data);
            $('.content-panel').html(data_content);
            $('.count').html(data_count);
            addResultOnclidc();
        },
        error :  function( jqXHR ){
            stopTimeCounter();
            $('#err_cont').append('<span>Error, error code : '+jqXHR.status+'!</span>');
            $('#err_cont').css({'display':'block'}).animate({'opacity':'1'},0.8);
            return false;
        }
    });
}
// ----------------------------------------------------------/**/


/*/ 发送re搜索的AJAX请求 -----------------------------------------
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

function addResultOnclidc()
{
    var contentBox = document.getElementById("content-box");
    if( contentBox ){
        if( contentBox.getElementsByTagName("a") ){
            var cBox_a = contentBox.getElementsByTagName("a");
            if( cBox_a.length ){
                for( var i = 0; i < cBox_a.length; i++){
                    cBox_a[i].setAttribute("onclick","showcode(this);return false;");
                }
            }
        }
    }
}
function showcode( tag_a )
{
    var code = findNext( tag_a );
    var code_selecter = null;
    if( elementChild(code) ){
        code_selecter = elementChild( code );
    }
    if( tag_a.flag ){
        $(code).css({'height':'0px'});
        $(code_selecter).css({'opacity':'0'});
        setTimeout(function(){
            $(tag_a).html('Code Viewr');
            $(code_selecter).css({'display':'none'});
        }, 600);
        tag_a.className = "";
        tag_a.flag = false;
    }
    else{
        $(code).html('');
        sendCodeViewReq( tag_a );
        $(code).css({'height':'190px'});
        $(code_selecter).css({'opacity':'1','display':'block'});
        $(tag_a).html('×');
        tag_a.className = "close";
        tag_a.flag = true;
    }
}
function sinkCodeShow( tag_a ){
    var arg = findNext( tag_a );
    var code_selecter = tag_a.parentNode;
    var code_pre = findNext( code_selecter );
    var code_table = elementChild( code_pre );
    $(code_table[0]).css({'display':'block','opacity':'1'});
    $(code_table[1]).css({'opacity':'0'});
    setTimeout(function(){
        $(code_table[1]).css({'display':'none'});
    }, 400);
    $(arg).css({'background':'rgba(0,0,0,0)'});
    $(tag_a).css({'background':'#f81'});
}
function argCodeShow( tag_a ){
    var sink = findPrevious( tag_a );
    var code_selecter = tag_a.parentNode;
    var code_pre = findNext( code_selecter );
    var code_table = elementChild( code_pre );
    $(code_table[1]).css({'display':'block','opacity':'1'});
    $(code_table[0]).css({'opacity':'0'});
    setTimeout(function(){
        $(code_table[0]).css({'display':'none'});
    }, 400);
    $(sink).css({'background':'rgba(0,0,0,0)'});
    $(tag_a).css({'background':'#2dcb70'});
}



// 获取上一个兄弟元素节点
function findPrevious( element )
{
    while( element.previousSibling ){
        if( element.previousSibling.nodeType == 1)
            return element.previousSibling;
        else
            element = element.previousSibling;
    }
    return null;
}
// 获取下一个兄弟元素节点
function findNext( element )
{
    while( element.nextSibling ){
        if( element.nextSibling.nodeType == 1)
            return element.nextSibling;
        else
            element = element.nextSibling;
    }
    return null;
}
// 获取下一个元素子节点
function elementChild( element )
{   
    var child = element.childNodes;
    if( child ){
        var elmChild = new Array();
        var j = 0;
        for( var i = 0; i < child.length; i++ ){
            if( child[i].nodeType == 1 ){
                elmChild[j] = child[i];
                j++;
            }
        }
        return elmChild;
    }
    return null;
}
function cutstr( str )
{
    var s = '';
    for(var i in str){
        if ( /[\s ]/.test(str[i]) ){
            continue;
        } 
        else{
            s += str[i];
        }
    }
    return s;
}

// timecounter
var start;
var status = 0;
var time = 0;
var h, m, s, ms;
function startTimeCounter()
{
    h = m = s = ms = 0;
    status = 1;
    start = new Date();
}

function stopTimeCounter()
{
    status = 0;
    setTimeout( function(){
        $('.timecounter').html('');
    }, 3000);
}

function timeDisplay()
{
    setTimeout("timeDisplay();", 50);
    if( status == 1 ){
        var now = new Date();
        // h = now.getHours() - start.getHours();
        // m = now.getMinutes() - start.getMinutes();
        // s = now.getSeconds() - start.getSeconds();
        time = now.getTime() - start.getTime();
        //time = time / 1000;
        h = Math.floor(time / 3600000);$('#h').html(h);
        time = time % 3600000;
        m = Math.floor(time / 60000);$('#m').html(m);
        time = time % 60000;
        s = Math.floor(time / 1000);$('#s').html(s);
        ms = time % 1000;$('#ms').html(ms);
    }
    
}