<?php
namespace App\Services\Order;

// 導入 point_orders 資料庫操作方法
use App\Repositories\Order\PointOrderRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class PointOrderService
{

    // PointOrderRepository 指定變數
    protected $pointOrderRepository;
    // Imgage Repository 指定變數
    protected $imageRepository;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(PointOrderRepository $pointOrderRepository, FuncHelper $funcHelper)
    {
        $this->pointOrderRepository = $pointOrderRepository;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 取得列表
     * @param object $data 列表搜尋過濾資料
     */
    public function getLists($data = null)
    {
        $pointOrderLists = $this->pointOrderRepository->getLists($data);
        return $pointOrderLists;
    }
    /**
     * 取得指定id列表資料
     * @param string $operatorId user_operators表id
     * @param object $data 分頁搜尋過濾資料
     */
    public function getListsById($operatorId, $data)
    {
        $pointOrderLists = $this->pointOrderRepository->getListsById($operatorId, $data);
        return $pointOrderLists;
    }
}
