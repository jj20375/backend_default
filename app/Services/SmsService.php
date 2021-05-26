<?php
namespace App\Services;

// 導入 sms 資料庫操作方法
use App\Repositories\SmsRepository;
// 導入台灣簡訊服務方法
use App\Services\TwSmsService;

class SmsService
{

    // SmsRepository 指定變數
    protected $smsRepository;
    // TwSmsService 指定變數
    protected $twSmsService;
    
    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(SmsRepository $smsRepository, TwSmsService $twSmsService)
    {
        $this->smsRepository = $smsRepository;
        $this->twSmsService = $twSmsService;
    }
    /**
     * 取得單一資料
     * @param smsId { type String or Number(字串或數字) } 簡訊商id
     */
    public function getData($smsId)
    {
        $sms = $this->smsRepository->getData("sms_id", $smsId);
        return $sms;
    }

    /**
     * 取得列表
     * @param object $data 列表搜尋過濾資料
     */
    public function getLists($data = null)
    {
        $smsLists = $this->smsRepository->getLists($data);
        return $smsLists;
    }
    /**
     * 取得可選擇列表
     * @param string $operatorId 管理者id
     */
    public function getSelectLists($operatorId)
    {
        $smsLists = $this->smsRepository->getSelectLists($operatorId);
        return $smsLists;
    }
    
    /**
     * 判斷傳入簡訊商key與回傳方法
     * @param key { type String(字串) } 簡訊商代碼
     * @param type { type String(字串) } 呼叫方法
     * @param data { type Object(物件) } 傳送資料
     * @param operatorId { type String or Number(字串或數字) } 管理者id
     */
    public function checkKeyMethod($key, $type, $data = null, $smsId = null)
    {
        // 初始化簡訊商key
        $sms = [
            "twSms" => [],
        ];
        // 判斷是否有此簡訊商 如果沒有回傳false
        if (!isset($sms[$key])) {
            return false;
        }
        switch ($key) {
            case 'twSms':
                // 台灣簡訊商方法
                $sms[$key] = [
                    "create" => $type == "create" ? $this->twSmsService->create($data) : false,
                    "update" => $type == "update" ? $this->twSmsService->update($data) : false,
                    "sendMessage" => $type == "sendMessage" ? $this->twSmsService->sendMessage($smsId, $data) : false,
                    "getKeyData" => $type == "getKeyData" ? $this->twSmsService->getKeyData() : false,
                ];
                break;
            default:
                return false;
                break;
        }
        return $sms[$key][$type];
    }
    /**
     * 刪除簡訊商
     */
    public function delete($smsId)
    {
        $sms = $this->smsRepository->delete($smsId);
        return $sms;
    }
}
