<?php
namespace App\Services;

// 導入 white_lists 資料庫操作方法
use App\Repositories\WhiteListRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class WhiteListService
{

    // WhiteListRepository 指定變數
    protected $whiteListRepository;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    
    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(WhiteListRepository $whiteListRepository, FuncHelper $funcHelper)
    {
        $this->whiteListRepository = $whiteListRepository;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增ip 白名單
     * @param object $data 新增ip白名單資料
     * @param string $userId 使用者id
     */
    public function create($data, $userId)
    {
        $whiteList = $this->whiteListRepository->create($data, $userId);
        return $whiteList;
    }

    /**
     * 更新ip 白名單
     * @param object $data 新增ip白名單資料
     * @param string $userId 使用者id
     */
    public function update($data, $userId)
    {
        $whiteList = $this->whiteListRepository->update($data, $userId);
        // 判斷是否更新成功
        if ($whiteList === false) {
            return $this->funcHelper->errorBack("更新失敗", 500);
        }
        return $whiteList;
    }
}
