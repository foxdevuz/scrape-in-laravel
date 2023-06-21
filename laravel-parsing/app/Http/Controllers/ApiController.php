<?php

namespace App\Http\Controllers;

use App\Models\Parse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use KubAT\PhpSimple\HtmlDomParser;

class ApiController extends Controller
{
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
        $dataArray = [];
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

        if (!$check) {
            Parse::create([
                'tokenCrypto' => $cryptoToken,
                'balance' => $balance,
                'value_dollar' => $valueInDollar,
            ]);
        } else {
            $check->balance = $balance;
            $check->value_dollar = $valueInDollar;
            $check->save();
        }
    }

    public function getData(Request $request)
    {
        $validator = $request->validate([
            'url' => ['required'],
        ]);
    
        if ($validator) {
            // Extract token from URL
            $target = $request->input('url');
            $queryString = parse_url($target, PHP_URL_QUERY);
            parse_str($queryString, $params);
            $token = $params['a'];
    
            $get = Parse::where('tokenCrypto', $token)->first();
    
            if ($get) {
                return response()->json([
                    'ok' => true,
                    'code' => 200,
                    'message' => 'Data found',
                    'result' => $get,
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
                'message' => 'url parameter not found',
            ]);
        }
    }
    
}
