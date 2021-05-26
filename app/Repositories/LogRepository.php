<?php
namespace App\Repositories;

// logs表 model
use App\Models\Log;

class LogRepository
{

    // Log Model 指定變數
    protected $log;

    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(Log $log)
    {
        $this->log = $log;
    }
    
    /**
     * 新增log
     * @param object $data 新增分類資料
     * @param object $ableOrm 多態關聯表資料
     */
    public function create($data, $ableOrm)
    {
        $log = new $this->log;
        $log->log_able()->associate($ableOrm);
        $log->fill($data);
        $log->save();
        return $log->fresh();
    }

    /**
     * 取得列表
     * @param object $data 分頁搜尋過濾條件
     */
    public function getLists($data = null)
    {
        // 預設如果沒有傳送一頁呈現幾筆資料的話 會出現15筆
        $perPage = $data["perPage"] ?? 15;
        // 刪除perPage key 此 key 只是用來判斷一頁要呈現幾筆資料而已
        unset($data["perPage"]);
        // 判斷是否為系統使用者
        $logLists = $this->product->with(["userOperator", "category", "provider", "morphToManyTag"]);
        // 將產品列表傳入過濾參數
        $responseData = $this->funcHelper->toWhereSort($logLists, $data)->paginate($perPage);
        // 將產品列表啟用分頁
        return $responseData;
    }
}
