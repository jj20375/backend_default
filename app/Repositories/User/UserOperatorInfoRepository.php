<?php
namespace App\Repositories\User;

// 經營者表 詳細資料 model
use App\Models\User\UserOperatorInfo;
// 檔案上傳方法
use Illuminate\Support\Facades\Storage;

class UserOperatorInfoRepository
{

    // UserOperatorInfo Model 指定變數
    protected $userOperatorInfo;

    /*
    * 將需要使用的Model通過建構函式例項化
    */
    public function __construct(UserOperatorInfo $userOperatorInfo)
    {
        $this->userOperatorInfo = $userOperatorInfo;
    }

    /**
     * 新增經營者詳細資料
     * @param object $data 新增經營者詳細資料
     */
    public function create($data)
    {
        $operatorInfo = new $this->userOperatorInfo;
        $operatorInfo->fill($data);
        $operatorInfo->save();
        return $operatorInfo->fresh();
    }
    /**
     * 更新經營者詳細資料
     * @param object $data 更新經營者詳細資料
     */
    public function update($data)
    {
        $operatorInfo = $this->userOperatorInfo->where("operator_id", $data["operator_id"])->first();
        // 判斷port 有無傳入 null 值 如果有則不執行更新port
        if (isset($data["port"])) {
            if($data["port"] == "null") {
                unset($data["port"]);
            }
        }
        // 判斷如果尚未創建管理者詳細資料時 先執行創建
        if ($operatorInfo === null) {
            $operatorInfo = $this->userOperatorInfo->create($data);
            // 判斷是否有傳入Logo圖檔
            if (!empty($data["logo"])) {
                // 使用 google cloud storage 方法
                $disk = Storage::disk('gcs');
                // 更新 logo 圖檔至 google cloud storage
                $path = $disk->putFile("operator/".$data["operator_id"], $data["logo"], "public");
                // 圖片路徑
                $operatorInfo->logo = $path;
                // 再執行一次更新
                $operatorInfo->save();
            }
            return $operatorInfo;
        }
        // logo 圖片路徑(圖片存在 google cloud storage)
        $logoPath = $operatorInfo->logo;
        $operatorInfo->fill($data);
        // 判斷是否有傳入Logo圖檔
        if (!empty($data["logo"])) {
            // 使用 google cloud storage 方法
            $disk = Storage::disk('gcs');
            // 判斷 logo欄位 是否有圖片路徑 如果有則執行刪除原始logo檔案
            if ($logoPath !== null) {
                $disk->delete($logoPath);
            }
            // 更新 logo 圖檔至 google cloud storage
            $path = $disk->putFile("operator/".$data["operator_id"], $data["logo"], "public");
            // 圖片路徑
            $operatorInfo->logo = $path;
        }
        $operatorInfo->save();
        return $operatorInfo->fresh();
    }
}
