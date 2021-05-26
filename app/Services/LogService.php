<?php
namespace App\Services;

// 導入 logs 資料庫操作方法
use App\Repositories\LogRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class LogService
{

    // LogRepository 指定變數
    protected $logRepository;

    
    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    public function getPermissionGroupLog($data = null) {
        $logLists = $this->logRepository->getLists($data);
    }
}
