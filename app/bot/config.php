<?php
    namespace bot;
#default codes
date_default_timezone_set("Asia/Tashkent");
#bot
define("API_KEY", "5979913085:AAE1yOkRjUvFGCr-1cEeKsnFXKCYMSjVV0M");
function bot($method, $datas=[]){
    $url = "https://api.telegram.org/bot".API_KEY."/".$method;
    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
    $res = curl_exec($ch);
    if(curl_error($ch)){
        var_dump(curl_error($ch));
    }else{
        return json_decode($res);
    }
};
function html($text){
    return str_replace(["<",">"], ["$#60","$#62"], $text);
};
#variables
$upd = json_decode(file_get_contents("php://input"));
#variable of message
$message = $upd->message;
$channel_post = $upd->channel_post;
$text = html($message->text);
$chat_id = $message->chat->id;
$chat_type = $message->chat->type;
$from_id = $message->from->id;
$message_id = $message->message_id;
$first_name = $message->from->first_name;
$last_name = $message->from->last_name;
$full_name = html($first_name . " " . $last_name);
$caption = $message->caption;
$username = $message->chat->username;
#reply_to_message
$reply_message = $message->reply_to_message;
#files
$document = $message->document;
$photo = $message->photo;
$audio = $message->audio;
$video = $message->video;
$voice = $message->voice;
# files  id variable
$docId = $document->file_id;
$docName = $document->file_name;
$docSize = $document->file_size;
#callback
$call = $upd->callback_query;
$chat_idCB = $call->message->chat->id;
$chat_typeCB = $call->message->chat->type;
$message_idCB = $call->message->message_id;
$call_from_id = $call->from->id;
$call_id = $call->id;
$call_data = $call->data;
$call_message_id = $call->message->message_id;
$myChatMember = $upd->my_chat_member;
$chat_type = $message->chat->type;
$chat_title = $message->chat->title;
#time 
$time = $message->date;
$time_second = date('s', $time);
$newChatMember = $message->new_chat_member;
$newChatMemberId = $message->new_chat_member->id;
$newChatMemberName = $message->new_chat_member->name;
$newChatMembers = $message->new_chat_members; // array!
#my log
file_put_contents("log.json", file_get_contents('php://input'));

?>
