<?php
namespace App\Repositories\Order;

// point_orders表 model
use App\Models\Order\PointOrder;
// UserOperator表 model
use App\Models\User\UserOperator;
// 導入 共用方法
use App\Helpers\FuncHelper;

class PointOrderRepository
{

    // PointOrder Model 指定變數
    protected $pointOrder;
    // UserOperator Model 指定變數
    protected $userOperator;
    // 導入共用方法 指定變數
    protected $funcHelper;

    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(PointOrder $pointOrder, UserOperator $userOperator, FuncHelper $funcHelper)
    {
        $this->pointOrder = $pointOrder;
        $this->userOperator = $userOperator;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增點數訂單
     * @param object $data 新增點數訂單資料
     */
    public function create($data)
    {
        // 訂單前綴 西元日期年月日
        $prefix = date('Ymd');
        // 隨機亂數3位數
        $randoNum = random_int(100, 999);
        // 取得時間搓(unix time) 最後末四碼(毫秒)
        $unixTime = substr(microtime(true), -4);
        // 生成18位數訂單號
        $orderNumber = $prefix.$randoNum.$unixTime;
        $data["order_number"] = $orderNumber;
        $pointOrder = new $this->pointOrder;
        $pointOrder->fill($data);
        $pointOrder->save();
        return $pointOrder->fresh();
    }
    /**
     * 取得列表
     * @param object $data 分頁搜尋過濾條件
     */
    public function getLists($data = null)
    {
        // 取得登入者資料
        $user = auth()->user();
        // 預設如果沒有傳送一頁呈現幾筆資料的話 會出現15筆
        $perPage = $data["perPage"] ?? 15;
        // 刪除perPage key 此 key 只是用來判斷一頁要呈現幾筆資料而已
        unset($data["perPage"]);
        // 判斷是否為系統使用者
        if ($user->group->group_code === "SYSTEM") {
            $pointOrderLists = $this->pointOrder->with(["userMember", "user.userable", "userOperator"]);
        } else {
            // 回傳管理者者自行建立的點數訂單列表 product_id 值
            $pointOrderIds = $user->userable->userOperator->pointOrder->pluck("point_order_id");
            // 取出點數訂單並關聯相關的表資料
            $pointOrderLists = $this->pointOrder->with(["userMember", "user.userable", "userOperator"])
            // 取出管理者創建的點數訂單
            ->whereIn("point_order_id", $pointOrderIds);
        }
        // 將點數訂單列表傳入過濾參數
        $responseData = $this->funcHelper->toWhereSort($pointOrderLists, $data)->paginate($perPage);
        // 將點數訂單列表啟用分頁
        return $responseData;
    }

    /**
     * 取得指定id列表資料
     * @param string $operatorId user_operators表 id
     * @param object $data 分頁搜尋過濾資料
     */
    public function getListsById($operatorId, $data = null)
    {
        $userGroupCode = auth()->user()->group->group_code;
        // 預設如果沒有傳送一頁呈現幾筆資料的話 會出現15筆
        $perPage = $data["perPage"] ?? 15;
        // 刪除不必要的 key
        unset($data["operatorId"]);
        // 判斷是否有傳入 perPage key
        if (!empty($data["perPage"])) {
            // 刪除perPage key 此 key 只是用來判斷一頁要呈現幾筆資料而已
            unset($data["perPage"]);
        }
        /**
         * 此判斷最主要是防止 系統已為使用者 透過id 方式 獲取到不屬於他底細的子帳號資料
         */
        if ($userGroupCode === "SYSTEM") {
            // 系統身份直接撈取管理者id對應的點數訂單列表
            $pointOrderLists = $this->userOperator->find($operatorId)->pointOrder()->with(["userMember", "user.userable", "userOperator"]);
        } else {
            // 回傳管理者者自行建立的點數訂單列表 point_order_id 值
            $pointOrderIds = auth()->user()->userable->userOperator->pointOrder->pluck("point_order_id");
            $pointOrderLists = $this->userOperator->find($operatorId)->pointOrder()->whereIn("point_order_id", $pointOrderIds)->with(["userMember", "user.userable", "userOperator"]);
        }
        // 將點數訂單列表傳入過濾參數
        $responseData = $this->funcHelper->toWhereSort($pointOrderLists, $data)->paginate($perPage);
        // 將點數訂單列表啟用分頁
        return $responseData;
    }

    /**
    * 取得儲值報表
    * @param data { type Object(物件) } 搜尋過濾資料
    * @param operatorId { type String or Number(字串或數字) } 管理者id
    * @param onleyOneOperator { type Boolean(布林值) } 判斷是否需要抓取下層管理者資料
    */
    public function getMoreData($data, $operatorId, $onleyOneOperator = false)
    {
        // 如果傳入0時取身份為系統時 不過過濾管理者id
        if (auth()->user()->group->group_code === "SYSTEM" && $operatorId == 0) {
            // 訂單資料
            $orders = $this->pointOrder->with(["userOperator"]);
        } else {
            // 判斷是否需要下抓取下層管理者id
            if ($onleyOneOperator) {
                // 只抓取指定管理者本身的id
                $userOperatorIds = $this->userOperator->where("operator_id", $operatorId)->pluck("operator_id");
            } else {
                // 取得對應管理者資料
                $userOperator = $this->userOperator->find($operatorId);
                // 取出包含自身管理者以及下層管理者id
                $userOperatorIds = $userOperator->descendantsAndSelf($operatorId)->pluck("operator_id");
            }
            // 訂單資料
            $orders = $this->pointOrder->with(["userOperator"])->whereIn("operator_id", $userOperatorIds);
        }
        // 搜尋結果時 無需過濾 管理者 id
        unset($data["operator_id"]);
        // 回傳搜尋結果
        $responseData = $this->funcHelper->toWhereSort($orders, $data)->get();
        return $responseData;
    }
}
