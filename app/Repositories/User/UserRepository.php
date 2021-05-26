<?php
namespace App\Repositories\User;

// 使用者表 model
use App\Models\User\User;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 身份驗證套件
use Illuminate\Support\Facades\Auth;
// 時間轉換套件
use Carbon\Carbon;

class UserRepository
{

    // User Model 指定變數
    protected $user;
    // 導入共用方法 指定變數
    protected $funcHelper;

    /*
    *    將需要使用的Model通過建構函式例項化
    */
    public function __construct(User $user, FuncHelper $funcHelper)
    {
        $this->user = $user;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增使用者
     * @param object data 新增資料
     * @param object $userableOrm  多態關聯表資料
     */
    public function create($data, $userableOrmData)
    {
        $user = new $this->user;
        $user->userable()->associate($userableOrmData);
        $user->fill($data);
        $user->save();
        return $user->fresh();
    }
    /**
     * 更新使用者
     * @param object data 新增資料
     * @param userType { type String(字串) } 判斷此使用者為何種身份
     */
    public function update($data)
    {
        $user = $this->user->find($data["user_id"]);
        $user->userable()->associate($user->userable);
        $user->fill($data);
        $user->save();
        return $user->fresh();
    }

    /**
     * 登入
     * @param string $account 登入帳號
     * @param string $password 登入密碼
     */
    public function login($account, $password)
    {
        // 取得來源ip
        $ip = request()->ip();
        // 取得登入身份（ 判斷是 system(系統管理者) 還是 operator(經營者) )
        $guard = $this->funcHelper->getGuard();
        // 取得token
        $token = Auth::guard($guard)->attempt(["account" => $account, "password" => $password], true);
        // 判斷是否有取得 token
        if ($token === false) {
            return ["success" => false];
        }

        
        // 取得登入者資料
        $userOrm = $this->getData("account", $account);
        // 取得登入者語系 沒有設定的話 取的預設語系
        $lang = $userOrm->lang ?? config('app.lang');
        // token 有效時間 預設 60分鐘 因此此計算方式為 60*60 等於 1小時 再加上 目前 unixtime
        $tokenTime = Auth::guard($guard)->factory()->getTTL() * 60 + time();
        //進行更新作業
        $userOrm->token = $token;
        $userOrm->token_time = $tokenTime;
        $userOrm->login_time = time();
        $userOrm->lang = $lang;
        $userOrm->last_ip = $ip;
        $userOrm->save();
        $response = [
            'token' => $token,
            'tokenTime' => $tokenTime,
            'groupCode' => $userOrm->group->group_code,
            'lang' => $lang,
            "success" => true,
            'dateStr' => date('Y-m-d H:i:s', $tokenTime),
        ];
        return $response;
    }

    /**
    * 判斷指定欄位是否有重複資料
    * @param string $column 欄位名稱
    * @param string $data 欄位資料
    */
    public function checkDuplicate($column, $data)
    {
        // 判斷是否有重複值
        $user = $this->user->where($column, $data)->first();
        // 如果有資料代表
        if (isset($user)) {
            return true;
        }
        return false;
    }

    /**
     * 取得資料
     * @param string $column 資料表欄位
     * @param string $column 資料表欄位
     */
    public function getData($column, $data)
    {
        // 取得登入者資料
        $user = $this->user->where($column, $data)->first();
        // 取得登入者群組身份
        $userGroupCode = $user->group->group_code;
        // 判斷是否為系統身份
        if ($userGroupCode === "SYSTEM") {
            // 系統身份沒有與管理者關聯 因此不關聯管理者資料
            $user = $this->user->with(["userable", "group"])->where($column, $data)->first();
        } else {
            // 其他身份使用者 皆與管理者有關聯 因此帶入管理者資料
            $user = $this->user->with(["userable.userOperator.userSub", "group"])->where($column, $data)->first();
        }
        return $user;
    }

    /**
     * 重取token
     */
    public function refreshToken()
    {
        $guard = $this->funcHelper->getGuard();
        // 取得登入者資料
        $user = $this->user->find(auth()->user()->user_id);
        // 重新取得token
        $token = Auth::guard($guard)->refresh();
        // token 有效時間 預設 60分鐘 因此此計算方式為 60*60 等於 1小時 再加上 目前 unixtime
        $tokenTime = Auth::guard($guard)->factory()->getTTL() * 60 + time();
        // 更新users 表token
        $user->token = $token;
        // 更新users 表token時間
        $user->token_time = $tokenTime;
        $user->save();
        $response = [
            'token' => $token,
            'tokenTime' => $tokenTime,
            'groupCode' => $user->group->group_code,
            "success" => true,
            'dateStr' => date('Y-m-d H:i:s', $tokenTime),
        ];
        return $response;
    }
    /**
     * 取得伺服器時間
     */
    public function getServerTime()
    {
        $timeZone = date('Z')/3600;
        $timeZone = ($timeZone>=0?'+':'').$timeZone;
        $serverTime = [
            'dateTime' => date('Y-m-d H:i:s'),
            'unixTime' => time(),
            'timeZone' => $timeZone,
            'zone' => date('Z'),
        ];

        return $serverTime;
    }
}
