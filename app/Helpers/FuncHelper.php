<?php
namespace App\Helpers;

// 時間格式轉換套件
use Carbon\Carbon;

class FuncHelper
{
    /**
     * 依域名判斷目前登入身份為何者
     *
     * @return string
     */
    public static function getGuard()
    {
        $host = request()->server('HOST') ?? request()->server('HTTP_HOST');

        $domain = config('app.domain');
        $subhost = str_replace(array($domain, '.'), '', $host);

        switch ($subhost) {
            case config('app.subAdminApi'):
            case config('app.subAdmin'):
            case config('app.subSystem'):
                $auth = 'admin';

                break;
            case config('app.subClientApi'):
            case config('app.subHome'):
                $auth = 'member';
                break;
            case config('app.subApi'):
                $auth = 'api';
                //$auth = 'user';
                break;
            default:
                $auth = 'user';
        }

        return $auth;
    }
    
    /**
    * cors處理
    * @param $response
    *
    * @return mixed
    */
    public static function responseCors($response)
    {
        $url = request()->server('HTTP_REFERER');
        $setHost = "";
        if ($url) {
            $urlArr = parse_url($url);
            $httpType = $urlArr['scheme'] ?? 'error';
            $host = $urlArr['host'] ?? 'error';
            $port = $urlArr['port'] ?? '';

            $setHost = $httpType.'://'.$host.($port ? ':'.$port : '');

            /**
             * 暫時用不到 判斷前端 referer回傳 cors 防止被偷用api
             */
            // $frontEndReferer = ['http://localhost'];
            // $frontEndReferer[] = config('app.urlType').'://'.config('app.subAdmin').'.'.config('app.domain');
            // $frontEndReferer[] = config('app.urlType').'://'.config('app.subHome').'.'.config('app.domain');
            // if (in_array($httpType.'://'.$host, $frontEndReferer) !== false) {
            // } else {
            //     $setHost = false;
            // }
        }
        return $response->header('Access-Control-Allow-Origin', $setHost)
                ->header('Access-Control-Allow-Methods', '*')
                ->header('Access-Control-Allow-Headers', 'Origin, Methods, Content-Type, Authorization')
                ->header('Access-Control-Allow-Credentials', true);
    }

    /**
     * 回應資料
     *
     * @param  string  $data
     * @param  string  $msg
     * @param  int  $code
     *
     */
    public static function successBack($data = null, $code = 200, $msg = 'success')
    {
        $responseJson = ['httpCode' => $code, 'msg' => $msg, 'data' => $data];
        $response = response()->json($responseJson, $code)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        $response = FuncHelper::responseCors($response);
        return $response;
    }

    /**
     * 錯誤的回應處理
     * @param  string  $errorMsg  回應的訊息
     * @param  null  $errorCode  回應的錯誤代碼
     * @param string $msg 回應fail(失敗)
     *
     */
    public static function errorBack($errorMsg = null, $errorCode = 500, $msg = "fail")
    {
        $errorMsg = $errorMsg ?: '異常錯誤';
        $responseJson = ['httpCode' => $errorCode, "msg" => $msg, 'errorMsg' => config("app.debug") ? $errorMsg : "程式錯誤"];
        $response = response()->json($responseJson, $errorCode)->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        $response = FuncHelper::responseCors($response);
        return $response;
    }
    /**
     * 帶入條件式的處理
     *
     * @param $orm
     * @param  array  $wheres  {帶入的條件式}
     * @param  array  $sorts  {帶入的排序}
     *
     */

    public static function toWhereSort($orm, $wheres = [], $sorts = null)
    {
        if (!$sorts) {
            $sorts = $wheres['sort'] ?? [];
            unset($wheres['sort']);
        }

        if ($sorts) {
            if (is_array($sorts)) {
                foreach ($sorts as $value) {
                    $sortArr = explode('|', $value);
                    $keyName = $sortArr[0];
                    $sortType = $sortArr[1] ?? 'ASC';

                    //判斷是否為'ASC','DESC'，不是的話，統一化成ASC正序
                    if (!in_array($sortType, ['ASC', 'DESC'])) {
                        $sortType = 'ASC';
                    }
                    $orm->orderBy($keyName, $sortType);
                }
            } else {
                $sortArr = explode('|', $sorts);
                $keyName = $sortArr[0];
                $sortType = $sortArr[1] ?? 'ASC';
                //判斷是否為'ASC','DESC'，不是的話，統一化成ASC正序
                if (!in_array($sortType, ['ASC','DESC'])) {
                    $sortType='ASC';
                }
                $orm->orderBy($keyName, $sortType);
            }
        }
        if ($wheres) {
            //排除不相關的值
            unset($wheres['perPage']);
            unset($wheres['page']);
            unset($wheres['sort']);

            foreach ($wheres as $keyName => $data) {
                switch ($keyName) {
                    case 'startDate':
                        $orm->whereDate('created_at', '>=', $data);
                        break;
                    case 'endDate':
                        $orm->whereDate('created_at', '<=', $data);
                        break;
                    case 'startUpdateDate':
                        $orm->whereDate('updated_at', '>=', $data);
                        break;
                    case 'endUpdateDate':
                        $orm->whereDate('updated_at', '<=', $data);
                        break;
                    case 'startDateTime':
                        $orm->where('created_at', '>=', $data);
                        break;
                    case 'endDateTime':
                        $orm->where('created_at', '<=', $data);
                        break;
                    case 'startUpdateDateTime':
                        $orm->where('updated_at', '>=', $data);
                        break;
                    case 'endUpdateDateTime':
                        $orm->where('updated_at', '<=', $data);
                        break;
                    case 'status':
                        if (is_array($data)) {
                            $orm->whereIn('status', $data);
                        } else {
                            $orm->where('status', $data);
                        }
                        break;
                    default://其它值只做等於處理
                        if (is_array($data)) {
                            $orm->whereIn($keyName, $data);
                        } else {
                            $ch_key = explode('|', $keyName);
                            switch ($ch_key[0]) {
                                case 'like':
                                    $orm->where($ch_key[1], 'like', '%'.$data.'%');
                                    break;
                                case 'start':
                                    $orm->where($ch_key[1], '>=', $data);
                                    break;
                                case 'end':
                                    $orm->where($ch_key[1], '<=', $data);
                                    break;
                                    // 關聯表查詢方法
                                case 'whereHas':
                                    $orm->whereHas($ch_key[1], function ($query) use ($ch_key, $data) {
                                        $query->where($ch_key[2], $data);
                                    });
                                    break;
                                case 'whereHasLike':
                                    $orm->whereHas($ch_key[1], function ($query) use ($ch_key, $data) {
                                        $query->where($ch_key[2], "like", "%{$data}%");
                                    });
                                    break;
                                    // 搜尋關聯表開始日期指定欄位方法
                                case 'whereHasDateStart':
                                    $orm->whereHas($ch_key[1], function ($query) use ($ch_key, $data) {
                                        $query->where($ch_key[2], ">=", $data);
                                    });
                                    break;
                                    // 搜尋關聯表結束日期指定欄位方法
                                case 'whereHasDateEnd':
                                    $orm->whereHas($ch_key[1], function ($query) use ($ch_key, $data) {
                                        $query->where($ch_key[2], "<=", $data);
                                    });
                                    break;
                                    // 搜尋指定欄位日期 開始時間方法
                                case "customStartDate":
                                    $orm->where($ch_key[1], '>=', $data);
                                    break;
                                    // 搜尋指定欄位日期 結束時間方法
                                case "customEndDate":
                                    $orm->where($ch_key[1], '<=', $data);
                                    break;
                                    // 搜尋生日時 使用 like比對
                                case "getBirthdayDate":
                                    // 將生日得 月 與 日期 拆開成 array ["月","日"]
                                    $birthday = explode("-", $data);
                                    $orm->whereMonth($ch_key[1], $birthday[0])->whereDay($ch_key[1], $birthday[1]);
                                    break;
                                    // 搜尋指定欄位 多筆資料判斷方法
                                case "whereIn":
                                    $orm->whereIn($ch_key[1], $data);
                                    break;
                                default:
                                    $orm->where($keyName, $data);
                            }
                        }

                }
            }
        }
        return $orm;
    }
    /**
     * 新增標籤
     * @param object $ormData 關聯表資料
     * @param array $tagIds 關聯 tag id
     */
    public function addTag($ormData, $tagIds)
    {
        foreach ($tagIds as $value) {
            $ormData->morphToManyTag()->attach($value);
        }
    }

    /**
     * 取得對應日期時間
     */
    public function getTime()
    {
        // 時區
        $tz = "Asia/Taipei";
        $now = Carbon::now($tz);
        // 取得當前西元年份
        $year = $now->copy()->year;
        // 取得當前月份
        $month = $now->copy()->month;
        // 取得當前月份的下一個月份
        $nextMonth = intval(Carbon::now($tz)->addMonth(1)->format("m"));
        // 取得當前月份的上個月份
        $lastMonth = intval(Carbon::now($tz)->subMonth(1)->format("m"));
        // 取得昨日日期
        $yesterday = $now->copy()->yesterday($tz);
        // 取得今日日期
        $today = $now->copy()->today($tz);
        // 取得明日日期
        $tomorrow = $now->copy()->tomorrow($tz);
        // 取得本週開始時間
        $nowWeek = $now->copy()->startOfWeek()->format("Y-m-d H:i:s");
        // 取得本週開始時間
        $endWeek = $now->copy()->endOfWeek()->format("Y-m-d H:i:s");
        // 上週開始時間
        $nowLastWeek =  Carbon::now()->startOfWeek()->subDays(7)->format("Y-m-d H:i:s");
        // 上週結束時間
        $endLastWeek = Carbon::now()->startOfWeek()->subDays(1)->format("Y-m-d 23:59:59");
        // 本月(包含時間)
        $nowMonthTime = Carbon::create($year, $month, 1, 0, 0, $tz)->format("Y-m-d H:i:s");
        // 下個月(包含時間)
        $nextMonthTime = Carbon::create($year, $nextMonth, 1, 0, 0, $tz)->format("Y-m-d H:i:s");
        // 上個月(包含時間)
        $lastMonthTime = Carbon::create($year, $lastMonth, 1, 0, 0, $tz)->format("Y-m-d H:i:s");
        return [
            "now" => $now,
            "tz" => $tz,
            "year" => $year,
            "month" => $month,
            "nextMonth" => $nextMonth,
            "lastMonth" => $lastMonth,
            "yesterday" => $yesterday,
            "today" => $today,
            "tomorrow" => $tomorrow,
            "nowMonthTime" => $nowMonthTime,
            "nextMonthTime" => $nextMonthTime,
            "lastMonthTime" => $lastMonthTime,
            "nowWeek" => $nowWeek,
            "endWeek" => $endWeek,
            "nowLastWeek" => $nowLastWeek,
            "endLastWeek" => $endLastWeek,
        ];
    }
}
