<?php

$admins = array( "132895158");
$hostname_excal = "localhost";
$database_excal = "parsamag_robot";
$username_excal = "parsamag_mohamad";
$password_excal = "yie4lPHK=A";
$excal = mysqli_connect($hostname_excal, $username_excal, $password_excal,$database_excal);
$excal->set_charset("utf8");

header('Content-Type: text/html; charset=utf-8');
$message= file_get_contents("php://input");
$arrayMessage= json_decode($message, true);
$token= "868518351:AAES1eTZ-zdaRR2hc6sYjE4ft5TmAifjfu4";
$chat_id= $arrayMessage['message']['from']['id'];
$command= $arrayMessage['message']['text'];
    
$query = "SELECT u_state FROM user where u_telegramid='".$chat_id."'";
$rsPackages = mysqli_query($excal,$query);
$recordinfo=mysqli_fetch_assoc($rsPackages);
$user_state=$recordinfo['u_state'];


     sendMessage($excal,"شما هنوز اجازه این کار را ندادید",$chat_id,0);       


if($command == '/start'){
    
        insertUser($excal,$chat_id);
        updateUserState( $excal,$chat_id,0);
    
}
else if($command == '/trade'){
    $query ="DELETE FROM `suggest` where s_telegramid='".$chat_id."';";
    mysqli_query( $excal,$query);
    $query = "SELECT u_telegramid FROM user where u_telegramid='".$chat_id."' AND u_isvalid = 1";
    $rsPackages = mysqli_query($excal,$query);
    $recordinfo=mysqli_fetch_assoc($rsPackages);
    $check=$recordinfo['u_telegramid'];
    if($check!='')
    {
        insertSuggest($excal,$chat_id);
        updateUserState($excal,$chat_id,1);
    }
    else{
        sendMessage($excal,"شما هنوز اجازه این کار را ندادید",$chat_id,0);
    }
}
else if($user_state == 1){
    if(!($command == "فروش" || $command == "خرید"))
    {
        sendMessage($excal,"داده معتبر نیست",$chat_id,1);
    }
    else{
        if($command == "فروش")
        {
            $query ="UPDATE `suggest` SET `s_iscell`=1 ,`s_level`=1  where s_telegramid='".$chat_id."'";
            mysqli_query( $excal,$query);
        }
        else if($command == "خرید")
        {
            $query ="UPDATE `suggest` SET `s_iscell`=0 ,`s_level`=1  where s_telegramid='".$chat_id."'";
            mysqli_query( $excal,$query);
        }
        sendMessage($excal,"آیا میزان وارد شده درست است؟ (۰ کیلو )",$chat_id,2);
        updateUserState($excal,$chat_id,2);
    }
}
else if($user_state == 2){
    if(!($command == "۱ بیشتر" || $command == "۱ کمتر" || $command == "۰.۵ بیشتر" || $command == "۰.۵ کمتر" || $command == "بلی" ))
    {
        sendMessage($excal,"داده معتبر نیست",$chat_id,2);
    }
    else{
        $query = "SELECT s_emount FROM suggest where s_telegramid='".$chat_id."'";
        $rsPackages = mysqli_query($excal,$query);
        $recordinfo=mysqli_fetch_assoc($rsPackages);
        $emount=$recordinfo['s_emount'];
        if($command == "۱ بیشتر")
        {
            $query ="UPDATE `suggest` SET `s_emount`=".($emount+1)." ,`s_level`=2  where s_telegramid='".$chat_id."'";
            mysqli_query( $excal,$query);
            sendMessage($excal,"آیا میزان وارد شده درست است(".($emount+1)." کیلو )",$chat_id,2);
        }
        if($command == "۱ کمتر")
        {
            if($emount>=1)
            {
                $query ="UPDATE `suggest` SET `s_emount`=".($emount-1)." ,`s_level`=2  where s_telegramid='".$chat_id."'";
                mysqli_query( $excal,$query);
                sendMessage($excal,"آیا میزان وارد شده درست است(".($emount-1)." کیلو )",$chat_id,2);
            }else{
                sendMessage($excal,"این مقدار قابل قبول نیست",$chat_id,2);
            }
        }
        if($command == "۰.۵ بیشتر")
        {
            $query ="UPDATE `suggest` SET `s_emount`=".($emount+0.5)." ,`s_level`=2  where s_telegramid='".$chat_id."'";
            mysqli_query( $excal,$query);
            sendMessage($excal,"آیا میزان وارد شده درست است(".($emount+0.5)." کیلو )",$chat_id,2);
        }
        if($command == "۰.۵ کمتر")
        {
            if($emount>=0.5)
            {
                $query ="UPDATE `suggest` SET `s_emount`=".($emount-0.5)." ,`s_level`=2  where s_telegramid='".$chat_id."'";
                mysqli_query( $excal,$query);
                sendMessage($excal,"آیا میزان وارد شده درست است(".($emount-0.5)." کیلو )",$chat_id,2);
            }else{
                sendMessage($excal,"این مقدار قابل قبول نیست",$chat_id,2);
            }
        }
        else if($command == "بلی")
        {
            if($emount>0)
            {
                sendMessage($excal,"قیمت مورد نظر را به تومان وارد کنید",$chat_id,3);
                updateUserState($excal,$chat_id,3);
            }else{
                sendMessage($excal,"این مقدار قابل قبول نیست",$chat_id,2);
            }
        }
    }
}
else if($user_state == 3){
    if(!is_numeric($command))
    {
        sendMessage($excal,"داده معتبر نیست",$chat_id,3);
    }
    else{
        $query ="UPDATE `suggest` SET `s_prise`=".$command." ,`s_level`=3  where s_telegramid='".$chat_id."'";
        mysqli_query( $excal,$query);
            
            
        $query = "SELECT * FROM suggest where s_telegramid='".$chat_id."'";
        $rsPackages = mysqli_query($excal,$query);
        $recordinfo=mysqli_fetch_assoc($rsPackages);
        $issell=$recordinfo['s_iscell']==1?"فروش":"خرید";
        $message=$issell.urlencode("\n");
        $message=$message."مقدار"." ".$recordinfo['s_emount']." "."کیلو".urlencode("\n");
        $message=$message."قیمت"." ".$recordinfo['s_prise']." "."تومان".urlencode("\n");
        sendMessage($excal,$message,$chat_id,4);
        sendMessage($excal,"در صورت صحت اطلاعات زیر دکمه تایید را فشار دهید در غیر اینصورت انصراف را فشار داده و دوباره اطلاعات را وارد کنید",$chat_id,4);
        updateUserState($excal,$chat_id,4);
    }
}
else if($user_state == 4){
    if(!($command == "تایید" || $command == "انصراف"))
    {
        sendMessage($excal,"داده معتبر نیست",$chat_id,3);
    }
    else{
        if($command == "تایید")
        {
            sendMessage($excal,"هم اکنون آگهی شما منتشر شد",$chat_id,0);
            updateUserState($excal,$chat_id,0);
            
            $query = "SELECT * FROM suggest where s_telegramid='".$chat_id."'";
            $rsPackages = mysqli_query($excal,$query);
            $recordinfo=mysqli_fetch_assoc($rsPackages);
            $issell=$recordinfo['s_iscell']==1?"فروش":"خرید";
            $message=$issell.urlencode("\n");
            $message=$message."مقدار"." ".$recordinfo['s_emount']." "."کیلو".urlencode("\n");
            $message=$message."قیمت"." ".$recordinfo['s_prise']." "."تومان".urlencode("\n");
            
            $query = "select * from user where u_isvalid = 1";
            $rsPackages = mysqli_query($excal,$query);
            while ($reader=mysqli_fetch_assoc($rsPackages)){
                sendMessage($excal,$message,$reader['u_telegramid'],10);
            }
            
        }
        else if($command == "انصراف")
        {
            $query ="DELETE FROM `suggest` where s_telegramid='".$chat_id."';";
            mysqli_query( $excal,$query);
            sendMessage($excal,"آگهی شما پاک شد",$chat_id,0);
            updateUserState($excal,$chat_id,0);
        }
    }
}

function gregorian_to_jalali($gy,$gm,$gd,$mod=''){
 $g_d_m=array(0,31,59,90,120,151,181,212,243,273,304,334);
 if($gy>1600){
  $jy=979;
  $gy-=1600;
 }else{
  $jy=0;
  $gy-=621;
 }
 $gy2=($gm>2)?($gy+1):$gy;
 $days=(365*$gy) +((int)(($gy2+3)/4)) -((int)(($gy2+99)/100)) +((int)(($gy2+399)/400)) -80 +$gd +$g_d_m[$gm-1];
 $jy+=33*((int)($days/12053)); 
 $days%=12053;
 $jy+=4*((int)($days/1461));
 $days%=1461;
 if($days > 365){
  $jy+=(int)(($days-1)/365);
  $days=($days-1)%365;
 }
 $jm=($days < 186)?1+(int)($days/31):7+(int)(($days-186)/30);
 $jd=1+(($days < 186)?($days%31):(($days-186)%30));
 return($mod=='')?array($jy,$jm,$jd):$jy.$mod.$jm.$mod.$jd;
}
function jalali_to_gregorian($jy,$jm,$jd,$mod=''){
 if($jy>979){
  $gy=1600;
  $jy-=979;
 }else{
  $gy=621;
 }
 $days=(365*$jy) +(((int)($jy/33))*8) +((int)((($jy%33)+3)/4)) +78 +$jd +(($jm<7)?($jm-1)*31:(($jm-7)*30)+186);
 $gy+=400*((int)($days/146097));
 $days%=146097;
 if($days > 36524){
  $gy+=100*((int)(--$days/36524));
  $days%=36524;
  if($days >= 365)$days++;
 }
 $gy+=4*((int)($days/1461));
 $days%=1461;
 if($days > 365){
  $gy+=(int)(($days-1)/365);
  $days=($days-1)%365;
 }
 $gd=$days+1;
 foreach(array(0,31,(($gy%4==0 and $gy%100!=0) or ($gy%400==0))?29:28 ,31,30,31,30,31,31,30,31,30,31) as $gm=>$v){
  if($gd<=$v)break;
  $gd-=$v;
 }
 return($mod=='')?array($gy,$gm,$gd):$gy.$mod.$gm.$mod.$gd; 
} 
/*function makeRecoverySQL($table, $id)
{
    // get the record          
    $selectSQL = "SELECT * FROM `" . $table . "` WHERE `id` = " . $id . ';';

    $result = mysql_query($selectSQL, $YourDbHandle);
    $row = mysql_fetch_assoc($result); 

    $insertSQL = "INSERT INTO `" . $table . "` SET ";
    foreach ($row as $field => $value) {
        $insertSQL .= " `" . $field . "` = '" . $value . "', ";
    }
    $insertSQL = trim($insertSQL, ", ");

    return $insertSQL;
}*/
function sendMessage($excal,$text,$chat_id,$user_state)
{
    $repl;
    if($user_state==0)
    {
        $repl=json_encode(['remove_keyboard'=>True]);
    }else if($user_state==1)
    {
        $repl=json_encode([
          'keyboard'=>[
            [
                ['text'=>'خرید'],['text'=>'فروش']
            ],
          ]
        ]);
    }else if($user_state==2)
    {
        $repl=json_encode([
          'keyboard'=>[
            [
                ['text'=>'۱ بیشتر'],['text'=>'۱ کمتر']
            ],[
                ['text'=>'۰.۵ بیشتر'],['text'=>'۰.۵ کمتر']
            ],[
                ['text'=>'بلی']
            ],
          ]
        ]);
    }else if($user_state==3)
    {
        $repl=json_encode(['remove_keyboard'=>True]);
    }else if($user_state==4)
    {
        $repl=json_encode([
          'keyboard'=>[
            [
                ['text'=>'تایید']
            ],[
                ['text'=>'انصراف']
            ],
          ]
        ]);
    }
    else if($user_state==10)
    {
        $repl=json_encode(array("inline_keyboard" => array(array(array(
            "text" => "برکت",
            "callback_data" => "command=button_0"
            )))
        ));
    }
    $token= "868518351:AAES1eTZ-zdaRR2hc6sYjE4ft5TmAifjfu4";
    
    $url= "https://api.telegram.org/bot".$token."/sendMessage?chat_id=".$chat_id."&text=".$text."&reply_markup=".$repl;
    $ch = curl_init();

    // set URL and other appropriate options
    curl_setopt($curld, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($curld, CURLOPT_RETURNTRANSFER, true);

    // grab URL and pass it to the browser
    curl_exec($ch);

    // close cURL resource, and free up system resources
    curl_close($ch);
}
function insertUser($excal,$chat_id)
{
    $query = "SELECT u_telegramid FROM user where u_telegramid='".$chat_id."'";
    $rsPackages = mysqli_query($excal,$query);
    $recordinfo=mysqli_fetch_assoc($rsPackages);
    $check=$recordinfo['u_telegramid'];
    if($check=='')
    {
        $query ="INSERT INTO `user` (`u_id`, `u_telegramid`, `u_name`, `u_isvalid`, `u_limitation`, `u_state`) VALUES (NULL, '".$chat_id."', '', 0, 0, 0);";
    	mysqli_query( $excal,$query);
    	$text= "به ربات خوش آمدید لطفا در انتظار تایید باشید";
        sendMessage($excal,$text,$chat_id,0);
    }else
    {
        $text= "شما قبلا وارد شده اید";
        sendMessage($excal,$text,$chat_id,0);
    }
}
function insertSuggest($excal,$chat_id)
{
    $text="قصد خرید دارید یا فروش؟";
    sendMessage($excal,$text,$chat_id,1);
    
    $query ="INSERT INTO `suggest` (`s_id`, `s_telegramid`, `s_level`, `s_emount`, `s_iscell`, `s_prise`) VALUES (NULL, '".$chat_id."', 0, 0, 0, 0);";
    mysqli_query( $excal,$query);
}
function updateUserState($excal,$chat_id,$newValue)
{
    // 0 default mode
    // 1 is selecting want to buy or sell
    
    $query ="UPDATE `user` SET `u_state`='".$newValue."' where u_telegramid='".$chat_id."'";
    mysqli_query( $excal,$query);
}
?>
