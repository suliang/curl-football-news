<meta charset="utf-8"> 
<?php 
/**
 * 采集虎扑足球网站的当日新闻（标题和链接）
 * @param $url
 * @return array
 */
	function caiji($url = "http://voice.hupu.com/soccer/newslist")
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		$content = curl_exec($ch);
		curl_close($ch);
		$little = stripos($content,'<div class="news-list">');
		$content = substr ($content,$little);
		$content = substr ($content,0,30000);                 //10000字大约是6条新闻，你自己估摸着
		$content = str_replace("\n","",$content);
		$content = str_replace("\r","",$content);
		
		preg_match_all("/list-hd(.*?)<a href=\"(.*?)\"  target=\"_blank\">(.*?)<\/a>/",$content,$arr);
		$info = array();
		$i = 0;
		foreach($arr[2] as $key=>$value){
			$info[$i]['link'] = $value;
			$info[$i]['title'] = $arr[3][$key];
			$i++;
		}
		return $info;
	}

/**
 * 根据新闻链接，采集每一条新闻的详细内容并匹配
 * @param $url
 * @return array
 */
	function once($url){
		$ch = curl_init();
	
		curl_setopt($ch, CURLOPT_URL, $url);
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

		$content = curl_exec($ch);
		curl_close($ch);
		$little = stripos($content,'一般全文 start');
		$content = substr ($content,$little);
		$last = stripos($content,'一般全文 end');		
		$content = substr ($content,0,$last);
		
		$content = str_replace("\n","",$content);
		$content = str_replace("\r","",$content);
		
		preg_match("/<div class=\"artical-content-read\">(.*?)<span id=\"editor_baidu/",$content,$arr);
		return $arr[1];
	}









	$newsurl = "http://voice.hupu.com/soccer/newslist";
	$newsarr = caiji($newsurl);
	$newnewsarr = array();
	$i = 0;
	foreach($newsarr as $key=>$value){
		$content=str_replace("div","p",once($value['link']));	
		//虎扑的图片是禁止外站调用的，所以你要把图片下载到本地
		preg_match("/src=\"(.*?)((.jpg)|(.png)|(.jpeg))(.*?)\"/",$content,$img);
		$imgstr = file_get_contents($img[1].$img[2]);
		$dir = "./Yourdir/";
		if(!file_exists($dir))
			mkdir($dir);
		$tempdir = $dir.date("Ymdhis")."_".rand(10000, 99999).$img[2];
		//下载图片并保持到本地---END
		file_put_contents($tempdir,$imgstr);
		$tempdir = substr($tempdir,1);
		$newnewsarr[$i]['content'] = preg_replace("/src=\"(.*?)\"/","src='http://www.yoursite.com{$tempdir}'",$content);
		$newnewsarr[$i]['title'] = $value['title'];
		$i++;
	}

	echo '<pre>';
	print_r($newnewsarr);