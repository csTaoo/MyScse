<?php
/**
 * autor:陈世滔
 *date:2016-5-15
 *
 *
 */
class LoginHelper
{
     //模拟登陆
     function curl_request($url,$post='',$cookie='', $returnCookie=0){
        //初始化curl
         $curl = curl_init();
         curl_setopt($curl, CURLOPT_URL, $url);
         curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');

         //设置这个选项为一个非零值(象 “Location: “)的头，服务器会把它当做HTTP头的一部分发送(注意这是递归的，PHP将发送形如 “Location: “的头)
         curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
         curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
         //在HTTP请求中包含一个”referer”头的字符串
         curl_setopt($curl, CURLOPT_REFERER, "http://class.sise.com.cn:7001/sise/"); //填写教务系统url
         if($post) {

             //如果你想PHP去做一个正规的HTTP POST，设置这个选项为一个非零值
             curl_setopt($curl, CURLOPT_POST, 1);
            //传递一个作为HTTP “POST”操作的所有数据的字符串。http_build_query根据数组产生一个urlencode之后的请求字符串
             curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
         }
         if($cookie) {
             //传递一个包含HTTP cookie的头连接。
             curl_setopt($curl, CURLOPT_COOKIE, $cookie);
         }
         //如果你想把一个头包含在输出中，设置这个选项为一个非零值。
         curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
         curl_setopt($curl, CURLOPT_TIMEOUT, 20);

         //如果成功只将结果返回  不输出任何内容
         curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
         $data = curl_exec($curl);
         if (curl_errno($curl)) {
             return curl_error($curl);
         }
         curl_close($curl);
         if($returnCookie){
             list($header, $body) = explode("\r\n\r\n", $data, 2);
             preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
             $info['cookie'] = substr($matches[1][0], 1);
             $info['content'] = $body;
             return $info;
         }else{
             return $data;
         }
     }

    //获取隐藏值
    function getView()
    {
        $url = 'http://class.sise.com.cn:7001/sise/';
        $result = $this->curl_request($url);
        
        //将结果从GBK转为utf-8格式
        $encode = mb_detect_encoding($result, array("ASCII",'UTF-8','GB2312',"GBK",'BIG5'));
        $result = mb_convert_encoding($result, "UTF-8", $encode);
        //获取random隐藏域的值
        $pattern = '<input id="random"   type="hidden"  value="(.*?)"  name="random" />';
        //使用正则表达式匹配 成功返回1 不成功返回0 使用$matchs存储结果
        preg_match_all($pattern, $result, $matches);

        //获取第一个隐藏域的随机数和值， 貌似这个不用也可以登录 ,有兴趣的自行测试
        $pattern='<input type="hidden" name="(.*?)"  value="(.*?)">';
        preg_match_all($pattern, $result, $name);

        $res = array($matches[1][0],$name[1][0],$name[2][0]);
		
        return $res;
    }

    //使用学号姓名登录
    function login($xh, $pwd)
    {
        $postdata=$this->getView();

        //登录网址
        $url = 'http://class.sise.com.cn:7001/sise/login_check.jsp';

        //post的数据
        $post[$postdata[1]]=$postdata[2];
        $post['random'] = $postdata[0];
        $post['username'] = $xh; //填写学号
        $post['password'] = $pwd; //填写密码

        //请求网络
        $result =$this->curl_request($url, $post, '', 1);

        //获取cookie
        return $result['cookie'];
    }

     //得到导航页，所有的查询链接都在这里
    function getmainhtml($xh, $pwd,$cookie)
    {
        $cookie=$this->login($xh, $pwd);
        $url = 'http://class.sise.com.cn:7001/sise/module/student_states/student_select_class/main.jsp';
        $result = $this->curl_request($url,'', $cookie);
		
        //获取字符编码格式 将结果从GBK转为utf-8格式
        $encode = mb_detect_encoding($result, array("ASCII",'UTF-8','GB2312',"GBK",'BIG5'));
        $result = mb_convert_encoding($result, "UTF-8", $encode);

        //用正则表达式匹配，取得所有查询链接
        //存储链接数组$list
        $pattern='/onclick="(.*?)"/';
        preg_match_all($pattern, $result, $list);
		
        //对匹配到的每个数组做处理获得查询链接并重新放进数组
        for($i=0;$i<sizeof($list[0]);$i++)
        {
            $str = $list[0][$i];
            $str=$this->split($str);
            $list[0][$i]=$str;
        }
		
        return $list[0];
    }

     //split用于组装查询链接
     function split($str)
     {
         if(strstr($str,"SISEWeb"))
         {
             $arr=explode("SISEWeb",$str);
             $url=substr($arr[1],0,strlen($arr[1])-2);
             $url="http://class.sise.com.cn:7001/SISEWeb".$url;
         }
         else
         {
             $arr=explode("='",$str);
             $url=substr($arr[1],0,strlen($arr[1])-2);
             $url="http://class.sise.com.cn:7001".$url;
         }
         return $url;
     }

     //获得个人信息
     function getpersoninfo($xh, $pwd)
     {
         $cookie=$this->login($xh, $pwd);
         $url=$this->getmainhtml($xh, $pwd,$cookie);
         $result=$this->curl_request($url[0],'',$cookie);
         //获取字符编码格式 将结果从GBK转为utf-8格式
         $encode = mb_detect_encoding($result, array("ASCII",'UTF-8','GB2312',"GBK",'BIG5'));
         $result = mb_convert_encoding($result, "UTF-8", $encode);
         
         //正则表达式匹配个人信息
         $pattern='~<table width="90%" border="0" class="table1" cellspacing="1" align="center" cellpadding="0">([\w\W]*?)</table>~';
         preg_match_all($pattern, $result, $table);
		 
		 
		 $str=$table[1][0];
		 $pattern='~<div[^>]*>([^<]*)</div>~';
		 preg_match_all($pattern,$str,$info);
		 
		 for($i=0;$i<sizeof($info[1]);$i++)
		 {
			 $info[1][$i]=trim($info[1][$i]);
		 }
		 
		 $infoarr=array("学号:".$info[1][1],"名称:".$info[1][2],"年级:".$info[1][3],"专业:".$info[1][4],"身份证号:".$info[1][5],"邮箱:".$info[1][6],"学习导师:".$info[1][7],"辅导员:".$info[1][8]);

         return($infoarr);
     }

     //获取个人成绩
     function  getscore($xh, $pwd)
     {
         $cookie=$this->login($xh, $pwd);
         $url=$this->getmainhtml($xh, $pwd,$cookie);
         $result=$this->curl_request($url[0],'',$cookie);

         //获取字符编码格式 将结果从GBK转为utf-8格式
         $encode = mb_detect_encoding($result, array("ASCII",'UTF-8','GB2312',"GBK",'BIG5'));
         $result = mb_convert_encoding($result, "UTF-8", $encode);

         //正则表达式匹配个人成绩
         $pattern='~<tbody>([\w\W]*?)</tbody>~';
         preg_match_all($pattern,$result,$scoretable);

         //必修成绩选修成绩
         $score=$scoretable[1][0];

         $pattern='~<td.*?>(.*?)</td>~';
         preg_match_all($pattern,$score,$td);
		 
		 //将课程名称取出重新赋给数组
         for($i=0;$i<sizeof($td[1]);$i++)
         {
             if(strstr($td[1][$i],"<a"))
             {
                 $pattern='~<a.*?>(.*?)</a>~';
                 preg_match_all($pattern,$score,$matc);
             }
         }

         //print_r($td[1]);
         //提取必修成绩
         $j=1;$a=0;$m=8;
         $arr = array();
         while(true)
         {
             $arr[]=$td[1][$j];
             $arr[]=$matc[1][$a];
             $arr[]=$td[1][$m];
             $j+=10;
             $a+=1;
             $m+=10;
             if($m>sizeof($td[1]))
                 break;
         }
         //print_r($arr);

         //选修成绩
         $optionscore=$scoretable[1][1];
		 
		 

         //提取选修成绩
         $pattern='~<td.*?>(.*?)</td>~';
         preg_match_all($pattern,$optionscore,$optiontd);
		 
		 
		  for($i=0;$i<sizeof($optiontd[1]);$i++)
         {
             if(strstr($optiontd[1][$i],"<a"))
             {
                 $pattern='~<a.*?>(.*?)</a>~';
                 preg_match_all($pattern,$optionscore,$ma);
             }
         }
         //print_r($optiontd);
         //提取必修成绩
         $o=0;$p=0;$l=7;
         $optionarr = array();
         while(true)
         {
             $optionarr[]=$optiontd[1][$o];
             $optionarr[]=$ma[1][$p];
             $optionarr[]=$optiontd[1][$l];
             $o+=9;
             $p+=1;
             $l+=9;
             if($l>sizeof($optiontd[1]))
                 break;
         }
		//print_r($optionarr);

         //定义成绩存储数组 存储必须和选修成绩并返回此数组
         $gradearr=array('nec'=>$arr,'opt'=>$optionarr);


         return $gradearr;
     }

     //获取课表
     function getcourse($xh, $pwd)
     {
         $cookie=$this->login($xh, $pwd);
         $url=$this->getmainhtml($xh, $pwd,$cookie);
         $result=$this->curl_request($url[1],'',$cookie);

         //获取字符编码格式 将结果从GBK转为utf-8格式
         $encode = mb_detect_encoding($result, array("ASCII",'UTF-8','GB2312',"GBK",'BIG5'));
         $result = mb_convert_encoding($result, "UTF-8", $encode);

         //正则表达式匹配切取课表
         $pattern='~<table borderColor="#999999" cellSpacing="0" bordercolordark="#ffffff" cellPadding="0" width="95%" border="1" align="center">([\w\W]*?)</table>~';
         preg_match_all($pattern,$result,$coursetable);

         //正则表达式取出单个课程
         $pattern='~<td width=\'10%\' align=\'left\' valign=\'top\' class=\'font12\'>(.*?)</td>~';
         preg_match_all($pattern,$result,$one);

         $course=$one[0];
         for($i=0;$i<sizeof($course);$i++)
         {
             $arr = explode("'>", $course[$i]);
             $splitcourse = explode("<", $arr[1]);
             $course[$i]=$splitcourse[0];
         }

         //课程数组
         $list = array(
             'sun' => array(
                 '1,2' => '今天星期天',
                 '3,4' => '不用上课'
             ),
             'mon' => array(
                 '1,2' =>$course[0]."[9:00-10:20]",
                 '3,4' => $course[7]."[10:40-12:00]",
                 '5,6' => $course[14]."[12:30-13:50]",
                 '7,8' => $course[21]."[14:00-15:20]",
                 '9,10' =>$course[28]."[15:30-16:50]",
                 '11,12'=>$course[35]."[17:00-18:20]",
                 '13,14'=>$course[42]."[19:00-20:20]",
                 '15,16'=>$course[49]."[20:30-21:50]"
             ),
             'tues' => array(
                 '1,2' => $course[1]."[9:00-10:20]",
                 '3,4' => $course[8]."[10:40-12:00]",
                 '5,6' => $course[15]."[12:30-13:50]",
                 '7,8' => $course[22]."[14:00-15:20]",
                 '9,10' =>$course[29]."[15:30-16:50]",
                 '11,12'=>$course[36]."[17:00-18:20]",
                 '13,14'=>$course[43]."[19:00-20:20]",
                 '15,16'=>$course[50]."[20:30-21:50]"
             ),
             'wed' => array(
                 '1,2' => $course[2]."[9:00-10:20]",
                 '3,4' => $course[9]."[10:40-12:00]",
                 '5,6' => $course[16]."[12:30-13:50]",
                 '7,8' => $course[23]."[14:00-15:20]",
                 '9,10' => $course[30]."[15:30-16:50]",
                 '11,12'=>$course[37]."[17:00-18:20]",
                 '13,14'=>$course[44]."[19:00-20:20]",
                 '15,16'=>$course[51]."[20:30-21:50]"
             ),
             'thur' => array(
                 '1,2' => $course[3]."[9:00-10:20]",
                 '3,4' => $course[10]."[10:40-12:00]",
                 '5,6' => $course[17]."[12:30-13:50]",
                 '7,8' => $course[24]."[14:00-15:20]",
                 '9,10' => $course[31]."[15:30-16:50]",
                 '11,12'=>$course[38]."[15:30-16:50]",
                 '13,14'=>$course[45]."[19:00-20:20]",
                 '15,16'=>$course[52]."[20:30-21:50]"
             ),
             'fri' => array(
                 '1,2' =>$course[4]."[9:00-10:20]",
                 '3,4' =>$course[11]."[10:40-12:00]",
                 '5,6' =>$course[18]."[12:30-13:50]",
                 '7,8' =>$course[25]."[14:00-15:20]",
                 '9,10' =>$course[32]."[15:30-16:50]",
                 '11,12'=>$course[39]."[17:00-18:20]",
                 '13,14'=>$course[46]."[19:00-20:20]",
                 '15,16'=>$course[53]."[20:30-21:50]"
             ),
             'sat' => array(
                 '1,2' => '今天星期六',
                 '3,4' => '不用上课'
             )
         );
         
         return $list;
     }
	
	 function examtime($xh,$pwd)
	 {
		$cookie=$this->login($xh,$pwd);
        $url=$this->getmainhtml($xh, $pwd,$cookie);
        $result=$this->curl_request($url[2],'',$cookie);
		
		//获取字符编码格式 将结果从GBK转为utf-8格式
        $encode = mb_detect_encoding($result, array("ASCII",'UTF-8','GB2312',"GBK",'BIG5'));
        $result = mb_convert_encoding($result, "UTF-8", $encode);
		
		$pattern='~<table width="90%" class="table" cellspacing="1">([\w\W]*?)</table>~';
		preg_match_all($pattern,$result,$td);
		
		$pattern='~<td.*?>(.*?)</td>~';
		preg_match_all($pattern,$td[1][0],$time);
		
		
		return $time[1]; 
	 }
	 
	 
	 //考勤信息
	 function attandence($xh,$pwd)
	 {
		$cookie=$this->login($xh,$pwd);
        $url=$this->getmainhtml($xh, $pwd,$cookie);
        $result=$this->curl_request($url[3],'',$cookie);
		
		//获取字符编码格式 将结果从GBK转为utf-8格式
        $encode = mb_detect_encoding($result, array("ASCII",'UTF-8','GB2312',"GBK",'BIG5'));
        $result = mb_convert_encoding($result, "UTF-8", $encode);
		
		
		$pattern='~<table width="99%" class="table" cellspacing="0" id="table1">([\w\W]*?)</table>~';
		preg_match_all($pattern,$result,$td);
		
		$pattern='~<td.*?>(.*?)</td>~';
		preg_match_all($pattern,$td[1][0],$atten);
		
		print_r($atten[1]);
		
		return $atten[1];
	 }
	 
	 //违规信息 violators
	 function violators($xh,$pwd)
	 {
		$cookie=$this->login($xh,$pwd);
        $url=$this->getmainhtml($xh, $pwd,$cookie);	
        $result=$this->curl_request($url[22],'',$cookie);
		
		
		//获取字符编码格式 将结果从GBK转为utf-8格式
        $encode = mb_detect_encoding($result, array("ASCII",'UTF-8','GB2312',"GBK",'BIG5'));
        $result = mb_convert_encoding($result, "UTF-8", $encode);
		
		$pattern='~<table width="95%" border="1" cellspacing="0" cellpadding="0" align="center">([\w\W]*?)</table>~';
		preg_match_all($pattern,$result,$td);
		
		
		$pattern='~<td.*?>(.*?)</td>~';
		preg_match_all($pattern,$td[1][0],$times);
		
		$count=array(trim($times[1][0]),trim($times[1][2]));
		
		
		return $count;
	 }
	 
	 
	 //查看开设的必修课程
	 function allcourse($xh,$pwd)
	 {
		$cookie=$this->login($xh,$pwd);
        $url=$this->getmainhtml($xh, $pwd,$cookie);	
        $result=$this->curl_request($url[10],'',$cookie);
		
		
		//获取字符编码格式 将结果从GBK转为utf-8格式
        $encode = mb_detect_encoding($result, array("ASCII",'UTF-8','GB2312',"GBK",'BIG5'));
        $result = mb_convert_encoding($result, "UTF-8", $encode);
		
		$pattern='~<table width="100%" border="0" class="table1" cellspacing="1" align="center">([\w\W]*?)</table>~';
		preg_match_all($pattern,$result,$table);
		
		$pattern='~<span.*?>(.*?)</span>~';
		preg_match_all($pattern,$table[1][0],$course);
		
		//取得标签内的文字
		$arrname=array();
		$arrname[0]="开设的必修课程名称";
		for($i=0;$i<sizeof($course[1]);$i++)
		{
			if(strstr($course[1][$i],"<a"))
			{
				$pattern='~<a.*?>(.*?)</a>~';
				preg_match_all($pattern,$course[1][$i],$a);
				$arrname[]=$a[1][0];
			}
		}
		
		return $arrname;
	 }
}

?>