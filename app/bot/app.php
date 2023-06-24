<?php
    include "./config.php";
    #code for private
    if($text = "/start"){
        bot("sendMessage".[
            'chat_id'=>1561051170,
            'text'=>"Hello, this bot only for otify if there's changes!!!"
        ]);
    }

    if(isset($_GET['oldBalance'])){
        bot("sendMessage".[
            'chat_id'=>1561051170,
            'text'=>"IT WORKED MAN!"
        ]);
    }

?>
