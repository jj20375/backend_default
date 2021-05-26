<?php
namespace App\Repositories;

// service_infos表 model
use App\Models\StoreServiceInfo;

class StoreServiceInfoRepository
{
    // StoreServiceInfo Model 指定變數
    protected $storeServiceInfo;

    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(StoreServiceInfo $storeServiceInfo)
    {
        $this->storeServiceInfo = $storeServiceInfo;
    }
    /**
     * 新增服務詳細資料
     * @param object $data 新增服務詳細資料
     */
    public function create($data)
    {
        $storeServiceInfo = new $this->storeServiceInfo;
        $storeServiceInfo->fill($data);
        $storeServiceInfo->save();
        return $storeServiceInfo->fresh();
    }
    /**
     * 更新服務詳細資料
     * @param object $data 更新服務詳細資料
     */
    public function update($data)
    {
        $storeServiceInfo = $this->storeServiceInfo->where("service_id", $data["service_id"])->first();
        $storeServiceInfo->fill($data);
        $storeServiceInfo->save();
        return $storeServiceInfo->fresh();
    }
}
