<?php

use KubAT\PhpSimple\HtmlDomParser;

    function parse() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://etherscan.io/token/0x6982508145454ce325ddbe47a25d4ec3d2311933?a=0xcca1303632437310c8b7e237251dc203d94a89c5");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        $dom = HtmlDomParser::str_get_html($response);
    
        $a = $dom->find('a');

        foreach ($a as $value) {
            return $value->href . "<br/>";
        }
    }

?>
