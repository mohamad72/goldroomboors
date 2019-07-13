<?php
$admins = array( "132895158");
$hostname_excal = "localhost";
						$database_excal = "parsamag_robot";
						$username_excal = "parsamag_mohamad";
						$password_excal = "yie4lPHK=A";
						
header('Content-Type: text/html; charset=utf-8');
$message= file_get_contents("php://input");
$arrayMessage= json_decode($message, true);
$token= "868518351:AAES1eTZ-zdaRR2hc6sYjE4ft5TmAifjfu4";
$chat_id= $arrayMessage['message']['from']['id'];
$command= $arrayMessage['message']['text'];
     

if($command != '/start'){
    $excal = mysqli_connect($hostname_excal, $username_excal, $password_excal,$database_excal);
    $excal->set_charset("utf8");
    $query = "SELECT u_telegramid FROM user where u_telegramid='".$chat_id."' AND u_isvalid = 1";
    $rsPackages = mysqli_query($excal,$query);
    $recordinfo=mysqli_fetch_assoc($rsPackages);
    $check=$recordinfo['u_telegramid'];
    if($check!='')
    {
        $query = "select * from user where u_isvalid = 1";
        $rsPackages = mysqli_query( $excal,$query);
        while ($reader=mysqli_fetch_assoc($rsPackages))
        {
            sendMessage(urlencode($command),$reader['u_telegramid']);
        }
    }
}
if($command == '/start'){
    insertUser($chat_id);
}

function sendMessage($text,$chat_id)
{
    $token= "868518351:AAES1eTZ-zdaRR2hc6sYjE4ft5TmAifjfu4";
    $url= "https://api.telegram.org/bot".$token."/sendMessage?chat_id=".$chat_id."&text=".$text;
    $ch = curl_init();

// set URL and other appropriate options
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);

// grab URL and pass it to the browser
curl_exec($ch);

// close cURL resource, and free up system resources
curl_close($ch);
}
function insertUser($chat_id)
{
    $hostname_excal = "localhost";
	$database_excal = "parsamag_robot";
	$username_excal = "parsamag_mohamad";
    $password_excal = "yie4lPHK=A";
    
    $excal = mysqli_connect($hostname_excal, $username_excal, $password_excal,$database_excal);
    $excal->set_charset("utf8");
	
    $query = "SELECT u_telegramid FROM user where u_telegramid='".$chat_id."'";
    $rsPackages = mysqli_query($excal,$query);
    $recordinfo=mysqli_fetch_assoc($rsPackages);
    $check=$recordinfo['u_telegramid'];
    if($check=='')
    {
        $query ="INSERT INTO `user` (`u_id`, `u_telegramid`, `u_name`, `u_isvalid`, `u_limitation`) VALUES (NULL, '".$chat_id."', '', 0, 0);";
    	mysqli_query( $excal,$query);
    	$text= "به ربات خوش آمدید لطفا در انتظار تایید باشید";
        sendMessage($text,$chat_id);
    }else
    {
        $text= "شما قبلا وارد شده اید";
        sendMessage($text,$chat_id);
    }
}
?>
