<?php
/**
 * Created by PhpStorm.
 * Time: 13:28
 */
include("login.php");
define("TOKEN","gh_b695b54ef1bf");
$wechatObj = new wechatCallbackapiTest();
if (!isset($_GET['echostr'])) {
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}

class wechatCallbackapiTest{
    //  接入校验
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }
    }
    //签名校验实现
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    //具体业务实现
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);

            //用户发送的消息类型判断
            switch ($RX_TYPE)
            {
                case "text":    //文本消息
                    $result = $this->receiveText($postObj);
                    break;
                case "event":   //图片消息
                    $result = $this->receiveEvent($postObj);
                    break;
                default:
                    $result = "unknow msg type: ".$RX_TYPE;
                    break;
            }
            echo $result;
        }else {
            echo "";
            exit;
        }
    }
    /*
   * 接收文本消息
   */
    private function receiveText($object)
    {
		//trim 去掉两边的空格
		$keyword=trim($object->Content);
		//切割用户发过来的用户名，密码，和要查询的项
		if(strstr($keyword,"#"))
		{
			$userarr=explode("#",$keyword);
			$student=new LoginHelper();
			switch($userarr[2])
			{
				case "个人信息":
				$content=$student->getpersoninfo($userarr[0],$userarr[1]);
				break;

				case "成绩":
				$content='<a href="http://139.129.135.12/coursedesign/score.php?username='.$userarr[0].'&pass='.$userarr[1].'">点击查看</a>';
				break;

				case "课表":
				$coursearr=$student->getcourse($userarr[0],$userarr[1]);
				//获得当前是星期几
				$week=date("w");

				//返回当前时间的课表
				switch($week)
				{
					case 1:
					$content=$coursearr['mon'];
					break;

					case 2:
					$content=$coursearr['tues'];
					break;

					case 3:
					$content=$coursearr['wed'];
					break;

					case 4:
					$content=$coursearr['thur'];
					break;

					case 5:
					$content=$coursearr['fri'];
					break;

					case 6:
					$content=$coursearr['sat'];
					break;

					case 0:
					$content=$coursearr['sun'];
					break;
				}
				break;
				
				case "考勤":
				$content=$student->attandence($userarr[0],$userarr[1]);
				break;
				
				case "考试时间":
				$content=$student->examtime($userarr[0],$userarr[1]);
				break;
				
				case "违规记录":
				$content=$student->violators($userarr[0],$userarr[1]);
				break;
				
				case "开设课程":
				$content=$student->allcourse($userarr[0],$userarr[1]);
				break;
				
				default:
				$content="你要查询的项目有错误,请重新输入";
				break;

			}
		}
		else
		{
			$content="查询格式错误";
		}

        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收事件消息
     */
    private function receiveEvent($object)
    {
        $content ="";
		switch($object->Event)
		{
			case "subscribe":
			$content="这里是陈世滔的测试号";
			break;
			
			case "unsubscribe":
			$content="取消关注";
			break;
			
			case "CLICK":
				switch($object->EventKey)
				{
					case "search":
					$content="查询教务系统方式如下：学号#密码#查询项（个人信息，成绩，课表，违规记录，考勤，考试时间，开设课程）";
					break;
					
					default:
						$content="查询教务系统方式如下：学号#密码#查询项（个人信息，成绩，课表，违规记录，考勤，考试时间，开设课程）";
						break;
				}
				break;
		}
        $result = $this->transmitText($object, $content);
        return $result;
    }


    /*
     * 回复文本消息
     */
    private function transmitText($object, $content)
    {
		if(is_array($content))
		{
			$str="";
			foreach($content as $item)
			$str.=$item."\n";
		}
		else
		{
			$str=$content;
		}
        $textTpl = "<xml>
    <ToUserName><![CDATA[%s]]></ToUserName>
    <FromUserName><![CDATA[%s]]></FromUserName>
    <CreateTime>%s</CreateTime>
    <MsgType><![CDATA[text]]></MsgType>
    <Content><![CDATA[%s]]></Content>
    </xml>";
		
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $str);
		
        return $result;
    }
}
?>













