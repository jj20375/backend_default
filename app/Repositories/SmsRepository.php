<?php
namespace App\Repositories;

// sms表 model
use App\Models\Sms;
// user_operators表 model
use App\Models\User\UserOperator;
// 導入 共用方法
use App\Helpers\FuncHelper;

class SmsRepository
{

    // Sms Model 指定變數
    protected $sms;
    // UserOperator Model 指定變數
    protected $userOperator;
    // 導入共用方法 指定變數
    protected $funcHelper;

    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(Sms $sms, UserOperator $userOperator, FuncHelper $funcHelper)
    {
        $this->sms = $sms;
        $this->userOperator = $userOperator;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增簡訊商
     * @param data { type Object(物件) } 新增簡訊商資料
     */
    public function create($data)
    {
        $sms = new $this->sms;
        $sms->fill($data);
        $sms->save();
        return $sms->fresh();
    }
    /**
     * 更新簡訊商
     * @param data { type Object(物件) } 更新簡訊商資料
     */
    public function update($data, $sms)
    {
        // $sms = $this->sms->find($data["sms_id"]);
        $sms->fill($data);
        $sms->save();
        return $sms->fresh();
    }

    /**
     * 取得資料
     * @param column { type String(字串) } 資料表欄位
     * @param data { type Object(物件) } 欄位資料
     * @param operatorId { type Number or String(數字或字串) } 管理者id
     */
    public function getData($column, $data)
    {
        $sms = $this->sms->where($column, $data)->first();
        return $sms;
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
            $smsLists = $this->sms->with(["userOperator"]);
            // 將簡訊商列表傳入過濾參數 並啟用分頁機制
            $responseData = $this->funcHelper->toWhereSort($smsLists, $data)->paginate($perPage);
        } else {
            // 回傳管理者者自行建立的簡訊商列表 sms_id 值
            $smsIds = collect($user->userable->userOperator->sms)->pluck("sms_id");
            // 取出簡訊商並關聯相關的表資料
            $smsLists = $this->sms->with(["userOperator"])
            // 取出管理者創建的簡訊商
            ->whereIn("sms_id", $smsIds);
            // 將簡訊商列表傳入過濾參數
            $filterData = $this->funcHelper->toWhereSort($smsLists, $data);
            // 過濾出可使用此簡訊商的管理者 如果有此欄位有可使用的管理者id 代表此管理者可以使用此簡訊商
            $filterOperatorIdsData = $filterData->orWhereJsonContains("operator_ids", $user->userable->userOperator->operator_id);
            // 將簡訊商列表傳入過濾參數 並啟用分頁機制
            $responseData = $this->funcHelper->toWhereSort($filterOperatorIdsData, $data)->paginate($perPage);
        }
        return $responseData;
    }

    /**
     * 取得可選擇的列表
     * @param string $operatorId 管理者id
     */
    public function getSelectLists($operatorId)
    {
        // 取得登入者群組代碼
        $userGroupCode = auth()->user()->group->group_code;
        // 判斷是否為系統登入者
        if ($userGroupCode === "SYSTEM") {
            // 取出屬於指定管理者id自己創建的簡訊商id
            $smsIds = $this->userOperator->find($operatorId)->sms->pluck("sms_id");
        } else {
            // 取出屬於該管理者自己創建的簡訊商id
            $smsIds = auth()->user()->userable->userOperator->sms->pluck("sms_id");
        }
        /**
         * 此判斷式主要用來取出
         * 1.屬於登入的管理者創建的簡訊商
         * 2.屬於系統創建的簡訊商 且有指定管理者可以使用
         * 3.屬於共用簡訊商也就是值為 [](空陣列) 或 null
         */
        $smsLists = $this->sms
        // 過濾簡訊商 id
        ->whereIn("sms_id", $smsIds)
        // 判斷是否有啟用簡訊商
        ->where("status", 5)
        // 取出共用簡訊商管理者
        ->orWhereJsonContains("operator_ids", (int)$operatorId)->where("status", 5)
        ->get();
        return $smsLists;
    }

    /**
     * 刪除簡訊商
     * @param smsId { type String or Number(字串或數字) } 簡訊商id
     */
    public function delete($smsId)
    {
        $sms = $this->sms->destroy($smsId);
        return $sms;
    }
}
