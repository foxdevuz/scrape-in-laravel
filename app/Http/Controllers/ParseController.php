<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use KubAT\PhpSimple\HtmlDomParser;
use Illuminate\Http\Request;
use App\Exports\DataExport;
use App\Models\Parse;
use App\Models\TelegramBot;
use DOMDocument;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Cast;
use Telegram\Bot\Laravel\Facades\Telegram;

use function Pest\Laravel\json;

class ParseController extends Controller
{
    public function getCryptoToken($url) {
        $pattern = '/token\/(0x[0-9a-fA-F]+)/';
        preg_match($pattern, $url, $matches);

        if (isset($matches[1])) {
            return $token = $matches[1];
        } else {
            return $token = null;
        }
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

    public function getCryptoName($target = "https://etherscan.io/token/0x6982508145454ce325ddbe47a25d4ec3d2311933?a=0xcca1303632437310c8b7e237251dc203d94a89c5") {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        $dom = HtmlDomParser::str_get_html($response);
    
        // Find the span with class "fs-base fw-medium"
        $span = $dom->find('span.fs-base.fw-medium', 0);
    
        // Get the text content of the span
        $cryptoName = $span->plaintext;
    
        // Return the cryptocurrency name
        return $cryptoName;
    }

    public function getTokensNumber($target = "https://etherscan.io/tokens")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        $dom = HtmlDomParser::str_get_html($response);
        $count = $dom->find('strong', 0)->plaintext;
        $number = str_replace(",","",$count);

        return $number;
    }

    public function parseTokenPage($target = "https://etherscan.io/tokens") {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        $dom = HtmlDomParser::str_get_html($response);

        $tokens = $dom->find('a[class=d-flex align-items-center gap-1 link-dark]');

        return $tokens;
    }
    public function parseTokenPageName($target = "https://etherscan.io/tokens")  {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $target);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        $dom = HtmlDomParser::str_get_html($response);

        $tokens = $dom->find('a[class=d-flex align-items-center gap-1 link-dark]');

        $dataToExtract[] = $tokens;
        $extractedData = [];
        foreach ($dataToExtract[0] as $token) {
            $dom = new DOMDocument();
            $dom->loadHTML($token);
            $anchorTag = $dom->getElementsByTagName('a')->item(0);
            $divTag = $anchorTag->getElementsByTagName('div')->item(0);
            $text = $divTag->textContent;
            $extractedData[] = $text;
        }

        return $extractedData;
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
                'cryptoName'=>trim($this->getCryptoName($url))
            ]);
        } else {
            $check->update([
                'balance' => $balance,
                'dollar' => $valueInDollar,
            ]);
        }
    }

    public function index()
    {
        $url = "https://etherscan.io/token/0x6982508145454ce325ddbe47a25d4ec3d2311933?a=0xcca1303632437310c8b7e237251dc203d94a89c5";
        $data = $this->parse($url);
        $this->storeResultParse($url);
        return view('welcome');
    }

    public function showData()
    {
        if (Cache::has('token')) {
            $target = Cache::get('token');
            $get = Parse::where('userToken', $target)->first();
            $inf[] = $get;
        } else {
            return redirect()->back()->withErrors(['error' => 'Something went wrong.']);
        }
        $this->storeResultParse(Cache::get('url'));
        return view('profile.getData', [
            'getOldaData' => $inf,
            'getNewData' => $this->parse(Cache::get('url')),
        ]);
    }

    public function getData(Request $request)
    {
        $validator = $request->validate([
            'targetUrl' => ['required'],
        ]);
        if ($validator) {
            // extract crypto token from url
            $token = $this->getCryptoToken(request('targetUrl'));
            // ending extract token
            Cache::forever('token', $token);
            Cache::forever('url', request('targetUrl'));
            $get = Parse::where('userToken', $token)->first();
            $inf[] = $get;
        } else {
            return redirect()->back()->withErrors(['error' => 'Something went wrong.']);
        }

        $this->storeResultParse(request('targetUrl'));
        return view('profile.getData', [
            'getOldaData' => $inf,
            'getNewData' => $this->parse(request('targetUrl')),
        ]);
    }

    public function getTelegramBotdata(){
        $get = TelegramBot::get();
        if($get->isEmpty()){
            $get = null;
            return $get;
        } else {
            return $get;
        }    
    }

    public function dashboard(){
        //cache of number tokens
        $cacheDuration = 60 * 24 * 10;

        $number = $this->getTokensNumber();
        // $number = 200;
        $limit = intval($number / 50);

        $dataArrayName = [];
        $dataArrayList = [];
        
        if(Cache::has('tokenHref')){
            $dataArrayList = Cache::get('tokenHref');
        } else {
            for ($i=0; $i <= $limit + 1; $i++) { 
                $dataArrayList[] = $this->parseTokenPage('https://etherscan.io/tokens?p=' . $i);
            }
            Cache::put('tokenHref', $dataArrayList, $cacheDuration);
        }
        if(Cache::has('extracted')){
            $dataArrayName = Cache::get('extracted');
        } else {
            for ($i=0; $i <= $limit + 1; $i++) { 
                $dataArrayName[] = $this->parseTokenPageName('https://etherscan.io/tokens?p=' . $i);
            }
            Cache::put('extracted', $dataArrayName, $cacheDuration);
        }

        return view('dashboard',[
            'extractedData' => $dataArrayName,
            'tokenHref'=>$dataArrayList,
            'number'=>$limit,
            'telegramNotification'=>$this->getTelegramBotdata()
        ]);
    }
    public function dataForTelegramBot(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'usersToken' => ['required'],
            'tokenCrypto' => ['required'],
        ]);

        if (!$validation->fails()) {
            try {
                $tokenCrypto = request('tokenCrypto');
                $user_token = request('usersToken');
                $exp = explode(" ", $user_token);

                foreach ($exp as $value) {
                    $check = TelegramBot::where('user', $value)->where('tokens',$tokenCrypto)->first();
                    if ($check == null) {
                        $this->storeResultParse("https://etherscan.io" . $tokenCrypto . "?a=" . $value);
                        
                        TelegramBot::create([
                            'user' => $value,
                            'tokens' => $tokenCrypto,
                            'cryptoName'=>$this->getCryptoName("https://etherscan.io" . request('tokenCrypto') . "?a=" . $value)
                        ]);
                    }
                }

                return redirect()->back()->with('success', "All actions have been performed successfully");
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['error1' => "Something went wrong. Error message: " . $e->getMessage()]);
            }
        } else {
            return redirect()->back()->withErrors(['error' => "Required data is missing"]);
        }
    }

    public function destroyNotificationTelegramBot(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => ['required'],
            'user_id' => ['required'],
        ]);
    
        // return response()->json($request->all());
        // exit;
        if ($validation->fails()) {
            return redirect()->back()->withErrors('error1', 'Validation failed');
        }
    
        $check = DB::table('telegram_bots')
            ->where('tokens', $request->input('name'))
            ->where('user', $request->input('user_id'))
            ->first();
        
        if ($check) {
            DB::table('telegram_bots')
                ->where('tokens', $request->input('name'))
                ->where('user', $request->input('user_id'))
                ->delete();
            return redirect()->back()->with('success', 'Record deleted successfully.');
        } else {
            return redirect()->back()->withErrors('error1', 'Record not found.');
        }
    }
    

}
