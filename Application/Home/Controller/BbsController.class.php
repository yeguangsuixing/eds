<?php
namespace Home\Controller;
use Think\Controller;
class BbsController extends EdsController {


    public function index($page=0){
    	$m = M('PostListView');
    	$list = $m->order('tstate desc')->select();
    	$this->post_list = $list;
        $this->display();
    }

    public function post_list($cid=-1){
    	$cid = I('cid');
    	if($cid == ''){
    		$cid = 1;
    	}
    	$m = M('Bbs');
    	$lim['ttopic'] = $cid;
    	$lim['tstate'] = array('in', '10,20');//10-发布,20-置顶
    	$this->post_list = $m->where($lim)->select();
    	//echo $m->_sql();return;
    	$this->cid = $cid;
    	$this->display();
    }

    public function post_dtl($tid=-1){
    	$tid = I('tid');
    	if($tid == ''){
    		$this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
            return;
    	}
    	$m = M('Bbs');
    	$lim['tid'] = $tid;
    	$record = $m->where($lim)->find();
    	if($record){
    		$this->tid = $tid;
    		$this->ttopic = $record['ttopic'];
    		$this->ttitle = $record['ttitle'];
    		$this->tsummary = $record['tsummary'];
    		$this->tcontent = htmlspecialchars_decode($record['tcontent'], ENT_QUOTES);
    		$this->tcreated_time = $record['tcreated_time'];
    		$this->tstate = $record['tstate'];
    		$this->tscan_count = $record['tscan_count'];
    		$this->treply_count = 17;//TODO
    		$this->cid = $record['ttopic'];

    		$replym = M('PostReplyView');
    		$lim2['treply_id'] = $tid;
    		$this->reply_list = $replym->where($lim2)->select();
    		$this->treply_count = count($this->reply_list);
    		session('rnickname', '系统管理员');//RM
    		session('rhead_photo', '/assets/image/headphoto/default.png');
    	} else {
    		$this->show('{"result":1,"msg":"unknown id."}', 'utf-8');
            return;
    	}
    	$this->display();
    }
    public function post_reply($tid=-1,$tsummary='',$tcontent=''){
    	$tid = I('tid');
    	$tsummary = I('tsummary');
    	$tcontent = I('tcontent');
    	if($tid == ''||$tsummary==''||$tcontent==''){
    		$this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
            return;
    	}
    	$m = M('Bbs');
		$data['treply_id'] = $tid;
    	$data['trid'] = 1;//TODO
		$data['tsummary'] = $tsummary;
		$data['tcontent'] = $tcontent;
    	$data['tstate'] = 10;//发布
    	$data['tcreated_time'] = date('Y-m-d H:i:s',time());
		if($m->add($data)){
			$this->show('{"result":0,"msg":"adding succeed."}', 'utf-8');
		}else{
			$this->show('{"result":1,"msg":"'.($m->getDbError()).'"}', 'utf-8');
		}
    }

    public function post($tid=-1){
    	$tid = I('tid');
    	if($tid == ''){
    		$tid = -1;
    	}
    	if($tid != -1){
    		$m = M('Bbs');
            $lim['tid'] = $tid;
            $record = $m->where($lim)->find();
            $this->tid = $tid;
            $this->ttitle = $record['ttitle'];
            $this->tsummary = $record['tsummary'];
            $this->tcontent = $record['tcontent'];
            $this->ttopic = $record['ttopic'];
            $this->cid = $record['ttopic'];
            $this->tstate = $record['tstate'];
            $this->tcreated_time = $record['tcreated_time'];
    	}
    	$this->display();
    }

    public function post_release($zid=-1,$zsub_type='',$ztitle='',$zsummary='',$zcontent=''){
        $zid = I('zid');
        $zsub_type = I('zsub_type');
        $ztitle = I('ztitle');
        $zsummary = I('zsummary');
        $zcontent = I('zcontent');
        if($zsub_type==''||$ztitle==''||$zsummary==''||$zcontent==''){
            $this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
            return;
        }
        $this->p_post_save($zid,$zsub_type,$ztitle,$zsummary,$zcontent,10);//10-发布
    }
    public function post_save($zid=-1,$zsub_type='',$ztitle='',$zsummary='',$zcontent=''){
        $zid = I('zid');
        $zsub_type = I('zsub_type');
        $ztitle = I('ztitle');
        $zsummary = I('zsummary');
        $zcontent = I('zcontent');
        if($zsub_type==''||$ztitle==''||$zsummary==''||$zcontent==''){
            $this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
            return;
        }
        $this->p_post_save($zid,$zsub_type,$ztitle,$zsummary,$zcontent,0);//0-编辑中
    }
    private function p_post_save($zid,$zsub_type,$ztitle,$zsummary,$zcontent,$tstate){
        if($zid == '' || $zid == -1){
            $m = M('Bbs');
            $data['ttopic'] = $zsub_type;
            $data['trid'] = session('rid');
            $data['ttitle'] = $ztitle;
            $data['tsummary'] = $zsummary;
            $data['tcontent'] = $zcontent;
            $data['tstate'] = $tstate;//编辑中
            $data['tcreated_time'] = date('Y-m-d H:i:s',time());
            $data['tlast_edited_time'] = $data['tcreated_time'];
            if($m->add($data)){
                $this->show('{"result":0,"msg":"adding succeed."}', 'utf-8');
                return;
            }else{
                $this->show('{"result":1,"msg":"'.($m->getDbError()).'"}', 'utf-8');
                return;
            }
        } else {
            $m = M('Bbs');
            $data['tid'] = $zid;
            $data['ttopic'] = $zsub_type;
            $data['ttitle'] = $ztitle;
            $data['tsummary'] = $zsummary;
            $data['tcontent'] = $zcontent;
            $data['tstate'] = $tstate;//编辑中
            $data['tlast_edited_time'] = date('Y-m-d H:i:s',time());
            if($m->save($data)){
                $this->show('{"result":0,"msg":"adding succeed."}', 'utf-8');
                return;
            }else{
                $this->show('{"result":1,"msg":"'.($m->getDbError()).'"}', 'utf-8');
                return;
            }
        }
    }

    public function post_top($tid=-1,$ttop=0){
        $tid = I('tid');
        $ttop = I('ttop');
        if($tid != ''){
            if($ttop == 0){//取消置顶
                $m = M('Bbs');
                $data['tid'] = $tid;
                $data['tstate'] = 10;
                $data['ttop_time'] = date('Y-m-d H:i:s',time());
                if($m->save($data)){
                    $this->show('{"result":0,"msg":"succeed."}', 'utf-8');
                    return;
                } else {
                    $this->show('{"result":1,"msg":"db error."}', 'utf-8');
                    return;
                }
            } else if($ttop == 1){//置顶
                $m = M('Bbs');
                $data['tid'] = $tid;
                $data['tstate'] = 20;
                $data['ttop_time'] = date('Y-m-d H:i:s',time());
                if($m->save($data)){
                    $this->show('{"result":0,"msg":"succeed."}', 'utf-8');
                    return;
                } else {
                    $this->show('{"result":1,"msg":"db error.'.$m->_sql().'"}', 'utf-8');
                    return;
                }

            }
        }
        $this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
    }
    public function post_delete($tid = -1){
        $tid = I('tid');
        if($tid == ''){
            $this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
        }
        $m = M('Bbs');
        $data['tid'] = $tid;
        $data['tstate'] = 30;//被原作者删除
        if($m->save($data)){
            $this->show('{"result":0,"msg":"succeed."}', 'utf-8');
        } else{
            $this->show('{"result":1,"msg":"db error."}', 'utf-8');
        }
    }

    public function my_post(){
        session('rid',1);
        $m = M('Bbs');
        $lim['tstate'] = array('in', '0,10,20');//0-编辑中,10-发布,20-置顶
        $lim['trid'] = session('rid');
        $lim['treply_id'] = array('exp', ' is null ');
        $this->post_list = $m->where($lim)->order('tstate')->select();
        //echo $m->_sql();return;
        //dump($this->post_list);
        $this->display();
    }


	public function acc(){
		$m = M('Register');
		$lim['rstate'] = array('in', '(0,10)');
		$lim['rrole'] = array('elt', 25);
		$this->acc_list = $m->where($lim)->field(true)->select();
		$this->display();
	}
    public function acc_save($rid = -1,$raccount='',$rpassword='',$rnickname='',
    	$rstate='',
		$mpcreate_teacher='',
		$mpcreate_news='',
		$mpcreate_act='',
		$mpcreate_plan_project='',
		$mpedit_league_college='',
		$mpcreate_notice='',
		$mpcheck_ex_note='',
		$mpcreate_ex_trend='',
		$mpcreate_ex_project='',
		$mpcheck_courseware='',
		$mpcreate_topic='',
		$mpcreate_post='',
		$mpcreate_reply='',
		$mpcreate_msg='',
		$mpmanager_user='',
		$mpmanager_bbs=''
    	){
    	$rid = I('rid');
    	$raccount = I('raccount');
    	$rpassword = I('rpassword');
    	$rnickname = I('rnickname');

    	$m = M('Register');
    	$lim['raccount'] = $raccount;
    	if($m->where($lim)->find()){
    		if($m->data()['rid'] != $rid){
    			$this->show('{"result":1,"msg":"account(\''.$raccount.'\') error."}', 'utf-8');return;
    		}
    	}
    	$m->create();
    	if($rid == -1 || $rid == ''){
    		$data['raccount'] = $raccount;
    		$data['rpassword'] = sha1(md5($rpassword));
    		$data['rnickname'] = $rnickname;
    		$data['rrole'] = 20;//普管
    		$data['rstate'] = $rstate=='true'?0:10;//0-正常
    		$data['rcreated_time'] = date('Y-m-d H:i:s',time());
    		$data['rlast_edited_time']  = $data['rcreated_time'];
    		$data['rhead_photo'] = '/assets/image/headphoto/default.png';
    		$rid = $m->add($data);
    		if($rid){
    			$data2['mrid'] = $rid;
				$data2['mpcreate_teacher']=$mpcreate_teacher=='true'?1:0;
				$data2['mpcreate_news']=$mpcreate_news=='true'?1:0;
				$data2['mpcreate_act']=$mpcreate_act=='true'?1:0;
				$data2['mpcreate_plan_project']=$mpcreate_plan_project=='true'?1:0;
				$data2['mpedit_league_college']=$mpedit_league_college=='true'?1:0;
				$data2['mpcreate_notice']=$mpcreate_notice=='true'?1:0;
				$data2['mpcheck_ex_note']=$mpcheck_ex_note=='true'?1:0;
				$data2['mpcreate_ex_trend']=$mpcreate_ex_trend=='true'?1:0;
				$data2['mpcreate_ex_project']=$mpcreate_ex_project=='true'?1:0;
				$data2['mpcheck_courseware']=$mpcheck_courseware=='true'?1:0;
				$data2['mpcreate_topic']=$mpcreate_topic=='true'?1:0;
				$data2['mpcreate_post']=$mpcreate_post=='true'?1:0;
				$data2['mpcreate_reply']=$mpcreate_reply=='true'?1:0;
				$data2['mpcreate_msg']=$mpcreate_msg=='true'?1:0;
				$data2['mpmanager_user']=$mpmanager_user=='true'?1:0;
				$data2['mpmanager_bbs']=$mpmanager_bbs=='true'?1:0;
    			
    			$m2 = M('Manager');
    			if($m2->add($data2)){
    				$this->show('{"result":0,"msg":"adding succeed."}', 'utf-8');
    			} else{
    				$this->show('{"result":1,"msg":"error1."}', 'utf-8');
    			}
    		}else{
    			$this->show('{"result":1,"msg":"'.htmlspecialchars($m->_sql(),ENT_QUOTES).'"}', 'utf-8');
    		}
    	} else {
    		$data['rid'] = $rid;
    		$data['raccount'] = $raccount;
    		//$data['rpassword'] = sha1(md5($rpassword));
    		$data['rnickname'] = $rnickname;
    		$data['rrole'] = 20;//普管
    		$data['rstate'] = $rstate=='true'?0:10;//0-正常
    		$data['rlast_edited_time']  = date('Y-m-d H:i:s',time());
    		if($m->save($data)){
				$data2['mpcreate_teacher']=$mpcreate_teacher=='true'?1:0;
				$data2['mpcreate_news']=$mpcreate_news=='true'?1:0;
				$data2['mpcreate_act']=$mpcreate_act=='true'?1:0;
				$data2['mpcreate_plan_project']=$mpcreate_plan_project=='true'?1:0;
				$data2['mpedit_league_college']=$mpedit_league_college=='true'?1:0;
				$data2['mpcreate_notice']=$mpcreate_notice=='true'?1:0;
				$data2['mpcheck_ex_note']=$mpcheck_ex_note=='true'?1:0;
				$data2['mpcreate_ex_trend']=$mpcreate_ex_trend=='true'?1:0;
				$data2['mpcreate_ex_project']=$mpcreate_ex_project=='true'?1:0;
				$data2['mpcheck_courseware']=$mpcheck_courseware=='true'?1:0;
				$data2['mpcreate_topic']=$mpcreate_topic=='true'?1:0;
				$data2['mpcreate_post']=$mpcreate_post=='true'?1:0;
				$data2['mpcreate_reply']=$mpcreate_reply=='true'?1:0;
				$data2['mpcreate_msg']=$mpcreate_msg=='true'?1:0;
				$data2['mpmanager_user']=$mpmanager_user=='true'?1:0;
				$data2['mpmanager_bbs']=$mpmanager_bbs=='true'?1:0;
				$m2 = M('Manager');
				if($m2->where(" `mrid`=$rid")->save($data2)){
					$this->show('{"result":0,"msg":"succeed."}', 'utf-8');
				} else{
					$this->show('{"result":1,"msg":"'.$m2->_sql().'."}', 'utf-8');
				}
    		}else{
    			$this->show('{"result":1,"msg":"error3."}', 'utf-8');
    		}
    	}
    }

    public function acc_dtl($rid=-1){
    	$m = M('ManagerView');
    	$lim['rid'] = I('rid');
    	$register = $m->where($lim)->find();
    	if(!$register){
    		$this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
            return;
    	}
    	$this->rid = $register['rid'];
    	$this->raccount = $register['raccount'];
    	$this->rnickname = $register['rnickname'];
    	$this->rcreated_time = $register['rcreated_time'];
    	$this->rlast_edited_time = $register['rlast_edited_time'];
    	$this->rstate = $register['rstate'];
    	$this->rstate_name = $register['rstate_name'];

		$this->mpcreate_teacher=$register['mpcreate_teacher'];
		$this->mpcreate_news=$register['mpcreate_news'];
		$this->mpcreate_act=$register['mpcreate_act'];
		$this->mpcreate_plan_project=$register['mpcreate_plan_project'];
		$this->mpedit_league_college=$register['mpedit_league_college'];
		$this->mpcreate_notice=$register['mpcreate_notice'];
		$this->mpcheck_ex_note=$register['mpcheck_ex_note'];
		$this->mpcreate_ex_trend=$register['mpcreate_ex_trend'];
		$this->mpcreate_ex_project=$register['mpcreate_ex_project'];
		$this->mpcheck_courseware=$register['mpcheck_courseware'];
		$this->mpcreate_topic=$register['mpcreate_topic'];
		$this->mpcreate_post=$register['mpcreate_post'];
		$this->mpcreate_reply=$register['mpcreate_reply'];
		$this->mpcreate_msg=$register['mpcreate_msg'];
		$this->mpmanager_user=$register['mpmanager_user'];
		$this->mpmanager_bbs=$register['mpmanager_bbs'];


    	$this->display();
    }
    public function acc_edit($rid=-1){
    	$m = M('ManagerView');
    	$lim['rid'] = I('rid');
    	$register = $m->where($lim)->find();
    	if(!$register){
    		$this->rid = -1;
    		$this->display();
            return;
    	}
    	$this->rid = $register['rid'];
    	$this->raccount = $register['raccount'];
    	$this->rnickname = $register['rnickname'];
    	$this->rcreated_time = $register['rcreated_time'];
    	$this->rlast_edited_time = $register['rlast_edited_time'];
    	$this->rstate = $register['rstate'];
    	$this->rstate_name = $register['rstate_name'];

		$this->mpcreate_teacher=$register['mpcreate_teacher'];
		$this->mpcreate_news=$register['mpcreate_news'];
		$this->mpcreate_act=$register['mpcreate_act'];
		$this->mpcreate_plan_project=$register['mpcreate_plan_project'];
		$this->mpedit_league_college=$register['mpedit_league_college'];
		$this->mpcreate_notice=$register['mpcreate_notice'];
		$this->mpcheck_ex_note=$register['mpcheck_ex_note'];
		$this->mpcreate_ex_trend=$register['mpcreate_ex_trend'];
		$this->mpcreate_ex_project=$register['mpcreate_ex_project'];
		$this->mpcheck_courseware=$register['mpcheck_courseware'];
		$this->mpcreate_topic=$register['mpcreate_topic'];
		$this->mpcreate_post=$register['mpcreate_post'];
		$this->mpcreate_reply=$register['mpcreate_reply'];
		$this->mpcreate_msg=$register['mpcreate_msg'];
		$this->mpmanager_user=$register['mpmanager_user'];
		$this->mpmanager_bbs=$register['mpmanager_bbs'];


    	$this->display();

    }
    public function acc_restrict($rid=-1,$state=0){
    	$rid = I('rid');
    	$state = I('state');
    	if($rid == -1 || $rid == ''){
    		$this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
    		return;
    	}
    	$m = M('Register');
    	$data['rid'] = $rid;
    	$data['rstate'] = $state == 1?0:10;
    	if($m->save($data)){
    		$this->show('{"result":0,"msg":"succeed."}', 'utf-8');
    	} else{
    		$this->show('{"result":1,"msg":"error."}', 'utf-8');
    	}
    }
    public function acc_reset_password($rid=-1){
    	if($rid == -1){
    		$this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
    		return;
    	}
    	$m = M('Register');
    	$data['rid'] = $rid;
    	$data['rpassword'] = sha1(md5('000000'));
        $data['rlast_edited_time'] = date('Y-m-d H:i:s', time());
    	if($m->save($data)){
			$this->show('{"result":0,"msg":"succeed."}', 'utf-8');
    	} else {
    		$this->show('{"result":1,"msg":"error."}', 'utf-8');
    	}
    }
    public function acc_delete($rid = -1){
    	if($rid == -1){
    		$this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
    		return;
    	}
    	//<!--在此插入权限检查-->
    	if($rid == 1){//系统管理员
    		$this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
    		return;
    	}

    	$m = M('Register');
    	$data['rid'] = $rid;
    	$data['rstate'] = 20;//删除
    	if($m->save($data)){
			$this->show('{"result":0,"msg":"succeed."}', 'utf-8');
    	} else {
    		$this->show('{"result":1,"msg":"error."}', 'utf-8');
    	}
    }


	public function tah(){
		$m = M('Register');
		$lim['rstate'] = array('in', '(0,10)');
		$lim['rrole'] = array('gt', 25);//使用25作为分界线
		$this->tah_list = $m->where($lim)->field(true)->select();
		//echo $m->_sql();
		$this->display();
	}

	public function tah_dtl($rid = ''){
		$rid = I('rid');
		if($rid == ''){
    		$this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
            return;
		}
    	$m = M('UserView');
    	$lim['rid'] = I('rid');
    	$register = $m->where($lim)->find();
    	if($register == false){
    		$this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
            return;
    	}
        $this->rid = $register['rid'];
        $this->raccount = $register['raccount'];
        $this->rnickname = $register['rnickname'];
        $this->rcreated_time = $register['rcreated_time'];
        $this->rlast_edited_time = $register['rlast_edited_time'];
        $this->rstate = $register['rstate'];
        $this->rstate_name = $register['rstate_name'];

        $this->gname = $register['gname'];
        $this->uname = $register['uname'];
        $this->uchar = $register['uchar'];
        $this->uchar_name = $register['uchar_name'];

        $this->rstate = $register['rstate'];
        $this->ucreate_post = $register['ucreate_post'];
        $this->ucreate_reply = $register['ucreate_reply'];
        $this->ucreate_msg = $register['ucreate_msg'];
        $this->ucreate_ex_note = $register['ucreate_ex_note'];
        $this->ucreate_ex_trend = $register['ucreate_ex_trend'];
        $this->ucreate_ex_project = $register['ucreate_ex_project'];
        $this->uupload_courseware = $register['uupload_courseware'];
        $this->udownload_courseware = $register['udownload_courseware'];
        $this->umanage_student = $register['umanage_student'];

        //$this->mpcreate_teacher=$register['mpcreate_teacher'];
        //dump($register);
        $this->display();
	}

	public function tah_edit($rid = -1){
		$cm = M('College');
		$this->college_list = $cm->select();
		$dicm = M('Dic');
		$limuchar['dic_type'] = 'uchar';
		$this->uchar_list = $dicm->where($limuchar)->order('dic_key desc')->select();
		$rid = I('rid');
		if($rid == '' || $rid == -1){
			$this->rid = -1;
			$this->display();
			return;
		}

        $m = M('UserView');
        $lim['rid'] = $rid;
        $register = $m->where($lim)->find();
        if($register == false){
            $this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
            return;
        }
        $this->rid = $register['rid'];
        $this->raccount = $register['raccount'];
        $this->rnickname = $register['rnickname'];
        $this->rcreated_time = $register['rcreated_time'];
        $this->rlast_edited_time = $register['rlast_edited_time'];
        $this->rstate = $register['rstate'];
        $this->rstate_name = $register['rstate_name'];

        $this->uchar = $register['uchar'];

        $this->rstate = $register['rstate'];
        $this->ucreate_post = $register['ucreate_post'];
        $this->ucreate_reply = $register['ucreate_reply'];
        $this->ucreate_msg = $register['ucreate_msg'];
        $this->ucreate_ex_note = $register['ucreate_ex_note'];
        $this->ucreate_ex_trend = $register['ucreate_ex_trend'];
        $this->ucreate_ex_project = $register['ucreate_ex_project'];
        $this->uupload_courseware = $register['uupload_courseware'];
        $this->udownload_courseware = $register['udownload_courseware'];
        $this->umanage_student = $register['umanage_student'];

        //dump($register);
        $this->display();
	}
    public function tah_save($rid = -1,$ugid=-1,$uchar=-1,$raccount='',$rpassword='',$rnickname='',
    	$rstate='',
		$ucreate_post='',
		$ucreate_reply='',
		$ucreate_msg='',
		$ucreate_ex_note='',
		$ucreate_ex_trend='',
		$ucreate_ex_project='',
		$uupload_courseware='',
		$udownload_courseware='',
		$umanage_student=''
    	){
		$rid = I('rid');
		$ugid = I('ugid');
    	$raccount = I('raccount');
    	$rpassword = I('rpassword');
    	$rnickname = I('rnickname');
    	if($ugid == '' || $raccount == ''){
    		$this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
    		return;
    	}

		$m = M('Register');
    	$lim['raccount'] = $ugid.$raccount;
    	if($m->where($lim)->find()){
    		if($m->data()['rid'] != $rid){
    			$this->show('{"result":1,"msg":"account(\''.$lim['raccount'].'\') error."}', 'utf-8');return;
    		}
    	}
    	if($rid == -1 || $rid == ''){
    		$data['raccount'] = $ugid.$raccount;
    		$data['rpassword'] = sha1(md5($rpassword));
    		$data['rnickname'] = $rnickname;
    		$data['rrole'] = 30;//教师级别
    		$data['rstate'] = $rstate=='true'?0:10;//0-正常
    		$data['rcreated_time'] = date('Y-m-d H:i:s',time());
    		$data['rlast_edited_time']  = $data['rcreated_time'];
    		$data['rhead_photo'] = '/assets/image/headphoto/default.png';
			$rid = $m->add($data);
    		if($rid){
    			$data2['urid'] = $rid;
    			$data2['uchar'] = $uchar;
    			$data2['uno'] = $raccount;
    			$data2['uname'] = $rnickname;
    			$data2['udisplay_name'] = $rnickname;
    			$data2['usender'] = 0;//0-未知
    			$data2['uuid'] = session('uid'); if(is_null($data2['uuid']))$data2['uuid'] = 0;
    			$data2['uhead_photo'] = '/assets/image/headphoto/default.png';
    			$data2['ucreate_post']=$ucreate_post=='true'?1:0;
				$data2['ucreate_reply']=$ucreate_reply=='true'?1:0;
				$data2['ucreate_msg']=$ucreate_msg=='true'?1:0;
				$data2['ucreate_ex_note']=$ucreate_ex_note=='true'?1:0;
				$data2['ucreate_ex_trend']=$ucreate_ex_trend=='true'?1:0;
				$data2['ucreate_ex_project']=$ucreate_ex_project=='true'?1:0;
				$data2['uupload_courseware']=$uupload_courseware=='true'?1:0;
				$data2['udownload_courseware']=$udownload_courseware=='true'?1:0;
				$data2['umanage_student']=$umanage_student=='true'?1:0;
                $data2['ulast_edited_time']=date('Y-m-d H:i:s',time());
    			$m2 = M('User');
    			if($m2->add($data2)){
    				$this->show('{"result":0,"msg":"adding succeed.","rid":"'.$rid.'"}', 'utf-8');
    			} else{
    				$this->show('{"result":1,"msg":"error1.'.$m2->getDbError().'"}', 'utf-8');
    			}
    		}else{
    			$this->show('{"result":1,"msg":"'.$m->getDbError().'"}', 'utf-8');
    		}
    	} else {
    		$data['rid'] = $rid;
			$data['raccount'] = $ugid.$raccount;
    		//$data['rpassword'] = sha1(md5($rpassword));
    		$data['rnickname'] = $rnickname;
    		//$data['rrole'] = 30;//教师级别
    		$data['rstate'] = $rstate=='true'?0:10;//0-正常
    		//$data['rcreated_time'] = date('Y-m-d H:i:s',time());
    		$data['rlast_edited_time']  = date('Y-m-d H:i:s',time());
    		$data['rhead_photo'] = '/assets/image/headphoto/default.png';
    		if($m->save($data)){
    			$data2['urid'] = $rid;
    			$data2['uchar'] = $uchar;
    			$data2['uno'] = $raccount;
    			$data2['uname'] = $rnickname;
    			$data2['udisplay_name'] = $rnickname;
    			//$data2['usender'] = 0;//0-未知
    			//$data2['uuid'] = session('uid');
    			$data2['uhead_photo'] = '/assets/image/headphoto/default.png';
    			$data2['ucreate_post']=$ucreate_post=='true'?1:0;
				$data2['ucreate_reply']=$ucreate_reply=='true'?1:0;
				$data2['ucreate_msg']=$ucreate_msg=='true'?1:0;
				$data2['ucreate_ex_note']=$ucreate_ex_note=='true'?1:0;
				$data2['ucreate_ex_trend']=$ucreate_ex_trend=='true'?1:0;
				$data2['ucreate_ex_project']=$ucreate_ex_project=='true'?1:0;
				$data2['uupload_courseware']=$uupload_courseware=='true'?1:0;
				$data2['udownload_courseware']=$udownload_courseware=='true'?1:0;
				$data2['umanage_student']=$umanage_student=='true'?1:0;
                $data2['ulast_edited_time']=date('Y-m-d H:i:s',time());
    			$m2 = M('User');
                $data2['uid'] = $m2->where('`urid`='.$rid)->find()['uid'];
                $result = $m2->save($data2);
                //echo $m2->_sql();
    			if($result){
    				$this->show('{"result":0,"msg":"saving succeed.","rid":"'.$rid.'"}', 'utf-8');
    			} else{
    				$this->show('{"result":1,"msg":"'.$m2->getDbError().'"}', 'utf-8');
    			}
    		}else{
    			$this->show('{"result":1,"msg":"'.htmlspecialchars($m->getDbError(),ENT_QUOTES).'"}', 'utf-8');
    		}
    	}
    }



    public function cw(){
        session('rid',1);
        $m = M('CourseView');
        $lim['fcreator'] = session('rid');
        $lim['fstate'] = array('in', '0,10,20');
        $this->course_list = $m->where($lim)->select();
        $this->display();
    }

    public function cw_dtl($fid = -1){
        $fid = I('fid');
        if($fid == '' || $fid == -1){
            $this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
            return;
        }
        $m = M('CourseView');
        $lim['fid'] = $fid;
        $course = $m->where($lim)->find();
        if($course == false){
            $this->show('{"result":1,"msg":"parameters error2."}', 'utf-8');
            return;
        }
        //dump($course);
        $this->fname = $course['fname'];
        $this->fgrade = $course['fgrade'];
        $this->faddr = $course['faddr'];
        $this->fteacher = $course['fteacher'];
        $this->fterm_name = $course['fterm_name'];
        $this->display();
    }

    public function cw_edit($fid=-1){
        $fid = I('fid');

        $mterm = M('Dic');
        $limterm['dic_type'] = 'fterm';
        $this->term_list = $mterm->where($limterm)->select();
        //dump($this->term_list);
        if($fid == '' || $fid == -1){
            $this->fid = -1;
            $this->display();
            return;
        }
        $m = M('CourseView');
        $lim['fid'] = $fid;
        $record = $m->where($lim)->find();
        if($record == false){
            $this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
            return;
        }
        $this->fid = $record['fid'];
        $this->fname = $record['fname'];
        $this->faddr = $record['faddr'];
        $this->fgrade = $record['fgrade'];
        $this->fterm = $record['fterm'];
        $this->fteacher = $record['fteacher'];
        $this->flast_edited_time = $record['flast_edited_time'];


        $this->display();
    }


    public function cw_save($fid=-1,$fname='',$fteacher='',$fgrade='',$faddr='',$fterm='',$release=0){
        $fid = I('fid');
        $fname = I('fname');
        $fteacher = I('fteacher');
        $fgrade = I('fgrade');
        $faddr = I('faddr');
        $fterm = I('fterm');
        $release = I('release');
        if($fname == '' || $fteacher == '' || $fgrade==''||$fterm==''){
            $this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
            return;
        }
        if($fid == -1 || $fid == ''){//add
            $m = M('Course');
            $data['fname'] = $fname;
            $data['fcreator'] = session('rid');
            $data['fteacher'] = $fteacher;
            $data['fgrade'] = $fgrade;
            $data['faddr'] = $faddr;
            $data['fterm'] = $fterm;
            $data['fstate'] = $release==1?10:0;
            $data['fcreated_time'] = date('Y-m-d H:i:s', time());
            $data['flast_edited_time'] = $data['fcreated_time'];
            if($m->add($data)){
                $this->show('{"result":0,"msg":"adding succeed.","fid":"'.$fid.'"}', 'utf-8');
            } else {
                $this->show('{"result":1,"msg":"error1.'.$m->getDbError().'"}', 'utf-8');
            }
        }  else {//modify
            $m = M('Course');
            $data['fid'] = $fid;
            $data['fname'] = $fname;
            $data['fteacher'] = $fteacher;
            $data['fgrade'] = $fgrade;
            $data['faddr'] = $faddr;
            $data['fterm'] = $fterm;
            $data['fstate'] = $release==1?10:0;
            $data['flast_edited_time'] = date('Y-m-d H:i:s', time());
            if($m->save($data)){
                $this->show('{"result":0,"msg":"saving succeed.","fid":"'.$fid.'"}', 'utf-8');
            } else {
                $this->show('{"result":1,"msg":"error2.'.$m->getDbError().'"}', 'utf-8');
            }
        }
    }
    public function cw_delete($fid=-1,$close=0){
        $fid = I('fid');
        $close = I('close');
        if($fid == '' || $close == ''){
            $this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
            return;
        }
        $m = M('Course');
        $data['fid'] = $fid;
        $data['fstate'] = $close==1?20:30;//20-关闭，30-被删除
        $data['flast_edited_time'] = date('Y-m-d H:i:s', time());
        if($m->save($data)){
            $this->show('{"result":0,"msg":"deleting succeed.","fid":"'.$fid.'"}', 'utf-8');
        } else {
            $this->show('{"result":1,"msg":"error.'.$m->getDbError().'"}', 'utf-8');
        }
    }
    public function cw_release($fid=-1){
        $fid = I('fid');
        if($fid == ''){
            $this->show('{"result":1,"msg":"parameters error."}', 'utf-8');
            return;
        }
        $m = M('Course');
        $data['fid'] = $fid;
        $data['fstate'] = 10;//10-已发布
        $data['flast_edited_time'] = date('Y-m-d H:i:s', time());
        if($m->save($data)){
            $this->show('{"result":0,"msg":"succeed.","fid":"'.$fid.'"}', 'utf-8');
        } else {
            $this->show('{"result":1,"msg":"error.'.$m->getDbError().'"}', 'utf-8');
        }
    }
}

?>