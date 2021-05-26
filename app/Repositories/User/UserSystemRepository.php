<?php
namespace App\Repositories\User;

// 系統使用者表 model
use App\Models\User\UserSystem;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserSystemRepository
{

    // UserSystem Model 指定變數
    protected $userSystem;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(UserSystem $userSystem, FuncHelper $funcHelper)
    {
        $this->userSystem = $userSystem;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增系統使用者
     * @param object $data 新增系統使用者資料
     */
    public function create($data)
    {
        $userSystem = new $this->userSystem;
        $userSystem->fill($data);
        $userSystem->save();
        return $userSystem->fresh();
    }

    /**
     * 更新系統使用者
     * @param object $data 更新系統使用者資料
     */
    public function update($data)
    {
        $userSystem = $this->userSystem->find($data["system_id"]);
        $userSystem->fill($data);
        $userSystem->save();
        return $userSystem->fresh();
    }

    /**
    * 判斷指定欄位是否有重複資料
    * @param string $column 欄位名稱
    * @param string $data 欄位資料
    */
    public function checkDuplicate($column, $data)
    {
        // 判斷是否有重複值
        $userSystem = $this->userSystem->where($column, $data)->first();
        // 如果有資料代表
        if (isset($userSystem)) {
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
        $user = $this->userSystem->with(["user", "whiteLists"])->where($column, $data)->first();
        return $user;
    }

    /**
    * 取得列表
    * @param object $data 分頁傳送過濾資料
    */
    public function getLists($data = null)
    {
        // 預設如果沒有傳送一頁呈現幾筆資料的話 會出現15筆
        $perPage = $data["perPage"] ?? 15;
        // 取得所有系統使用者列表
        $userLists = $this->userSystem->with(["user.group"]);
        // 將系統使用者列表傳入過濾參數並啟用分頁
        $responseData = $this->funcHelper->toWhereSort($userLists, $data)->paginate($perPage);
        return $responseData;
    }
}
