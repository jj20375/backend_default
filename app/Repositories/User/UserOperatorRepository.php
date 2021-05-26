<?php
namespace App\Repositories\User;

// 經營者表(user_operators) model
use App\Models\User\UserOperator;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserOperatorRepository
{

    // UserOperator Model 指定變數
    protected $userOperator;
    // 導入共用方法 指定變數
    protected $funcHelper;

    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(UserOperator $userOperator, FuncHelper $funcHelper)
    {
        $this->userOperator = $userOperator;
        $this->funcHelper = $funcHelper;
    }
    
    /**
     * 新增經營者
     * @param object $data 新增經營者資料
     */
    public function create($data)
    {
        $userOperator = new $this->userOperator;
        $userOperator->fill($data);
        // 判斷是否為最上層經營者 因為儲存涵式不同 parent_id = 0 代表最上層
        if ($data['parent_id']==0) {
            $userOperator->saveAsRoot();
        } else {
            $userOperator->parent_id = $data['parent_id'];
        }
        $userOperator->save();
        return $userOperator->fresh();
    }
    /**
     * 更新經營者
     * @param object $data 更新經營者資料
     */
    public function update($data)
    {
        $userOperator = $this->userOperator->find($data["operator_id"]);
        $userOperator->fill($data);
        $userOperator->save();
        return $userOperator->fresh();
    }
    /**
     * 判斷指定欄位是否有重複資料
     * @param string $column 欄位名稱
     * @param string $data 欄位資料
     */
    public function checkDuplicate($column, $data)
    {
        // 判斷是否有職
        $userOperator = $this->userOperator->where($column, $data)->first();
        // 如果有資料代表
        if (isset($userOperator)) {
            return true;
        }
        return false;
    }

    /**
     * 取得資料
     * @param string $column 資料表欄位
     * @param string $data 欄位資料
     */
    public function getData($column, $data)
    {
        $userOperator = $this->userOperator->with(["user.group", "userOperatorInfo", "whiteLists"])->where($column, $data)->first();
        return $userOperator;
    }
    /**
     * 取得資料 並客製化後續條件專用
     * @param string $column 資料表欄位
     * @param string $data 欄位資料
     */
    public function getDataCustom($column, $data)
    {
        $userOperator = $this->userOperator->with(["user.group", "userOperatorInfo", "whiteLists", "userDesigner"])->where($column, $data);
        return $userOperator;
    }
    /**
     * 取得樹狀列表
     * @param string $operatorId 上層管理者 id
     */
    public function getTreeLists($operatorId)
    {
        // 取得登入者資料
        $user = auth()->user();
        // 判斷是否為系統使用者
        if ($user->group->group_code === "SYSTEM" && $operatorId == 0) {
            // 取得所有管理者列表
            $userOperatorLists = $this->userOperator->all()->toTree();
        } else {
            // 取出下層管理者id
            $userOperatorIds = $this->userOperator->with(["user.group"])->find($operatorId)->descendants()->pluck("operator_id");
            // 取出下層管理者
            $userOperatorLists = $this->userOperator->with(["user.group"])->whereIn("operator_id", $userOperatorIds)->get()->toTree();
        }
        return $userOperatorLists;
    }

    /**
     * 取得列表
     * @param string $operatorId 上層管理者 id
     */
    public function getLists($operatorId)
    {
        // 取得登入者資料
        $user = auth()->user();
        // 判斷是否為系統使用者
        if ($user->group->group_code === "SYSTEM" && $operatorId == 0) {
            // 取得所有管理者列表
            $userOperatorLists = $this->userOperator->with(["user.group"])->where("parent_id", null)->get();
        } else {
            // 取出下層管理者id
            $userOperatorIds = $this->userOperator->find($operatorId)->descendants()->pluck("operator_id");
            // 取出下層管理者
            $userOperatorLists = $this->userOperator->with(["user.group"])
             ->whereIn("operator_id", $userOperatorIds)->where("parent_id", $operatorId)->get();
        }
        return $userOperatorLists;
    }

    /**
     * 取得可選擇的列表
     */
    public function getSelectLists($operatorId)
    {
        // 取得登入者群組代碼
        $userGroupCode = auth()->user()->group->group_code;
        // 判斷是否為系統登入者
        if ($userGroupCode === "SYSTEM" && $operatorId === 0) {
            // 取出所有管理者
            $userOperatorIds = $this->userOperator->pluck("operator_id");
        } else {
            // 取出包含自身管理者以及下層管理者id
            $userOperatorIds = $this->userOperator->find($operatorId)->descendantsAndSelf($operatorId)->pluck("operator_id");
        }
        /**
         * 此判斷式主要用來取出
         * 1.屬於登入的管理者底下的管理者 如果是系統身份查詢的話 就是查詢
         */
        $userOperatorLists = $this->userOperator
        ->with(["user"])
        // 過濾管理者 id
        ->whereIn("operator_id", $userOperatorIds)
        ->get();
        return $userOperatorLists;
    }

    /**
     * 取得下層代理id
     * @param operatorId { type String or Number(字串或數字) } 管理者id
     */
    public function getDescendants($operatorId)
    {
        // 取出包含自身管理者以及下層管理者id
        $userOperatorIds = $this->userOperator->find($operatorId)->descendantsAndSelf($operatorId);
        return $userOperatorIds;
    }
    /**
     * 模糊比對搜尋使用
     * @param userName { type String(字串) }管理者名稱
     * @param account { type String(字串) }帳號
     */
    public function remoteLists($userName, $account) {
        if($userName !== null) {
            $lists = $this->userOperator->where("name", "like", "%".$userName."%")->take(10)->get();
        } else {
            $lists = $this->userOperator->where("account", "like", "%".$account."%")->take(10)->get();
        }
        return $lists;
    }
}
