<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
// 導入 sms 資料庫操作方法
use App\Repositories\SmsRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class TwSmsService
{

    // SmsRepository 指定變數
    protected $smsRepository;
        // 導入共用方法 指定變數
        protected $funcHelper;

    
    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct (SmsRepository $smsRepository, FuncHelper $funcHelper)
    {
        $this->smsRepository = $smsRepository;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增台灣簡訊
     * @param data { type Object(物件) } 新增簡訊商資料
     */
    public function create($data) {
        // 解析json欄位
        $keyData = json_decode($data["key_data"]);
        // 加密password資料
        $keyData->password = encrypt($keyData->password);
        // 更新$key_data值
        $data["key_data"] = json_encode($keyData);
        // 新增簡訊商
        $sms = $this->smsRepository->create($data);
        return $sms;
    }

    /**
     * 更新台灣簡訊
     * @param data { type Object(物件) } 更新簡訊商資料
     */
    public function update($data) {
        $sms = $this->smsRepository->getData("sms_id", $data["sms_id"]);
        if(isset($data["key_data"])) {
            // 傳送表單$key_data json解析
            $keyData = json_decode($data["key_data"]);
            if(json_decode($sms["key_data"])->password !== $keyData->password ) {
                // 加密password資料
                $keyData->password = encrypt($keyData->password);
                // 更新$key_data值
                $data["key_data"] = json_encode($keyData);
            }
        }
        // 更新簡訊商
        $sms = $this->smsRepository->update($data, $sms);
        return $sms;
    }

    /**
     * 發送訊息
     * @param smsId { type String(字串) } 簡訊商代號
     * @param data { type Object(物件) } 發送資料
     */
    public function sendMessage($smsId, $data) {
        // 取得簡訊商資料
        $sms = $this->smsRepository->getData("sms_id", $smsId);
        // 取得簡訊商資料為null時 回傳錯誤
        if(empty($sms)) {
            return false;
        }
        $result = [];
        foreach($data["phones"] as $value) {
            // 解析json資料
            $smsKeyData = json_decode($sms["key_data"]);
            // 發送資料
            $sendData = [
                "username" => $smsKeyData->username,
                "password" => decrypt($smsKeyData->password),
                "mobile" => $value,
                "message" => urlencode($data["message"]),
                // 回調 url
                // "drurl" => "http://adminapi.erpfuckbackend.com/sms/callback",
            ];
            // 發送資料
            $res = Http::post($sms["httpType"]."://".$sms["url"]."?username={$sendData['username']}&password={$sendData['password']}&mobile={$sendData['mobile']}&message={$sendData['message']}");
            $result[] = ["message" => $res->json(), "mobile" => $value];
        }
        return $result;
    }

    // 新增簡訊商需要輸入至key_data欄位
    public function getKeyData() {
        $smsKey = [
            "username" => [ "label" => "帳號", "type" => "input" ],
            "password" => [ "label" => "密碼", "type" => "input" ],
        ];
        return $smsKey;
    }

}
