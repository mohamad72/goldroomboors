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
                $query5 = "SELECT * FROM `user` where u_telegramid='".$chat_id."'";
                $rsPackages5 = mysqli_query($excal,$query5);
                $recordinfo5=mysqli_fetch_assoc($rsPackages5);
                if($emount<=$recordinfo5['u_limitation'])
                {
                    sendMessage($excal,"قیمت مورد نظر را به تومان وارد کنید",$chat_id,3);
                    updateUserState($excal,$chat_id,3);
                }else{
                    sendMessage($excal,"از محدوده مجاز شما بیشتر است",$chat_id,2);
                    updateUserState($excal,$chat_id,2);
                }
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
            
            $query = "select * from user";
            $rsPackages = mysqli_query($excal,$query);
            while ($reader=mysqli_fetch_assoc($rsPackages)){
                sendMessage($excal,$message,$reader['u_telegramid'],10,$recordinfo['s_id']);
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
}else if($arrayMessage['callback_query']['data']!='')
{
    $query = "SELECT * FROM user where u_telegramid='".$arrayMessage['callback_query']['from']['id']."'";
    $rsPackages = mysqli_query($excal,$query);
    $recordinfo=mysqli_fetch_assoc($rsPackages);
    if($recordinfo['u_isvalid']==0)
        sendAnswerCallbackQuery("شما مجاز به انجام این کار نیستید",$arrayMessage['callback_query']['id']);
    else{
        $query2 = "SELECT * FROM suggest where s_id='".$arrayMessage['callback_query']['data']."'";
        $rsPackages2 = mysqli_query($excal,$query2);
        $recordinfo2=mysqli_fetch_assoc($rsPackages2);
        if($recordinfo['u_telegramid']==$recordinfo2['s_telegramid'])
            sendAnswerCallbackQuery("این پیشنهاد خودتان است",$arrayMessage['callback_query']['id']);
        else
        {
            if($recordinfo2['s_accepter']!='')
                sendAnswerCallbackQuery("پیشنهاد به فروش رفته است",$arrayMessage['callback_query']['id']);
            else{
                if($recordinfo['u_limitation']<$recordinfo['s_emount'])
                    sendAnswerCallbackQuery("از محدوده مجاز شما بیشتر است",$arrayMessage['callback_query']['id']);
                else{
                    $query ="UPDATE `suggest` SET `s_accepter`=".$arrayMessage['callback_query']['from']['id']."  where s_id='".$arrayMessage['callback_query']['data']."'";
                    mysqli_query( $excal,$query);
            
                    sendMessage($excal,"تراکنش شما تایید شد",$arrayMessage['callback_query']['from']['id'],0);
                    updateUserState($excal,$arrayMessage['callback_query']['from']['id'],0);   
    
                    sendMessage($excal,"پیشنهاد شما قبول شد",$recordinfo2['s_telegramid']);
                    $query3 = "select * from user where `u_telegramid` ='".$arrayMessage['callback_query']['data']."'";
                    $rsPackages3 = mysqli_query($excal,$query3);
                    $recordinfo3=mysqli_fetch_assoc($rsPackages3);
                    $query14 = "select * from user where `u_telegramid` ='".$recordinfo2['s_telegramid']."'";
                    $rsPackages14 = mysqli_query($excal,$query14);
                    $recordinfo14=mysqli_fetch_assoc($rsPackages14);
                    $issell=$recordinfo2['s_iscell']==1?"فروشنده ".$recordinfo14['u_name'].urlencode("\n")."خریدار ".$recordinfo['u_name']:"خریدار ".$recordinfo14['u_name'].urlencode("\n")."فروشنده ".$recordinfo['u_name'];
                    $message=$issell.urlencode("\n");
                    $message=$message."مقدار"." ".$recordinfo2['s_emount']." "."کیلو".urlencode("\n");
                    $message=$message."قیمت"." ".$recordinfo2['s_prise']." "."تومان".urlencode("\n");
                    $query13 = "select * from user";
                    $rsPackages13 = mysqli_query($excal,$query13);
                    while ($reader=mysqli_fetch_assoc($rsPackages13)){
                        sendMessage($excal,$message,$reader['u_telegramid']);
                    }
                }
            }
        }
    }
    //TODO pishnahad batel ya monghazi shode
}


function sendMessage($excal,$text,$chat_id,$user_state,$from_chat_id)
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
            "callback_data" => $from_chat_id
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
function sendAnswerCallbackQuery($text,$query_id)
{
    $token= "868518351:AAES1eTZ-zdaRR2hc6sYjE4ft5TmAifjfu4";
    
    $url= "https://api.telegram.org/bot".$token."/answerCallbackQuery?callback_query_id=".$query_id."&text=".$text;
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
    
    $query ="INSERT INTO `suggest` (`s_id`, `s_telegramid`, `s_level`, `s_emount`, `s_iscell`, `s_prise`,`s_accepter`) VALUES (NULL, '".$chat_id."', 0, 0, 0, 0,'');";
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
