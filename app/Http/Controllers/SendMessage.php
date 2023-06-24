<?php

namespace App\Http\Controllers;
include "../../../bot/config.php";

use App\Models\Parse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use KubAT\PhpSimple\HtmlDomParser;

use function bot\bot;
use function Pest\Laravel\get;

class SendMessage extends Controller
{
    protected $dataArray;
    private function parse($target = "https://etherscan.io/token/0x6982508145454ce325ddbe47a25d4ec3d2311933?a=0xcca1303632437310c8b7e237251dc203d94a89c5")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        $dom = HtmlDomParser::str_get_html($response);

        // getting Token from url {a}
        $queryString = parse_url($target, PHP_URL_QUERY);
        parse_str($queryString, $params);
        $tokenCrypto = $params['a'];
        # get Value of crypto in $
        $cardBody = $dom->find('div.card-body', 3);
        if ($cardBody) {
            $valueInDollar = $cardBody->find('div[id=ContentPlaceHolder1_divFilteredHolderValue]');
            $pattern = '/\$([\d,\.]+)/';
            preg_match($pattern, $cardBody, $matches);

            $value = $matches[1];
            $value = str_replace(',', '', $value);
            $valInUSD = $value;
        }
        # get balance
        $balanceDiv = $dom->find('div[id=ContentPlaceHolder1_divFilteredHolderBalance]', 0);
        if ($balanceDiv) {
            $currentBalance = str_replace(',', '', $balanceDiv->plaintext);
            $realCurrentBalance = explode(" ", $currentBalance)[2];
        } else {
            $dataArray['error'] = "Balance element not found.";
        }
        // needed
        $dataArray['cryptoToken'] = $tokenCrypto;
        $dataArray['valueInDollar'] = $valInUSD;
        $dataArray['currentBalance'] = $realCurrentBalance;
        return $dataArray;
    }

    private function storeResultParse($url)
    {
        $data = $this->parse($url);
        // make variable from data
        $cryptoToken = $data['cryptoToken'];
        $balance = $data['currentBalance'];
        $valueInDollar = $data['valueInDollar'];

        $check = Parse::where('tokenCrypto', $cryptoToken)->first();

        if ($check == null) {
            Parse::create([
                'tokenCrypto' => $cryptoToken,
                'balance' => $balance,
                'value_dollar' => $valueInDollar,
            ]);
            $dataArray[] = $check;
        } else {
            $check->update([
                'balance' => $balance,
                'value_dollar' => $valueInDollar,
            ]);
        }
    }


    public function get()
    {
        $url = Cache::get('url');
        $item1 = $this->parse($url);
        $item2 = $this->storeResultParse($url);

        $jsonEncode = json_encode($item1); // Use $item1 or $item2 depending on what you want to send

        bot("sendMessage", [
            'chat_id' => 1561051170,
            'text' => $jsonEncode
        ]);
    }

    public function start()
    {
        while (true) {
            $this->get();
            sleep(5);
        }
    }
}
