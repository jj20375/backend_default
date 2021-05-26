<?php
namespace App\Repositories;

// white_lists表 model
use App\Models\WhiteList;
// users表 model
use App\Models\User\User;

class WhiteListRepository
{

    // WhiteList Model 指定變數
    protected $whiteList;
    // User Model 指定變數
    protected $user;

    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(WhiteList $whiteList, User $user)
    {
        $this->whiteList = $whiteList;
        $this->user = $user;
    }

    /**
     * 新增ip白名單
     * @param object $data ip白名單資料
     * @param string $userId 使用者id
     */
    public function create($data, $userId)
    {
        // 關聯對象
        $ableOrm = $this->user->find($userId);
        $whiteList = new $this->whiteList;
        // 將子帳號關聯 model 與 id 存入資料庫
        $whiteList->subable()->associate($ableOrm->userable);
        $whiteList->fill($data);
        $whiteList->save();
        return $whiteList->fresh();
    }

    /**
     * 更新ip白名單
     * @param object $data 更新ip白名單資料
     * @param string $userId 使用者id
     */
    public function update($data, $userId)
    {
        // 如果是系統使用者 可以更新
        if (auth()->user()->group->group_code === "SYSTEM") {
            // 取得對應的使用者 ip白名單 ip白名單資料
            $whiteList = $this->whiteList->find($data["ip_id"]);
            // 如果沒有找到 代表此使用者 沒有ip白名單
            if ($whiteList === null) {
                // 關聯對象
                $ableOrm = $this->user->find($userId);
                // 沒有找到ip白名單資料時改為新增 ip 白名單方法
                $whiteList = new $this->whiteList;
                // 白名單關聯使用者
                $whiteList->whitelist_able()->associate($ableOrm->userable);
            }
            $whiteList->fill($data);
            $whiteList->save();
            return $whiteList->fresh();
        } else {
            return false;
        }
    }
}
