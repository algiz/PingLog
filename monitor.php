<?php 
	//include Mysql DB_config
	require_once("DB_config.php");
    require_once("DB_class.php");
	session_start();
	// seesion timer
	if (isset($_SESSION["refresh_timer"])==false){
		$_SESSION["refresh_timer"] = 0;
	}
	// Expires in the past
	header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
	// Always modified
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
	// HTTP/1.1
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	// HTTP/1.0
	header("Pragma: no-cache");
?>
<?php

	class website {
		public $url="";
		public $response_time;
		public $status_code="";
		public $info;
		private $ch;
		
		public function get(){
		
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_URL, $this->url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_USERAGENT, "Google Bot");
			// SET TIMEOUT VALUE
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 50);
			curl_setopt($ch, CURLOPT_TIMEOUT, 50);
			//
			if(!curl_errno($ch)){
				$output = curl_exec($ch);
				$this->info = curl_getinfo($ch);
				$this->response_time = $info['total_time'];
				$this->status_code = $info['http_code'];
				//echo " GET $this->url is $response_time <br /> \n";
				curl_close($ch);
			}
		}
	}
	$datetime = date ("Y-m-d H:i:s" , mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'))) ; 
	
	
	$sitelist=array("http://www.google.com/",
					"http://www.vghtc.gov.tw/home/index.html",
					"http://www.vghtc.gov.tw/GipOpenWeb/wSite/mp?mp=1",
					"http://register.vghtc.gov.tw/register/queryInternetPrompt.jsp?type=query",
					"http://register.vghtc.gov.tw/register/listSection.jsp");
					
		
?>
<html>
<head>
	<title>回應速度監控(30秒更新)</title>
	<meta http-equiv="Content-Language" content="zh-tw" />
	<meta http-equiv="Content-Type" content="text/html" charset="Big5" />
	<meta http-equiv="refresh" content="30" />
	<meta http-equiv="cache-control" content="no-cache">
	<meta http-equiv="pragma" content="no-cache"> 
	<meta http-equiv="expires" content="0"> 	
	<style type="text/css">
		a:link {text-decoration:none;color: blue}
		a:visited {text-decoration:none;color: blue}
		a:active {text-decoration:none;color: black}
		a:hover {text-decoration:none;color: green}
		body{text-decoration: none;margin: 0px;}
	</style>　　	
</head>

<body>
	<center>(<b>NowTime:<?php echo $datetime ;?>)</b></center>
	<h1>
	<table border="1">

		<thead></thead>
			<tr>
				<th>host</th>
				<th>STATUS</th>
				<th>Response Time</th>
			</tr>
		<tbody>
		
<?php		
	foreach ($sitelist as $site) {
		//echo "Value: $site <br />\n";
		$s = new website;
		$s->url = $site;
		$s->get();
		echo "<tr>";
		echo "<td> <a href='$s->url' target=_new >$s->url</a> </td>" ;
		echo "<td align='center'>" ;
			if($s->info['http_code'] == '200'){
				echo "<font color=green>";
				echo $s->info['http_code'];
				echo "</font>";
			}else{
				echo "<font color=red>";
				echo $s->info['http_code'];
				echo "</font>";
			}
		echo "</td>";		
		echo "<td align='center'>" ;
		echo $s->info['total_time'];
		echo "</td>";
		echo "</tr>";
		if ($_SESSION["refresh_timer"] >= 10)
		{
			$db = new DB();
    		if ($db->connect_db($_DB['host'], $_DB['username'], $_DB['password'], $_DB['dbname']))
    		{
    		$db->query("INSERT INTO LOG(URL,Status,Response) VALUES('" . $s->url . "'," . $s->info['http_code'] . "," . $s->info['total_time'] . ");");
    		}
		}
	}
		if ($_SESSION["refresh_timer"] >= 10)
		{
			$_SESSION["refresh_timer"] = 0;
		}
		else{
		echo $_SESSION["refresh_timer"];
		$_SESSION["refresh_timer"] = $_SESSION["refresh_timer"] + 1;
		}
		unset($s);
?>

<?php
		$db = new DB();
		if ($db->connect_db($_DB['host'], $_DB['username'], $_DB['password'], $_DB['dbname']))
		{
			$db->query("SELECT * FROM LOG ORDER BY ID DESC LIMIT 10;");
			while($result = $db->fetch_array()){
				echo "</ br><h5>";
				echo $result['ID'] ."," . $result['Time'] ."," . $result['URL'] ."," . $result['Status'] ."," . $result['Response'];
				echo "</h5>";
			}
		}
?>

		</tbody>
	</table>
</body>
</html>
