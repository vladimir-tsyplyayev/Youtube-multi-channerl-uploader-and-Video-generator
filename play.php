<?php

// http://127.0.0.1/youtube/play.php
set_time_limit(0);
ini_set("memory_limit","1256M");


header('Content-Type: text/html; charset=utf-8');

require_once '/Users/YOUR_USER_NAME/vendor/autoload.php';

require 'IvonaClient.php';
$ivona = new IvonaClient();


// /usr/bin/ffmpeg
$exec_path = 'PATH_TO/ffmpeg';
$project_path = 'PATH_TO_THIS_FILE/';

$signs = array('oven', 'telets', 'bliznetsi', 'rac', 'lev', 'deva', 'vesy', 'scorpion', 'strelets', 'kozerog', 'vodoley', 'riby');
$signs_names = array('Оовнов', 'Тельцов', 'Близнецов', 'Раков', 'Львов', 'Дев', 'Весов', 'Скорпионов', 'Стрельцов', 'Козерогов', 'Водолеев', 'Рыб');
$signs_tags  = array('Овен', 'Телец', 'Близнецы', 'Рак', 'Лев', 'Дева', 'Весы', 'Скорпион', 'Стрелец', 'Козерог', 'Водолей', 'Рыбы');
$month_names = array(1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля', 5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа', 9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря');
$prilag_ends = array('ый', 'ого', 'ому', 'ым', 'ом', 'ий', 'его', 'ему', 'им', 'ем', 'ая', 'ой', 'ую', 'яя', 'ей', 'юю', 'ое', 'ее', 'ые', 'ых', 'ыми', 'ие', 'их', 'ими');
$icons = array('♈', '♉', '♊', '♋', '♌', '♍', '♎', '♏', '♐', '♑', '♒', '♓');
$images_search = array('юмор', 'кадры из фильма', 'люди', 'отношения', 'любовь', 'дети', 'кулинария', 'наука', 'бизнес');

// Youtube API tokens
$refresh_tokens = array(
    '',
    '',
    ''
);

// Channels ID's
$channel_ids = array(
	  '',
    '',
    ''
);



	$datetime = new DateTime(date("y-n-j"));
	$datetime->modify('+1 day');
	$day = $datetime->format('j');
	$month = $datetime->format('n');
	$month1 = $datetime->format('m');
	$year = $datetime->format('y');
print($day.'_'.$month.'_'.$year.'<br>');

if(isset($_GET['i'])){
	$i = $_GET['i'];

	if($i==12){
		print('<script>
		//window.location.replace("play_copy.php?i=0");
		</script>');
		die;
	}


	$v = $signs[$i];

	$cr = curl_init('http://www.astrostar.ru/horoscopes/main/'.$v.'/tomorrow.html');
	//$cr = curl_init('http://www.astrostar.ru/horoscopes/main/'.$v.'/day.html');
    curl_setopt($cr, CURLOPT_COOKIEFILE, "cookie.txt");
    curl_setopt($cr, CURLOPT_COOKIEJAR, "cookie.txt");
	curl_setopt($cr, CURLOPT_HTTPHEADER, array("Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/x-icq, */*","Accept-Language: ru;en;q=0.5","US-CPU: x86","User-Agent: Opera/9.10 (Windows NT 5.1; U; ru)","Connection: Keep-Alive"));
    curl_setopt($cr, CURLOPT_RETURNTRANSFER, 1);
   	curl_setopt($cr, CURLOPT_FOLLOWLOCATION, 1);
   	curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($cr, CURLOPT_REFERER, 'http://www.astrostar.ru');
    $r = strtolower(curl_exec($cr));
    curl_close ($cr);

	preg_match_all('#</h3>(.*?)noindex#is', $r, $all_text);
	preg_match_all('#<p>(.*?)</p>#is', str_replace('овн', 'оовн', $all_text[1][0]), $texts);

	if(strlen(trim($texts[1][0]))>0){


	// Get audio voice mp3 

	$text = 'Гороскоп для '.$signs_names[$i].' на '.$day.'-е '.$month_names[$month].' 20'.$year.' года. '.trim($texts[1][0]).' . . . Внимание, теперь бизнес гороскоп. '.trim($texts[1][2]).' А теперь любовный гороскоп. '.trim($texts[1][1]).' Уважаемые друзья, подписывайтесь на канал гороскопов для '.$signs_names[$i].' и слушайте свой ежедневный гороскоп. Не забывайте ставить лайк, когда события в вашей жизни сбылись так, как предсказывалось гороскопом, оставляйте свои комментарии.';

	if(!file_exists('mp3/'.$day.'_'.$month.'_'.$year)){
		mkdir('mp3/'.$day.'_'.$month.'_'.$year, 0755);
		chmod('mp3/'.$day.'_'.$month.'_'.$year, 0755);
	}

	$audio_file_path = 'mp3/'.$day.'_'.$month.'_'.$year.'/'.$v.'.mp3';
	$file = fopen($audio_file_path,"w");
	fputs ($file, $ivona->get($text, array('Language' => 'ru-RU', 'VoiceRate' => 'default', 'VoiceName'=>'Maxim')));
	fclose($file);
	chmod($audio_file_path, 0755);

	// mp3 file duration in sec

	$result = shell_exec($exec_path." -i ".$audio_file_path.' 2>&1 | grep -o \'Duration: [0-9:.]*\'');
	$audio_duration = str_replace('Duration: ', '', $result);
	$audio_duration_tmp = explode('.', trim($audio_duration));
	$audio_duration_parts = explode(":", $audio_duration_tmp[0]);
	$track_seconds = $audio_duration_parts[1]*60+$audio_duration_parts[2];
	$slides_seconds = $track_seconds-21.7;






	// Get and crop images - slides

	$predl = explode('. ', trim($texts[1][0]).' '.trim($texts[1][1]).' '.trim($texts[1][2]));

	if(!file_exists('slides/'.$day.'_'.$month.'_'.$year)){
		mkdir('slides/'.$day.'_'.$month.'_'.$year, 0755);
		chmod('slides/'.$day.'_'.$month.'_'.$year, 0755);
	}

	$koef = 2.6*$slides_seconds/count($predl);
	$img_files = array();
	$predl_duration = array();
	$last_prilag = array();
	$last_prilag2 = array();
	$last_prilag3 = array();
	$last_prilag4 = array();
	$last_prilag5 = array();
	$slides_counter = 0;
	foreach ($predl as $pr => $vp) {
		$last_prilag[$pr] = '';
		$words = explode(' ', $vp);
		foreach ($words as $wi => $vw) {
			foreach ($prilag_ends as $pli => $vpl) {
				if(strlen($vw)>strlen($vpl)){
					if(strcmp(substr($vw, (-1*strlen($vpl))),$vpl)==0){
						$prt1 = ''; if(isset($words[($wi-2)]) && strlen($words[($wi-2)])>3) $prt1 = $words[($wi-2)].' ';
						$prt2 = ''; if(isset($words[($wi-1)]) && strlen($words[($wi-1)])>3) $prt2 = $words[($wi-1)].' ';
						$prt3 = ''; if(isset($words[($wi+1)]) && strlen($words[($wi+1)])>3) $prt3 = ' '.$words[($wi+1)];
						$prt5 = '';
						$prt4 = ''; if(isset($words[($wi+2)]) && strlen($words[($wi+2)])>3) {$prt4 = ' '.$words[($wi+2)];}else{ 
							if(isset($words[($wi-3)]) && strlen($words[($wi-3)])>3) $prt5 = $words[($wi-3)].' '; 
						}
						$last_prilag[$pr] = $prt5.$prt1.$prt2.$vw.$prt3.$prt4;
						$last_prilag2[$pr] = $prt1.$prt2.$vw.$prt3;
						$last_prilag3[$pr] = $prt2.$vw.$prt3;
						$last_prilag4[$pr] = $vw.$prt3;
						$last_prilag5[$pr] = $vw;
					}
				}
			}
		}

		$new_img_duration = (int)(strlen($vp)/$koef);
		$predl_duration[] = $new_img_duration;
	}

	while(!file_exists('tmp/output_'.$v.'_tmp.avi')){


	$found_img = array();
	foreach ($predl as $pr => $vp) {

		if(strlen($last_prilag[$pr])>0){

			$images_search_selected = $images_search[rand(0,(count($images_search)-1))].' ';

			$url="http://www.bing.com/images/search?&q=".urlencode(trim($images_search_selected.' '.strtolower($last_prilag[$pr])))."&qft=+filterui:photo-photo+filterui:aspect-wide+filterui:imagesize-large&FORM=R5IR5";
			$imgs=file_get_contents($url);
			preg_match_all('#class="thumb" target="_blank" href="(.*?)"#i', $imgs, $all_images);
			if(!isset($all_images[1][0]) || !url_exists($all_images[1][0])){
				$url="http://www.bing.com/images/search?&q=".urlencode(strtolower($last_prilag[$pr]))."&qft=+filterui:photo-photo+filterui:aspect-wide+filterui:imagesize-large&FORM=R5IR5";
				$imgs=file_get_contents($url);
				preg_match_all('#class="thumb" target="_blank" href="(.*?)"#i', $imgs, $all_images);
				if(!isset($all_images[1][0]) || !url_exists($all_images[1][0])){
					$url="http://www.bing.com/images/search?&q=".urlencode(strtolower($last_prilag2[$pr]))."&qft=+filterui:photo-photo+filterui:aspect-wide+filterui:imagesize-large&FORM=R5IR5";
					$imgs=file_get_contents($url);
					preg_match_all('#class="thumb" target="_blank" href="(.*?)"#i', $imgs, $all_images);
					if(!isset($all_images[1][0]) || !url_exists($all_images[1][0])){
						$url="http://www.bing.com/images/search?&q=".urlencode(strtolower($last_prilag3[$pr]))."&qft=+filterui:photo-photo+filterui:aspect-wide+filterui:imagesize-large&FORM=R5IR5";
						$imgs=file_get_contents($url);
						preg_match_all('#class="thumb" target="_blank" href="(.*?)"#i', $imgs, $all_images);
						if(!isset($all_images[1][0]) || !url_exists($all_images[1][0])){
							$url="http://www.bing.com/images/search?&q=".urlencode(strtolower($last_prilag4[$pr]))."&qft=+filterui:photo-photo+filterui:aspect-wide+filterui:imagesize-large&FORM=R5IR5";
							$imgs=file_get_contents($url);
							preg_match_all('#class="thumb" target="_blank" href="(.*?)"#i', $imgs, $all_images);
							if(!isset($all_images[1][0]) || !url_exists($all_images[1][0])){
								$url="http://www.bing.com/images/search?&q=".urlencode(strtolower($last_prilag5[$pr]))."&qft=+filterui:photo-photo+filterui:aspect-wide+filterui:imagesize-large&FORM=R5IR5";
								$imgs=file_get_contents($url);
								preg_match_all('#class="thumb" target="_blank" href="(.*?)"#i', $imgs, $all_images);
							}
						}
					}
				}
			}

			

			shuffle($all_images[1]);
			foreach ($all_images[1] as $ii => $iv) {
				$found_img[$pr.'_'.$ii] = $iv;
			}
		}
	}
	$unic_img = array_unique($found_img);



	foreach ($predl as $pr => $vp) {
		for($ii=0;$ii<10;$ii++){

			$unic_img_id = $pr.'_'.$ii;
			if(isset($unic_img[$unic_img_id])){
				if($img = file_get_contents($unic_img[$unic_img_id])){

				  if($im = imagecreatefromstring($img)){
					$w = imagesx($im);
					$h = imagesy($im);
					$width = 1920;
					$height = 1080;
					$hn = (int)($height*$w/$width);
					$dy = 0;
					if($hn<$h){
    					$dy = (int)(($h-$hn)/4);
    				}
    				if($hn>$h){
    					$hn = $h;
    					$dy = 0;
    				}

    				$img_file_name = 'slides/'.$day.'_'.$month.'_'.$year.'/'.$v.'_'.$slides_counter.'.jpg';
    				$slides_counter++;
    			
					$thumb = imagecreatetruecolor($width, $height);
					imagecopyresized($thumb, $im, 0, 0, 0, $dy, $width, $height, $w, $hn);
					imagejpeg($thumb, $img_file_name);
					imagedestroy($thumb); 
					imagedestroy($im);
					chmod($img_file_name, 0755);

					$img_files[] = $project_path.$img_file_name;

					break;
				  }
				}
			}
		}
	}


	// Slideshow generator

	$exec = $exec_path.' ';
	foreach ($img_files as $imf => $vimf) {
		$exec .= '-loop 1 -i '.$vimf.' ';
	}
	$exec .= '-filter_complex "[0:v]trim=duration='.$predl_duration[0].',fade=t=out:st='.($predl_duration[0]-0.5).':d=0.5[v0]; ';
	foreach ($img_files as $imf => $vimf) {
		if($imf>0){
			$exec .= '['.$imf.':v]trim=duration='.$predl_duration[$imf].',fade=t=in:st=0:d=0.5,fade=t=out:st='.($predl_duration[$imf]-0.5).':d=0.5[v'.$imf.']; ';
		}
	}
	foreach ($img_files as $imf => $vimf) {
		$exec .= '[v'.$imf.']';
	}
	$exec .= 'concat=n='.count($img_files).':v=1:a=0,format=yuv420p[v]" -map "[v]" '.$project_path.'tmp/output_'.$v.'_tmp.avi';

	echo shell_exec($exec);

	foreach ($img_files as $imf => $vimf) {
		unlink(str_replace($project_path, '', $vimf));
	}

	}

	// merge with intro video

	if(file_exists('tmp/output_'.$v.'_tmp.avi')){
		while(!file_exists('tmp/output_'.$v.'_tmp2.avi')){

			$exec = $exec_path.' -i "concat:'.$project_path.'intros/'.$v.'.avi|'.$project_path.'tmp/output_'.$v.'_tmp.avi|'.$project_path.'intros/'.$v.'.avi|'.$project_path.'intros/'.$v.'.avi|'.$project_path.'intros/'.$v.'.avi|'.$project_path.'intros/'.$v.'.avi|'.$project_path.'intros/'.$v.'.avi" -c copy '.$project_path.'tmp/output_'.$v.'_tmp2.avi';

			echo shell_exec($exec);
		}
	}


	// Add audio to final video

	if(!file_exists('videos/'.$day.'_'.$month.'_'.$year)){
		mkdir('videos/'.$day.'_'.$month.'_'.$year, 0755);
		chmod('videos/'.$day.'_'.$month.'_'.$year, 0755);
	}


	$exec = $exec_path." -i ".$project_path."tmp/output_".$v."_tmp2.avi -i ".$project_path.$audio_file_path." -codec copy ".$project_path."videos/".$day.'_'.$month.'_'.$year."/".$v.".mp4";

	echo shell_exec($exec);

	





	$final_file_name = "Гороскоп для ".str_replace('Оо', 'О', $signs_names[$i])." (".$day.'.'.$month1.'.'.$year.")";

	rename ("videos/".$day.'_'.$month.'_'.$year."/".$v.".mp4", "videos/".$day.'_'.$month.'_'.$year."/".$final_file_name.".mp4");

	unlink('tmp/output_'.$v.'_tmp.avi');
	unlink('tmp/output_'.$v.'_tmp2.avi');
	unlink($audio_file_path);



	// thumbnail for Youtube with date

	$thumb = imagecreatefromjpeg('thumb/'.$v.'.jpg');
  $font = 'thumb/Arial.ttf';
	$text_size = 80;
	$textcolor = imagecolorallocate($thumb, 255, 255, 255); 
  $img_file_name = 'thumb/tmp_thumb/'.$v.'.jpg';
	imagettftext($thumb, $text_size, 0, 250, 840, $textcolor, $font, $day.' '.ucfirst($month_names[$month]).' 20'.$year);
	imagejpeg($thumb, $img_file_name);
	imagedestroy($thumb);
	chmod($img_file_name, 0755);




	if(file_exists("videos/".$day.'_'.$month.'_'.$year."/".$final_file_name.".mp4")){



	// Upload video to Youtube

	session_start();


	$OAUTH2_CLIENT_ID = '176907913278-9420p9cebcq47tf8cdn7d11nem1ka8ga.apps.googleusercontent.com';
	$OAUTH2_CLIENT_SECRET = 'QjuToxmHdIiTiWE8GJiFXyr4';
	$client = new Google_Client();
	$client->setClientId($OAUTH2_CLIENT_ID);
	$client->setClientSecret($OAUTH2_CLIENT_SECRET);
	$client->setScopes('https://www.googleapis.com/auth/youtube');
	$redirect = filter_var('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'],
    FILTER_SANITIZE_URL);
	$client->setRedirectUri($redirect);
	$client->setAccessType("offline");
	$client->setApplicationName('Youtube Horoscope');
	$client->setDeveloperKey('AIzaSyCbUvSZttk4-6QWKgz0brYH9RVxu9qAl68');
    $client->refreshToken($refresh_tokens[$i]);
    $newtoken = $client->getAccessToken();
    $client->setAccessToken($newtoken);

	$youtube = new Google_Service_YouTube($client);


	if ($client->getAccessToken()) {
  		try {

    		$videoPath = "videos/".$day.'_'.$month.'_'.$year."/".$final_file_name.".mp4";

    		$snippet = new Google_Service_YouTube_VideoSnippet();
    		$snippet->setTitle($icons[$i].' '.$final_file_name);
 			$snippet->setDescription($final_file_name.".
Любовный гороскоп и бизнес гороскоп для ".str_replace('Оо', 'О', $signs_names[$i])." на сегодня ".$day.'.'.$month1.'.'.$year.".
Гороскоп как для женщин ".str_replace('Оо', 'О', $signs_names[$i]).", так и для мужчин ".str_replace('Оо', 'О', $signs_names[$i]).".
");
 			$snippet->setTags(array('Гороскоп', $signs_tags[$i] , $day.'.'.$month1.'.'.$year, $month_names[$i], "гороскоп ".$signs_tags[$i], "гороскоп на сегодня ".$signs_tags[$i], "гороскоп ".$signs_tags[$i]." женщина", "гороскоп на завтра ".$signs_tags[$i], "гороскоп ".$signs_tags[$i]." мужчина", "любовный гороскоп ".$signs_tags[$i], "гороскоп на сегодня женщина", "любовный гороскоп на сегодня", "любовный гороскоп", "бизнес гороскоп"));
 			$snippet->setCategoryId("22");

		    $status = new Google_Service_YouTube_VideoStatus();
		    $status->privacyStatus = "public";

 			$video = new Google_Service_YouTube_Video();
		    $video->setSnippet($snippet);
		    $video->setStatus($status);

    		$chunkSizeBytes = 1 * 1024 * 1024;

    		$client->setDefer(true);

    		$insertRequest = $youtube->videos->insert("status,snippet", $video);

    		$media = new Google_Http_MediaFileUpload(
        		$client,
        		$insertRequest,
        		'video/*',
        		null,
        		true,
        		$chunkSizeBytes
    		);
    		$media->setFileSize(filesize($videoPath));

    		$status = false;
    		$handle = fopen($videoPath, "rb");
    		while (!$status && !feof($handle)) {
      			$chunk = fread($handle, $chunkSizeBytes);
      			$status = $media->nextChunk($chunk);
    		}

    		fclose($handle);

    		$videoId = $status['id'];

    		$client->setDefer(false);




    		// Upload thumbnail to Youtube

		    $imagePath = 'thumb/tmp_thumb/'.$v.'.jpg';
			$chunkSizeBytes = 1 * 1024 * 1024;
			$client->setDefer(true);
			$setRequest = $youtube->thumbnails->set($videoId);
			$media = new Google_Http_MediaFileUpload(
        		$client,
        		$setRequest,
        		'image/png',
        		null,
        		true,
        		$chunkSizeBytes
    		);
    		$media->setFileSize(filesize($imagePath));
    		$status = false;
    		$handle = fopen($imagePath, "rb");
    		while (!$status && !feof($handle)) {
      			$chunk = fread($handle, $chunkSizeBytes);
      			$status = $media->nextChunk($chunk);
    		}
    		fclose($handle);
			$client->setDefer(false);




    	} catch (Exception $ex) {}
    }

    unlink($videoPath);

    session_destroy();

	}

	$i++;
	if($i<=count($signs)){
		print('<h1>Youtube upload</h1><br>Keyword: '.$v.'<br>Cycyle #'.$i.'<br>
		<script>
		window.location.replace("play.php?i='.$i.'");
		</script>');
	}
	if($i==12){
		print('DONE!<br><a href="play.php?i=0">start</a>');
	}

	}

}else{
	print('<a href="play.php?i=0">start</a>');
}


function url_exists($url) {
    if (!$fp = curl_init($url)) return false;
    return true;
}


?>
