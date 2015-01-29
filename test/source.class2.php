<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class News extends CI_Controller {

	function __construct(){
		parent::__construct();
        	$this->load->helper('url');
        	$this->load->helper('form');
                $this->load->library('session'); //加载session
		$this->load->model('CsdjDB');
		$this->load->model('CsdjSkins');
		$this->load->model('CsdjCache');
	}

        //文章主页
	public function index()
	{
            $data='';

            //判断运行模式
            $cscms=$this->security->xss_clean($this->input->get('cs', TRUE));
	    if(Web_Mode==3 && $cscms!="html"){ 
                    header("Location: ".Html_Nindex.""); 
                    exit;
            }

	    $cache_id ="news_index";
	    if(!($this->CsdjCache->start($cache_id))){
                $this->load->get_templates();
		$template=$this->load->view('news-index.html',$data,true);
		$Mark_Text=str_replace("{cscms:title}","文章首页 - ".Web_Name,$template);
                $this->CsdjSkins->skins($Mark_Text,$data);
		$this->CsdjCache->end();
	    }
	}

        //分类列表
	public function lists()
	{
            $data='';$data_content='';
            $fid = $this->uri->segment(3);   //方式
            $id = intval($this->uri->segment(4));   //ID
            $page  = intval($this->uri->segment(5));   //页数
            if($page==0) $page=1;
                
            if($id==0){
                exit($this->CsdjSkins->Msg_url('出错了，ID不能为空！',Web_Path));
            }

            $aliasname="";
            //判断运行模式
            $cscms=$this->input->get('cs', TRUE);
	    if(Web_Mode==3 && $cscms!="html"){ 
                    $Htmllink=$this->CsdjSkins->LinkUrl('lists','news',$fid,$id,$page);
                    header("Location: ".$Htmllink.""); 
                    exit;
            }

	    $cache_id ="news_list_".$fid."_".$id."_".$page;
	    if(!($this->CsdjCache->start($cache_id))){

		$row=$this->CsdjDB->get_select ('class','CS_ID','*',''.$id.'');
		if(!$row){
                    $aliasname=$id;
                    $row=$this->CsdjDB->get_select ('class','CS_AliasName','*',''.$id.'');
                    if(!$row){
                           exit($this->CsdjSkins->Msg_url('出错了，该分类不存在！',Web_Path));
                    }
		}
                $id=$row[0]->CS_ID;
                if($row[0]->CS_SID!=2){
                      exit($this->CsdjSkins->Msg_url('出错了，该分类不存在！',Web_Path));
                }

                if(empty($row[0]->CS_Template)){
                      $CS_Template='news-list.html';
                }else{
                      $CS_Template=$row[0]->CS_Template;
                }

                $this->load->get_templates();
		$template=$this->load->view($CS_Template,$data,true);

        	//SEO标题
        	if (empty($row[0]->CS_Title)){
               		$seo_title=$row[0]->CS_Name." - ".Web_Name;
        	}else{
               		$seo_title=$row[0]->CS_Title;
        	}
        	//SEO关键词
        	if (empty($row[0]->CS_Keywords)){
               		$seo_keywords=$row[0]->CS_Name."";
        	}else{
              		$seo_keywords=$row[0]->CS_Keywords;
        	}
        	//SEO描述
        	if (empty($row[0]->CS_Description)){
               		$seo_description=$row[0]->CS_Name." 是由".Web_Name."提供。";
        	}else{
               		$seo_description=$row[0]->CS_Description;
        	}
		$Mark_Text=$this->CsdjSkins->TopandBottom($template);
		$Mark_Text=str_replace("[news:classname]",$row[0]->CS_Name,$Mark_Text);
		$Mark_Text=str_replace("{cscms:title}",$seo_title,$Mark_Text);
		$Mark_Text=str_replace("{cscms:keywords}",$seo_keywords,$Mark_Text);
		$Mark_Text=str_replace("[news:classid]",$row[0]->CS_ID,$Mark_Text);
		$Mark_Text=str_replace("[news:sort]",$fid,$Mark_Text);

		//预先除了分页
		$pagenum=$this->CsdjSkins->GetPageNum($Mark_Text);
		preg_match_all('/{cscms:news(.*?pagesize=([\S]+).*?)}([\s\S]+?){\/cscms:news}/',$Mark_Text,$page_arr);//判断是否有分页标识
		if(!empty($page_arr) && !empty($page_arr[2])){

			$sqlstr=$this->CsdjSkins->Mark_Sql('news',$page_arr[1][0],$id,$fid);
			$Arr=$this->CsdjSkins->SpanPage($sqlstr,$page_arr[2][0],$pagenum,'lists','news',$fid,$row[0]->CS_ID,1,$page,$aliasname);//sql,每页显示条数
			$result=$this->CsdjDB->db->query($Arr[2]);
			$recount=$result->num_rows();
			if($recount==0){
				$data_content="<div align=center><strong><font style=font-size:12px;>该分类暂无数据！</font></strong></div>";
			}else{
				if($result){
					$sorti=1;
					foreach ($result->result() as $row2) {
						$datatmp=$this->CsdjSkins->Datanews($page_arr[0][0],$page_arr[3][0],$row2,$sorti);
						$sorti=$sorti+1;
						$data_content.=$datatmp;
					}
				}
			}// end if recount
			$Mark_Text=$this->CsdjSkins->Page_Mark($Mark_Text,$Arr);	
			$Mark_Text=str_replace($page_arr[0][0],$data_content,$Mark_Text);
			unset($Arr);
                }
		unset($page_arr);
		$Mark_Text=$this->CsdjSkins->Common_Mark($Mark_Text,$id);
		$Mark_Text=str_replace("{cscms:description}",$seo_description,$Mark_Text);
		$Mark_Text=str_replace("[news:classlink]",$this->CsdjSkins->LinkUrl('lists','news','id',$row[0]->CS_ID),$Mark_Text);
		$Mark_Text=str_replace('[news:classnlink]',$this->CsdjSkins->LinkUrl('lists','news','new',$row[0]->CS_ID),$Mark_Text);
		$Mark_Text=str_replace('[news:classhlink]',$this->CsdjSkins->LinkUrl('lists','news','hits',$row[0]->CS_ID),$Mark_Text);
		$Mark_Text=str_replace('[news:classtlink]',$this->CsdjSkins->LinkUrl('lists','news','reco',$row[0]->CS_ID),$Mark_Text);
		$Mark_Text=str_replace('[news:classylink]',$this->CsdjSkins->LinkUrl('lists','news','yue',$row[0]->CS_ID),$Mark_Text);
		$Mark_Text=str_replace('[news:classzlink]',$this->CsdjSkins->LinkUrl('lists','news','zhou',$row[0]->CS_ID),$Mark_Text);
		$Mark_Text=str_replace('[news:classrlink]',$this->CsdjSkins->LinkUrl('lists','news','ri',$row[0]->CS_ID),$Mark_Text);
		$this->CsdjSkins->skins($Mark_Text,$data);
		$this->CsdjCache->end();
	    }
	}


        //搜索列表
	public function so()
	{
            $data='';$data_content='';
            $fid = $this->uri->segment(3);   //方式
            $key = $this->input->get_post('key');   //关键字
            $page  = intval($this->input->get('p', TRUE));   //页数
            if($page==0) $page=1;
                
	    $cache_id ="news_so_".$fid."_".$key."_".$page;
	    if(!($this->CsdjCache->start($cache_id))){

	    $key = $this->CsdjSkins->safe_replace(trim($key));
	    $key = htmlspecialchars(strip_tags($key));
	    $key = str_replace('%', '', $key);	//过滤'%'，用户全文搜索
            $key=$this->CsdjSkins->rurlencode($key);

                $CS_Template='so-news-'.$fid.'.html';

                $this->load->get_templates();
		$template=$this->load->view($CS_Template,$data,true);

		$Mark_Text=$this->CsdjSkins->TopandBottom($template);
		$Mark_Text=str_replace("{cscms:sokey}",$key,$Mark_Text);
		$Mark_Text=str_replace("{cscms:title}","搜索关键词".$key." - ".Web_Name,$Mark_Text);
		$Mark_Text=str_replace("{cscms:keywords}",$key,$Mark_Text);
		$Mark_Text=str_replace("{cscms:description}",stripslashes(html_entity_decode(Web_Description)),$Mark_Text);
		//预先除了分页
		$pagenum=$this->CsdjSkins->GetPageNum($Mark_Text);
		preg_match_all('/{cscms:news(.*?pagesize=([\S]+).*?)}([\s\S]+?){\/cscms:news}/',$Mark_Text,$page_arr);//判断是否有分页标识
		if(!empty($page_arr) && !empty($page_arr[2])){


                        $sqlstr="select * from ".CS_SqlPrefix."news where CS_YID=0 and CS_HID=0 and CS_Name like '%".$key."%' order by CS_AddTime desc";

			$Arr=$this->CsdjSkins->SpanPage($sqlstr,$page_arr[2][0],$pagenum,'so','news',$fid,urlencode($key),1,$page);//sql,每页显示条数
			$result=$this->CsdjDB->db->query($Arr[2]);
			$recount=$result->num_rows();
			if($recount==0){
				$data_content.="<div align=center><strong>对不起,没有《<font color=#FF0000>".$key."</font>》相关数据!</strong></div>";
			}else{
				if($result){
					$sorti=1;
					foreach ($result->result() as $row2) {
						$datatmp=$this->CsdjSkins->Datanews($page_arr[0][0],$page_arr[3][0],$row2,$sorti);
						$sorti=$sorti+1;
						$data_content.=$datatmp;
					}
				}
			}// end if recount
			$Mark_Text=$this->CsdjSkins->Page_Mark($Mark_Text,$Arr);	
			$Mark_Text=str_replace($page_arr[0][0],$data_content,$Mark_Text);
			unset($Arr);
                }
		unset($page_arr);
		$this->CsdjSkins->skins($Mark_Text,$data);
		$this->CsdjCache->end();
	    }
	}


        //文章内容页面
	public function show()
	{
            $data=$data_content='';
            $fid = $this->uri->segment(3);   //方式
            $id = intval($this->uri->segment(4));   //ID
                
            if($id==0){
                exit($this->CsdjSkins->Msg_url('出错了，ID不能为空！',Web_Path));
            }

            //判断运行模式
            $cscms=$this->security->xss_clean($this->input->get('cs', TRUE));
	    if(Web_Mode==3 && $cscms!="html"){
                    $Htmllink=$this->CsdjSkins->LinkUrl('news','show',$fid,$id,$id);
                    header("Location: ".$Htmllink.""); 
                    exit;
            }

		$row=$this->CsdjDB->get_select ('news','CS_ID','*',''.$id.'');
		if(!$row){
		    exit($this->CsdjSkins->Msg_url('抱歉，该文章记录不存在！',Web_Path));
		}
                if($row[0]->CS_YID==1){
                    exit($this->CsdjSkins->Msg_url('抱歉，该文章还没有审核！',Web_Path));
                }


		if($this->session->userdata('cs_id')){
                     $uid=$this->session->userdata('cs_id');
                }else{
                     $uid=0;
                }
		if($this->session->userdata('cs_name')){
                     $user=$this->session->userdata('cs_name');
                }else{
                     $user='';
                }

                if($row[0]->CS_Vip>0){
	            $this->load->model('CsdjUser');
		    if(!$this->CsdjUser->User_Login(1)){
                       exit($this->CsdjSkins->Msg_url('出错了，该文章设置了阅读权限，请先登入后在阅读！',site_url("user/login")));
                    }else{
                       $sql="SELECT CS_Vip FROM ".CS_SqlPrefix."user where cs_id='".$uid."'";
		       $rowu=$this->CsdjDB->get_all($sql);
                       $vip=$rowu[0]->CS_Vip;
                    }

                    if($vip==0){
                       exit($this->CsdjSkins->Msg_url('出错了，您当前的级别不能阅读该文章！',site_url("user/pay/vip")));
                    }

                    //判断级别权限
                        $sqlz="SELECT * FROM ".CS_SqlPrefix."userzu where cs_id=".$vip."";
		        $rowz=$this->CsdjDB->get_all($sqlz);
		        if(!$rowz){
                             exit($this->CsdjSkins->Msg_url('出错了，该会员组不存在！',site_url("user/pay/vip")));
                        }else{
                             if($this->CsdjSkins->Getqx('news_'.$row[0]->CS_CID,$rowz[0]->CS_Quanx)!='ok'){
                                  exit($this->CsdjSkins->Msg_url('抱歉，您所在的会员组不能阅读该文章！',site_url("user/pay/vip")));
                             }
                        }
                }

	    $cache_id ="news_show_".$id;
	    if(!($this->CsdjCache->start($cache_id))){

                if(empty($row[0]->CS_Template)){
                      $CS_Template='news-show.html';
                }else{
                      $CS_Template=$row[0]->CS_Template;
                }

                $this->load->get_templates();
		$template=$this->load->view($CS_Template,$data,true);

        	//SEO标题
        	if (empty($row[0]->CS_Title)){
               		$seo_title=$row[0]->CS_Name." - ".Web_Name;
        	}else{
               		$seo_title=$row[0]->CS_Title;
        	}
        	//SEO关键词
        	if (empty($row[0]->CS_Keywords)){
               		$seo_keywords=$row[0]->CS_Name."";
        	}else{
              		$seo_keywords=$row[0]->CS_Keywords;
        	}
        	//SEO描述
        	if (empty($row[0]->CS_Description)){
               		$seo_description=$row[0]->CS_Name." 是由".Web_Name."提供。";
        	}else{
               		$seo_description=$row[0]->CS_Description;
        	}

		$Mark_Text=str_replace("{cscms:title}",$seo_title,$template);
		$Mark_Text=str_replace("{cscms:keywords}",$seo_keywords,$Mark_Text);
		$Mark_Text=str_replace("{cscms:description}",$seo_description,$Mark_Text);


                //静态模式,动态人气
                if(Web_Mode==3){
			$Mark_Text=str_replace('[news:hits]',"<script src=".site_url('news/hits/dt/hits/'.$id)."></script>",$Mark_Text);
			$Mark_Text=str_replace('[news:rhits]',"<script src=".site_url('news/hits/dt/rhits/'.$id)."></script>",$Mark_Text);
			$Mark_Text=str_replace('[news:zhits]',"<script src=".site_url('news/hits/dt/zhits/'.$id)."></script>",$Mark_Text);
			$Mark_Text=str_replace('[news:yhits]',"<script src=".site_url('news/hits/dt/yhits/'.$id)."></script>",$Mark_Text);
                }
		$Mark_Text=$this->CsdjSkins->Common_Mark($Mark_Text,$id);
		//上下编开始
                preg_match_all('/news:slink/',$Mark_Text,$arr);
		if(!empty($arr[0]) && !empty($arr[0][0])){
		      $sqlu="Select CS_ID,CS_Name,CS_CID from ".CS_SqlPrefix."news where CS_YID=0 and CS_HID=0 and CS_ID>".$id." order by cs_id asc limit 1";
		      $rowu=$this->CsdjDB->get_all($sqlu);
		      if($rowu){
		             $Mark_Text=str_replace('[news:sid]',$rowu[0]->CS_ID,$Mark_Text);
		             $Mark_Text=str_replace('[news:sname]',$rowu[0]->CS_Name,$Mark_Text);
		             $Mark_Text=str_replace('[news:slink]',$this->CsdjSkins->LinkUrl('show','news','id',$rowu[0]->CS_CID,$rowu[0]->CS_ID),$Mark_Text);
		      }else{
		             $Mark_Text=str_replace('[news:sid]','0',$Mark_Text);
		             $Mark_Text=str_replace('[news:sname]','没有了',$Mark_Text);
		             $Mark_Text=str_replace('[news:slink]','#',$Mark_Text);
		      }
		}
                preg_match_all('/news:xlink/',$Mark_Text,$arr);
		if(!empty($arr[0]) && !empty($arr[0][0])){
		      $sqld="Select CS_ID,CS_Name,CS_CID from ".CS_SqlPrefix."news where CS_YID=0 and CS_HID=0 and CS_ID<".$id." order by cs_id desc limit 1";
		      $rowd=$this->CsdjDB->get_all($sqld);
		      if($rowd){
		             $Mark_Text=str_replace('[news:xid]',$rowd[0]->CS_ID,$Mark_Text);
		             $Mark_Text=str_replace('[news:xname]',$rowd[0]->CS_Name,$Mark_Text);
		             $Mark_Text=str_replace('[news:xlink]',$this->CsdjSkins->LinkUrl('show','news','id',$rowd[0]->CS_CID,$rowd[0]->CS_ID),$Mark_Text);
		      }else{
		             $Mark_Text=str_replace('[news:xid]','0',$Mark_Text);
		             $Mark_Text=str_replace('[news:xname]','没有了',$Mark_Text);
		             $Mark_Text=str_replace('[news:xlink]','#',$Mark_Text);
		      }
		}
		$Mark_Text=$this->CsdjSkins->Datanews($Mark_Text,$Mark_Text,$row[0],'1'); //全部解析
		$Mark_Text.="<script src=".site_url('news/hits/id/'.$id)."></script>";//增加人气

		$this->CsdjSkins->skins($Mark_Text,$data);
		$this->CsdjCache->end();
	    }
	}

        //增加人气
	public function hits()
	{
           $ac = $this->uri->segment(3);   //方式
           if($ac=='id'){

                $id = intval($this->uri->segment(4));   //ID
		$row=$this->CsdjDB->get_select ('news','CS_ID','CS_Rhits,CS_Zhits,CS_Yhits,CS_Hits,CS_LookTime',''.$id.'');
		if(!$row){
			show_404();
		}
                //增加文章人气
                $daytime =$this->CsdjSkins->DateBj("d",$row[0]->CS_LookTime,date('Y-m-d H:i:s'));
	        if($daytime==0){
	            $updata['CS_Rhits']=$row[0]->CS_Rhits+1;
	        }else{
	            $updata['CS_Rhits']=1;
	        }
	        if($daytime < 7){
	            $updata['CS_Zhits']=$row[0]->CS_Zhits+1;
	        }else{
	            $updata['CS_Zhits']=1;
	        }
	        if($daytime <31){
	            $updata['CS_Yhits']=$row[0]->CS_Yhits+1;
	        }else{
	            $updata['CS_Yhits']=1;
	        }
	            $updata['CS_Hits']=$row[0]->CS_Hits+1;
	            $updata['CS_LookTime']=date('Y-m-d H:i:s');
                    $this->CsdjDB->get_update ('news',$id,$updata);

           }elseif($ac=='dt'){   //静态模式、动态人气

                $op = $this->uri->segment(4);   //类型
                $id = intval($this->uri->segment(5));   //ID
		$row=$this->CsdjDB->get_select ('news','CS_ID','CS_Yhits,CS_Zhits,CS_Rhits,CS_Hits',''.$id.'');
		if(!$row){
			show_404();
		}

                if($op=='yhits'){
                      echo "document.write('".$row[0]->CS_Yhits."');";
                }elseif($op=='zhits'){
                      echo "document.write('".$row[0]->CS_Zhits."');";
                }elseif($op=='rhits'){
                      echo "document.write('".$row[0]->CS_Rhits."');";
                }else{
                      echo "document.write('".$row[0]->CS_Hits."');";
                }
           }
	}
}


