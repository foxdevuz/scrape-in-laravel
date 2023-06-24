<?php

namespace App\Http\Controllers;

use App\Models\Parse;
use App\Models\TelegramBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use KubAT\PhpSimple\HtmlDomParser;
use PDO;

class ApiController extends Controller
{
    public function getCryptoName($target = "https://etherscan.io/token/0x6982508145454ce325ddbe47a25d4ec3d2311933?a=0xcca1303632437310c8b7e237251dc203d94a89c5") {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
        $response = curl_exec($ch);
        curl_close($ch);

        $dom = HtmlDomParser::str_get_html($response);
        $span = $dom->find('span.fs-base.fw-medium', 0);
        $cryptoName = $span->plaintext;
        return trim($cryptoName);
    }
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

        // get crypto token
        $pattern = '/token\/(0x[0-9a-fA-F]+)/';
        preg_match($pattern, $target, $matches);

        if (isset($matches[1])) {
            $tokenAddress = $matches[1];
        }

        $parsedUrl = parse_url($target);
        $queryString = $parsedUrl['query'];
        parse_str($queryString, $queryParams);
        $aValue = $queryParams['a'];

        // needed
        $dataArray = [];
        $dataArray['cryptoToken'] = $tokenAddress;
        $dataArray['userToken'] = $aValue;
        $dataArray['valueInDollar'] = $valInUSD;
        $dataArray['currentBalance'] = $realCurrentBalance;
        $dataArray['cryptoName'] = $this->getCryptoName($target);
        return $dataArray;
    }

    private function storeResultParse($url)
    {
        $data = $this->parse($url);
        // make variable from data
        $userToken = $data['userToken'];
        $cryptoToken = $data['cryptoToken'];
        $balance = $data['currentBalance'];
        $valueInDollar = $data['valueInDollar'];

        $check = Parse::where('tokenCrypto', $cryptoToken)->first();
        // get token name from url
        $pattern = '/token\/(0x[0-9a-fA-F]+)/';
        preg_match($pattern, $url, $matches);

        if (isset($matches[1])) {
            $tokenAddress = $matches[1];
        } else {
            $tokenAddress = null;
        }
        // end
        if ($check == null) {
            Parse::create([
                'userToken'=>$userToken,
                'tokenCrypto' => $tokenAddress,
                'balance' => $balance,
                'dollar' => $valueInDollar,
            ]);
        } else {
            $check->update([
                'balance' => $balance,
                'dollar' => $valueInDollar,
            ]);
        }
    }

    public function getData(Request $request)
    {
        $validator = $request->validate([
            'usersToken' => ['required'],
            'tokenCrypto'=>['required']
        ]);

        return response()->json([
            'ok' => false,
            'code' => 403,
            'message' => 'url parameter not found',
            'result'=>$validator
        ]);

        if ($validator) {
            // check url if it's correct or no
            $exp = explode('/',$request->input('url'));
            $checkUrl = $exp[2];
            if($checkUrl == 'etherscan.io'){
                // Extract token from URL
                $target = $request->input('url');
                $queryString = parse_url($target, PHP_URL_QUERY);
                parse_str($queryString, $params);
                $token = $params['a'];

                $getNew = $this->parse($target);
                $get = Parse::where('tokenCrypto', $token)->first();
                $this->storeResultParse($target);
                $dataArray[] = $getNew;
                $dataArray[] = $get;
                if ($get) {
                    return response()->json([
                        'ok' => true,
                        'code' => 200,
                        'message' => 'Data found',
                        'result' => $dataArray
                    ]);
                } else {
                    return response()->json([
                        'ok' => false,
                        'code' => 404,
                        'message' => 'Data not found',
                    ]);
                }
            } else {
                return response()->json([
                    'ok' => false,
                    'code' => 403,
                    'message' => 'Url has given wrongly',
                ]);
            }
        } else {
            return response()->json([
                'ok' => false,
                'code' => 403,
                'message' => 'url parameter not found',
            ]);
        }
    }
    
    public function getTelegramBotdata()
    {
        $get = TelegramBot::get();

        return $get;
    }

    public function getDataForTgBot()
    {
        $getDataForTg_DB = $this->getTelegramBotdata();
    
        $data = [];
        foreach ($getDataForTg_DB as $item) {
            if (isset($item['tokens']) && isset($item['user'])) {
                $data['parsingresult'][] = $this->parse("https://etherscan.io" . $item['tokens'] . "?a=" . $item['user']);
                $dbResult = Parse::where('userToken', $item['user'])
                    ->where('tokenCrypto', str_replace("/token/", "", $item['tokens']))
                    ->first();
                if ($dbResult !== null) {
                    $data['searchFromDbResult'][] = $dbResult;
                }
            }
        }
    
        return response()->json([
            'success' => true,
            'code' => 200,
            'message' => "Data found",
            'data' => $data
        ]);
    }
    

}
