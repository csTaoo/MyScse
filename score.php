<?php
include("login.php");
if(isset($_GET['username'])&&isset($_GET['pass']))
{
	$username=$_GET['username'];
	$pass=$_GET['pass'];
	$student=new LoginHelper();
	$arr=$student->getscore($username,$pass);
}else
{
	$arr=array();
}
?>
<!DOCTYPEHTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
<title>成绩查询</title>

<script type="text/javascript">
function altRows(id){
	if(document.getElementsByTagName){  
		
		var table = document.getElementById(id);  
		var rows = table.getElementsByTagName("tr"); 
		 
		for(i = 0; i < rows.length; i++){          
			if(i % 2 == 0){
				rows[i].className = "evenrowcolor";
			}else{
				rows[i].className = "oddrowcolor";
			}      
		}
	}
}

window.onload=function(){
	altRows('alternatecolor');
}
</script>


<!-- CSS goes in the document HEAD or added to your external stylesheet -->
<style type="text/css">
table.altrowstable {
	width:100%;
	font-family: verdana,arial,sans-serif;
	font-size:11px;
	color:#333333;
	border-width: 1px;
	border-color: #a9c6c9;
	border-collapse: collapse;
}
table.altrowstable th {
	border-width: 1px;
	padding: 8px;
	border-style: solid;
	border-color: #a9c6c9;
}
table.altrowstable td {
	border-width: 1px;
	padding: 8px;
	border-style: solid;
	border-color: #a9c6c9;
}
.oddrowcolor{
	background-color:#d4e3e5;
}
.evenrowcolor{
	background-color:#c3dde0;
}
</style>
</head>

<!-- Table goes in the document BODY -->
<body>
	<h1>必修成绩</h1>
	<table class="altrowstable" id="alternatecolor">
	<tr>
		<th>课程代码</th><th>课程名称</th><th>成绩</th>
	</tr>
	<?php
		if(sizeof($arr)==0)
		{
			echo("<tr><td>暂时无数据~</td><td>暂时无数据~</td><td>暂时无数据~</tr>");
		}
		else{
			
			$a=0;$b=1;$c=2;
			
			for($i=0;$i<sizeof($arr['nec'])/3;$i++)
			{
				echo("<tr><td>".$arr['nec'][$a]."</td><td>".$arr['nec'][$b]."</td><td>".$arr['nec'][$c]."</tr>");
				$a+=3;
				$b+=3;
				$c+=3;
			}
		}
	?>
	</table>
	<br>
	<h1>选修成绩</h1>
	<table class="altrowstable" id="alternatecolor">
	<tr>
		<th>课程代码</th><th>课程名称</th><th>成绩</th>
	</tr>
	<?php
		if(sizeof($arr)==0)
		{
			echo("<tr><td>暂时无数据~</td><td>暂时无数据~</td><td>暂时无数据~</tr>");
		}
		else
		{
			$a=0;$b=1;$c=2;
			
			for($i=0;$i<sizeof($arr['opt'])/3;$i++)
			{
				echo("<tr><td>".$arr['opt'][$a]."</td><td>".$arr['opt'][$b]."</td><td>".$arr['opt'][$c]."</tr>");
				$a+=3;
				$b+=3;
				$c+=3;
			}
		}
	?>
	</table>
</body>
</html>

