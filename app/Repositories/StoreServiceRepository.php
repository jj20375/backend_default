<?php
namespace App\Repositories;

// services表 model
use App\Models\StoreService;
// service_ables表 model
use App\Models\StoreServiceAble;
// 導入 共用方法
use App\Helpers\FuncHelper;
// tagService 方法
use App\Services\TagService;

class StoreServiceRepository
{

    // StoreService Model 指定變數
    protected $storeService;
    // StoreServiceAble Model 指定變數
    protected $storeServiceAble;
    // 導入共用方法 指定變數
    protected $funcHelper;
    // tagService 指定變數
    protected $tagService;

    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(StoreService $storeService, StoreServiceAble $storeServiceAble, FuncHelper $funcHelper, TagService $tagService)
    {
        $this->storeService = $storeService;
        $this->storeServiceAble = $storeServiceAble;
        $this->funcHelper = $funcHelper;
        $this->tagService = $tagService;
    }

    /**
     * 新增服務
     * @param object $data 新增服務資料
     * @param object $id 關聯表 id 值
     * @param string $ableType 多態關聯表Model 路徑
     * @param array $tagIds tag表的id
     */
    public function create($data, $id, $ableType, $tagIds = null)
    {
        $storeService = new $this->storeService;
        $storeService->fill($data);
        $storeService->save();
        // service_ables 表 多對多多態關聯
        $storeService->morphByManyUser($ableType)->attach($id, ["name" => $data["name"]]);
        // 判斷是否有傳入tag id
        if (is_array($tagIds)) {
            // 新增tag 多對多多態關聯
            $this->funcHelper->addTag($storeService, $tagIds);
        }
        return $storeService->fresh();
    }
    /**
     * 更新服務
     * @param object $data 更新服務資料
     *
     * @param string $id 關聯表 id 值
     * @param string $ableType 多態關聯表Model 路徑
     * @param array $tagIds tag表的id
     */
    public function update($data, $id, $ableType, $tagIds = null)
    {
        $storeService = $this->storeService->find($data["service_id"]);
        $storeService->fill($data);
        $storeService->save();
        // 判斷是否有傳入tag id
        if (is_array($tagIds)) {
            // 更新tag 多對多多態關聯
            $storeService->morphToManyTag()->sync($tagIds);
        }
        /**
         * service_ables 表 多對多多態關聯 更新
         */
        // 判斷關聯表id 與關聯類型 並判斷 service_id 找出對應資料
        $StoreServiceAble = $this->storeServiceAble->where("serviceable_id", $id)->where("serviceable_type", $ableType)->where("service_id", $data["service_id"])->first();
        // 更新多對多多態表 的 name
        $StoreServiceAble->name = $data["name"];
        $StoreServiceAble->save();
        return $storeService->fresh();
    }
    /**
     * 取得列表
     * @param string $ableType 需要取出服務的多態關聯表 Model 路徑
     * @param string $ableId 需要取出服務的多態關聯表id
     * @param object $data 分頁搜尋過濾資料
     */
    public function getLists($ableType, $ableId, $data = null)
    {
        // 預設如果沒有傳送一頁呈現幾筆資料的話 會出現15筆
        $perPage = $data["perPage"] ?? 15;
        // 判斷是否有傳入 perPage key
        if (!empty($data["perPage"])) {
            // 刪除perPage key 此 key 只是用來判斷一頁要呈現幾筆資料而已
            unset($data["perPage"]);
        }
        // 判斷關聯表 為哪一個 model 回傳 1對1關聯的 model資料
        $ableUser = $ableType === "App\\Models\\User\\UserOperator" ? "operator" : "designer";
        // 判斷如果傳入 0 的話只過濾 關聯表 不過濾使用者
        if ($ableId == 0) {
            $storeServiceLists = $this->storeServiceAble->with(["storeService.storeServiceInfo","storeService.category", "serviceable", $ableUser])->whereHasMorph("serviceable", [$ableType]);
        } else {
            $storeServiceLists = $this->storeServiceAble->with(["storeService.storeServiceInfo","storeService.category", "serviceable", $ableUser])->whereHasMorph("serviceable", [$ableType], function ($query) use ($ableId) {
                $query->where("serviceable_id", $ableId);
            });
        }
        // 將列表資料導入分頁
        $responseData = $this->funcHelper->toWhereSort($storeServiceLists, $data)->paginate($perPage);
        return $responseData;
    }
    /**
     * 取得選擇列表
     * @param string $ableType 需要取出服務的多態關聯表 Model 路徑
     * @param string $ableId 需要取出服務的多態關聯表id
     */
    public function getSelectLists($ableType, $ableId)
    {
        // 判斷關聯表 為哪一個 model 回傳 1對1關聯的 model資料
        $ableUser = $ableType === "App\\Models\\User\\UserOperator" ? "operator" : "designer";
        // 判斷如果傳入 0 的話只過濾 關聯表 不過濾使用者
        if ($ableId == 0) {
            $storeServiceLists = $this->storeServiceAble->with(["storeService.storeServiceInfo","storeService.category", $ableUser])->whereHasMorph("serviceable", [$ableType])->get();
        } else {
            $storeServiceLists = $this->storeServiceAble->with(["storeService.storeServiceInfo","storeService.category", $ableUser])->whereHasMorph("serviceable", [$ableType], function ($query) use ($ableId) {
                $query->where("serviceable_id", $ableId);
            })->get();
        }
        return $storeServiceLists;
    }

    /**
     * 取得Able資料
     * @param string $column 資料表欄位
     * @param string $data 欄位資料
     * @param string $ableType 需要取出服務的多態關聯表 Model 路徑
     */
    public function getAbleData($column, $data, $ableType)
    {
        // 判斷關聯表 為哪一個 model 回傳 1對1關聯的 model資料
        $ableUser = $ableType === "App\\Models\\User\\UserOperator" ? "operator" : "designer";
        $storeService = $this->storeServiceAble->with(["storeService.storeServiceInfo","storeService.category", "storeService.morphToManyTag", $ableUser])->where($column, $data)->first();
        return $storeService;
    }
    /**
     * 取得資料
     * @param string $column 資料表欄位
     * @param string $data 欄位資料
     */
    public function getData($column, $data)
    {
        // 判斷關聯表 為哪一個 model 回傳 1對1關聯的 model資料
        $storeService = $this->storeService->with(["storeServiceInfo","category", "morphToManyTag"])->where($column, $data)->first();
        return $storeService;
    }
}
