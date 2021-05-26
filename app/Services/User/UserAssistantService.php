<?php
namespace App\Services\User;

// 導入 user_assistants 資料庫操作方法
use App\Repositories\User\UserAssistantRepository;
// 導入 users 資料庫操作方法
use App\Repositories\User\UserRepository;
// 導入 images 資料庫操作方式
use App\Repositories\ImageRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserAssistantService
{

    // UserAssistantRepository Repository 指定變數
    protected $userAssistantRepository;
    // UserRepository Repository 指定變數
    protected $userRepository;
    // Imgage Repository 指定變數
    protected $imageRepository;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(UserAssistantRepository $userAssistantRepository, UserRepository $userRepository, ImageRepository $imageRepository, FuncHelper $funcHelper)
    {
        $this->userRepository = $userRepository;
        $this->userAssistantRepository = $userAssistantRepository;
        $this->imageRepository = $imageRepository;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增助理
     * @param object $data 新增助理資料
     * @param $imgFile 圖片檔案
     */
    public function create($data, $imgFile = null)
    {
        // 判斷帳號是否重複
        if ($this->userRepository->checkDuplicate("account", $data["account"])) {
            return $this->funcHelper->errorBack("帳號重複", 500);
        }
        // 表單資料
        $sendData = $data;
        // 密碼加密
        $sendData["password"] = bcrypt($data["password"]);
        // 創建ip
        $sendData["create_ip"] = request()->ip();
        // 將新增資料 傳入 userAssistant repository 執行新增助理方法
        $userAssistant = $this->userAssistantRepository->create($sendData);
        // 判斷是否有傳入圖片檔案
        if (!empty($imgFile)) {
            $imgSendData = [
                "operator_id" => $data["operator_id"],
                "imgFile" => $imgFile,
                "imgType" => "assistant",
            ];
            $this->imageRepository->create($imgSendData, $userAssistant);
        }
        // 將新增資料 傳入 user repository 執行新增使用者方法
        $user = $this->userRepository->create($sendData, $userAssistant);
        return ["userAssistant" => $userAssistant, "user" => $user];
    }
    /**
     * 更新助理
     * @param object $data 更新助理資料
     * @param $imgFile 圖片檔案
     */
    public function update($data, $imgFile = null)
    {
        // 圖片資料
        $imageData = $this->userAssistantRepository->getData("assistant_id", $data["assistant_id"])->image;
        // 表單資料
        $sendData = $data;
        // 判斷是否有需要更新密碼 如有傳送 password key 代表需重新更新密碼 因此需將密碼加密
        if (!empty($data["password"])) {
            // 密碼加密
            $sendData["password"] = bcrypt($data["password"]);
        }
        // 將新增資料 傳入 userAssistant repository 執行更新助理方法
        $userAssistant = $this->userAssistantRepository->update($sendData);
        // 判斷是否有傳入圖片檔案
        if (!empty($imgFile)) {
            $imgSendData = [
                "operator_id" => $data["operator_id"],
                "imgFile" => $imgFile,
                "imgType" => "assistant",
            ];
            if ($imageData !== null) {
                $imgSendData["imageId"] = $imageData->image_id;
                $this->imageRepository->delete($imageData->img_path);
                $this->imageRepository->update($imgSendData);
            } else {
                $this->imageRepository->create($imgSendData, $userAssistant);
            }
        }
        // 將新增資料 傳入 user repository 執行更新使用者方法
        $user = $this->userRepository->update($sendData);
        return ["userAssistant" => $userAssistant, "user" => $user];
    }

    /**
     * 刪除圖片
     * @param string @imgPath 圖片路徑
     * @param string @assistantId 助理id
     */
    public function deleteImage($imgPath, $assistantId)
    {
        // 取的圖片id
        $imageId = $this->userAssistantRepository->getData("assistant_id", $assistantId)->image->image_id;
        $image = $this->imageRepository->delete($imgPath, true, $imageId);
        return $image;
    }
    
    /**
     * 取的列表資料
     * @param object 分頁搜尋過濾資料
     */
    public function getLists($data)
    {
        $userAssistantLists = $this->userAssistantRepository->getLists($data);
        return $userAssistantLists;
    }

    /**
     * 取得指定id列表資料
     * @param string $operatorId user_operators表id
     * @param object $data 分頁搜尋過濾資料
     */
    public function getListsById($operatorId, $data)
    {
        $userAssistantLists = $this->userAssistantRepository->getListsById($operatorId, $data);
        return $userAssistantLists;
    }
    
    /**
     * 取得可用列表
     * @param string $operatorId 管理者id
     */
    public function getSelectLists($operatorId)
    {
        $userAssistantLists = $this->userAssistantRepository->getSelectLists($operatorId);
        return $userAssistantLists;
    }

    /**
     * 使用帳號取得單ㄧ資料
     * @param string $account 助理帳號
     */
    public function getDataByAccount($account)
    {
        $userAssistant = $this->userAssistantRepository->getData("account", $account);
        return $userAssistant;
    }
}
