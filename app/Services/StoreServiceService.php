<?php
namespace App\Services;

// 導入 services 資料庫操作方法
use App\Repositories\StoreServiceRepository;
// 導入 service_infos 資料庫操作方法
use App\Repositories\StoreServiceInfoRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class StoreServiceService
{
    // StoreServiceRepository 指定變數
    protected $storeServiceRepository;
    // StoreServiceInfoRepository 指定變數
    protected $storeServiceInfoRepository;
    // 導入共用方法 指定變數
    protected $funcHelper;
    

    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(StoreServiceRepository $storeServiceRepository, StoreServiceInfoRepository $storeServiceInfoRepository, FuncHelper $funcHelper)
    {
        $this->storeServiceRepository = $storeServiceRepository;
        $this->storeServiceInfoRepository = $storeServiceInfoRepository;
        $this->funcHelper = $funcHelper;
    }
    
    /**
     * 取得列表
     * @param string $ableType 需要取出服務的多態關聯表 Model 路徑
     * @param string $ableId 需要取出服務的多態關聯表id
     */
    public function getLists($ableType, $ableId)
    {
        $storeServiceLists = $this->storeServiceRepository->getLists($ableType, $ableId);
        return $storeServiceLists;
    }
    /**
     * 使用id取得單ㄧable資料
     * @param string $serviceId 服務id
     * @param string $ableType 關聯表 model 路徑
     */
    public function getAbleDataById($serviceId, $ableType)
    {
        $storeService = $this->storeServiceRepository->getAbleData("service_id", $serviceId, $ableType);
        return $storeService;
    }
    /**
     * 使用id取得單1資料
     * @param string $serviceId 服務id
     */
    public function getDataById($serviceId)
    {
        $storeService = $this->storeServiceRepository->getData("service_id", $serviceId);
        return $storeService;
    }
}
