<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Music extends CI_Controller {

	function __construct(){
		parent::__construct();
        	$this->load->helper('url');
            $this->load->library('session'); //加载session
		$this->load->model('CsdjDB');
		$this->load->model('CsdjSkins');
		$this->load->model('CsdjUser');
		$this->CsdjUser->User_Login();
	}

	public function index()
	{
                $data='';
                $yid = intval($this->input->get('yid', TRUE));   //yid，1为分享，2为待审核，3为回收站
                $cid = intval($this->input->get('cid', TRUE));   //cid
                $page = intval($this->input->get('page', TRUE));   //page
                if($page==0) $page=1;


                $this->load->get_user_templates();
		$template=$this->load->view('dance-list.html',$data,true);
		$template=$this->CsdjSkins->TopandBottom($template,'user');

		$Mark_Text=str_replace("{cscms:title}","音乐 - 会员管理中心",$template);
		$Mark_Text=str_replace("{cscms:keywords}",stripslashes(html_entity_decode(Web_Keywords)),$Mark_Text);
		$Mark_Text=str_replace("{cscms:description}",stripslashes(html_entity_decode(Web_Description)),$Mark_Text);
		$Mark_Text=str_replace("[dance:cid]",$cid,$Mark_Text); //CID
		$Mark_Text=str_replace("[dance:yid]",$yid,$Mark_Text); //YID

		//预先除了分页
		$pagenum=$this->CsdjSkins->GetPageNum($Mark_Text);
		preg_match_all('/{cscms:dance(.*?pagesize=([\S]+).*?)}([\s\S]+?){\/cscms:dance}/',$Mark_Text,$page_arr);//判断是否有分页标识
		if(!empty($page_arr) && !empty($page_arr[2])){

                        $sqlstr="select * from ".CS_SqlPrefix."dance where CS_User='".$this->session->userdata('cs_name')."'";
                        if($yid==3){
                             $sqlstr.=" and cs_hid=1";
                        }else{
                             $sqlstr.=" and cs_hid=0";
                        }
                        if($yid==1 || $yid==2){
                            $yid=$yid-1;
                            $sqlstr.=" and cs_yid='$yid'";
                        }
                        if($cid>0){
                            $sqlstr.=" and cs_cid='$cid'";
                        }

                        $sqlstr.=" order by CS_AddTime desc";
                        $data_content='';
			$Arr=$this->CsdjSkins->SpanUsersPage($sqlstr,$page_arr[2][0],$pagenum,'user/music?cid='.$cid.'&yid='.$yid,$page);//sql,每页显示条数
			$result=$this->CsdjDB->db->query($Arr[2]);
			$recount=$result->num_rows();
			if($recount==0){
				$data_content="<div align=center><strong><font style=font-size:12px;>暂无数据！</font></strong></div>";
			}else{
				if($result){
					$sorti=1;
					foreach ($result->result() as $row2) {
						$datatmp=$this->CsdjSkins->DataDance($page_arr[0][0],$page_arr[3][0],$row2,$sorti);
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
		$Mark_Text=$this->CsdjSkins->Common_Mark($Mark_Text,$this->session->userdata('cs_name'));
	        $row=$this->CsdjDB->get_select ('user','CS_ID','*',''.$this->session->userdata('cs_id').'');
		$Mark_Text=$this->CsdjUser->getuser($Mark_Text,$row);
                $this->CsdjSkins->skins($Mark_Text,$data);
        }

	public function add()
	{
                $data='';
                $this->load->get_user_templates();
		$template=$this->load->view('dance-add.html',$data,true);
		$template=$this->CsdjSkins->TopandBottom($template,'user');

		$Mark_Text=str_replace("{cscms:title}","音乐 - 会员管理中心",$template);
		$Mark_Text=str_replace("{cscms:keywords}",stripslashes(html_entity_decode(Web_Keywords)),$Mark_Text);
		$Mark_Text=str_replace("{cscms:description}",stripslashes(html_entity_decode(Web_Description)),$Mark_Text);
		$Mark_Text=str_replace("[user:musicaddsave]",site_url('user/music/add_save'),$Mark_Text); //发表连接

        $token=md5(uniqid(rand(), true));
		$this->session->set_userdata('token',$token);
		$Mark_Text=str_replace("[user:token]",$token,$Mark_Text);

                $row=$this->CsdjDB->get_select ('user','CS_ID','*',''.$this->session->userdata('cs_id').'');
                if($row[0]->CS_Vip==0 && User_MusicNum>0){
		       $Mark_Text=str_replace("[user:musicaddcount]",User_MusicNum,$Mark_Text); //普通会员限制舞曲数量
                }else{
		       $Mark_Text=str_replace("[user:musicaddcount]",'不限制',$Mark_Text); //VIP会员限制舞曲数量
                }
		$Mark_Text=str_replace("[user:dayaddcount]",$this->CsdjSkins->getusercount($row[0]->CS_Name,'dance','day'),$Mark_Text); //会员今日上传数量
		$Mark_Text=$this->CsdjSkins->Common_Mark($Mark_Text,$this->session->userdata('cs_name'));
		$Mark_Text=$this->CsdjUser->getuser($Mark_Text,$row);
                $this->CsdjSkins->skins($Mark_Text,$data);
        }

	public function add_save()
	{
                $music['cs_name']=strip_tags($this->input->post('cs_name', TRUE));   //名称
                $music['cs_cid']=intval($this->input->post('cs_cid', TRUE));   //分类
                $music['cs_tid']=intval($this->input->post('cs_tid', TRUE));   //专集
                $music['cs_tags']=strip_tags($this->input->post('cs_tags', TRUE));   //关键词
                $music['cs_cion']=intval($this->input->post('cs_cion', TRUE));   //金币
                $music['cs_singerid']=intval($this->input->post('cs_singerid', TRUE));   //歌手
                $music['cs_singer']=trim($this->CsdjSkins->uhtml($this->input->post('cs_singer', TRUE)));   //歌手
                $music['cs_content']=$this->CsdjSkins->uhtml($this->input->post('cs_content'));   //歌词/介绍
                $music['cs_playurl']=$this->CsdjSkins->str_checkhtml($this->input->post('cs_playurl', TRUE));   //播放地址
                $music['cs_dx']=$this->CsdjSkins->str_checkhtml($this->input->post('cs_dx', TRUE));   //歌曲大小
                $music['cs_yz']=$this->CsdjSkins->str_checkhtml($this->input->post('cs_yz', TRUE));   //歌曲音质
                $music['cs_sc']=$this->CsdjSkins->str_checkhtml($this->input->post('cs_sc', TRUE));   //歌曲时长
                $music['cs_pic']=$this->CsdjSkins->str_checkhtml($this->input->post('cs_pic', TRUE));   //歌曲图片

				//token check
                $token=$this->input->post('token', TRUE);
				if(!$this->session->userdata('token') || $token!=$this->session->userdata('token'))  $this->CsdjSkins->Msg_url('非法提交数据!','javascript:history.back();'); 

                if(empty($music['cs_name']))  $this->CsdjSkins->Msg_url('歌曲名称不能为空!','javascript:history.back();'); 
                if(empty($music['cs_cid']) || $music['cs_cid']==0)  $this->CsdjSkins->Msg_url('请选择歌曲分类!','javascript:history.back();'); 
                if(empty($music['cs_playurl']))  $this->CsdjSkins->Msg_url('请选上传歌曲!','javascript:history.back();'); 


                if($music['cs_singerid']>0){ //判断歌手
                    $sql="SELECT CS_Name FROM ".CS_SqlPrefix."singer where cs_id=".$music['cs_singerid']."";
	                $row=$this->CsdjDB->get_all($sql); 
	                if(!$row){
                            $music['cs_singerid']=0;
                    }else{
                            $music['cs_singer']=$row[0]->CS_Name;
                    }
                }else{ //自定义歌手
                    $sql="SELECT CS_ID FROM ".CS_SqlPrefix."singer where cs_name='".$music['cs_singer']."'";
	                $row=$this->CsdjDB->get_all($sql); 
	                if($row){
                            $music['cs_singerid']=$row[0]->CS_ID;
                    }else{
                            $gsadd['CS_Name']=$music['cs_singer'];
                            $gsadd['CS_CID']=1;
                            $gsadd['CS_AddTime']=date('Y-m-d H:i:s');
                            $singerid=$this->CsdjDB->get_insert('singer',$gsadd);
                            $music['cs_singerid']=intval($singerid);
                    }
				}

                if(empty($music['cs_tid'])) $music['cs_tid']=0;
                if(empty($music['cs_cion'])) $music['cs_cion']=0;
                $music['CS_DownUrl1']=$music['cs_playurl'];
                $music['CS_UID']=$this->session->userdata('cs_id');
                $music['CS_User']=$this->session->userdata('cs_name');
                $music['CS_AddTime']=date('Y-m-d H:i:s');

                //判断审核
                if(User_DjFun==1){
                    $title='，请等待管理员审核';
                    $music['CS_YID']=1;
                    $url=site_url('user/music').'?yid=2';
                }else{
                    $title='';
                    $music['CS_YID']=0;
                    $url=site_url('user/music');
                }

                //获取当天上传成功数量
                $daycount=$this->CsdjSkins->getusercount($this->session->userdata('cs_name'),'dance','day');
                if(User_MusicNum>0 && $daycount >= User_MusicNum){
                     $this->CsdjSkins->Msg_url('今天已经上传了('.$daycount.')首歌曲,明天在传吧!','javascript:history.back();'); 
                }

                $res=$this->CsdjDB->get_insert('dance',$music);
                if($res>0){

                       if($daycount < Cion_Num && Cion_UpMusic>0 && User_DjFun==2){  //奖励金币
                              $title.='，系统奖励给您'.Cion_UpMusic.'个金币';
                              $this->db->query("update ".CS_SqlPrefix."user set CS_Cion=CS_Cion+".Cion_UpMusic." where CS_ID='".$this->session->userdata('cs_id')."'");
                       }

                       //写入动态
                       $dt['CS_CID']=1;
                       $dt['CS_DID']=$res;
                       $dt['CS_YID']=$music['CS_YID'];
                       $dt['CS_User']=$this->session->userdata('cs_name');
                       $dt['CS_Title']=$music['cs_name'];
                       $dt['CS_AddTime']=date('Y-m-d H:i:s');
                       $this->CsdjDB->get_insert('dt',$dt);
                       $this->session->unset_userdata('token');
                       $this->CsdjSkins->Msg_url('恭喜您，歌曲发表成功'.$title.'!',$url); 

                }else{
                       $this->CsdjSkins->Msg_url('抱歉，歌曲发表失败，请联系管理员!','javascript:history.back();'); 
                }
        }

	public function del()
	{
                $id=intval($this->input->get('id', TRUE));   //id
                $hid=intval($this->input->get('hid', TRUE));   //hid 等于1时为还原
                if($id==0) $this->CsdjSkins->Msg_url('抱歉，参数错误，ID为空!','javascript:history.back();'); 
 
                if($hid==1){
                    $this->db->query("update ".CS_SqlPrefix."dance set cs_hid=0 where cs_user='".$this->session->userdata('cs_name')."' and cs_id='".$id."'");
                    $this->CsdjSkins->Msg_url('恭喜您，还原成功!',@$_SERVER['HTTP_REFERER']);  
                }else{
                    $this->db->query("update ".CS_SqlPrefix."dance set cs_hid=1 where cs_user='".$this->session->userdata('cs_name')."' and cs_id='".$id."'");
                    $this->CsdjSkins->Msg_url('恭喜您，删除成功!',@$_SERVER['HTTP_REFERER']);  
                }
        }
}

