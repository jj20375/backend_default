<?php
namespace App\Services\User;

// 導入 user_designers 資料庫操作方法
use App\Repositories\User\UserDesignerRepository;
// 導入 users 資料庫操作方法
use App\Repositories\User\UserRepository;
// 導入 images 資料庫操作方式
use App\Repositories\ImageRepository;
// 導入 services 資料庫操作方法
use App\Repositories\StoreServiceRepository;
// 導入 service_infos 資料庫操作方法
use App\Repositories\StoreServiceInfoRepository;
// 導入 tags 資料庫操作方法
use App\Repositories\TagRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserDesignerService
{

    // UserDesignerRepository Repository 指定變數
    protected $userDesignerRepository;
    // UserRepository Repository 指定變數
    protected $userRepository;
    // Imgage Repository 指定變數
    protected $imageRepository;
    // StoreServiceRepository 指定變數
    protected $storeServiceRepository;
    // StoreServiceInfoRepository 指定變數
    protected $storeServiceInfoRepository;
    // TagRepository 指定變數
    protected $tagRepository;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(
        UserDesignerRepository $userDesignerRepository,
        UserRepository $userRepository,
        ImageRepository $imageRepository,
        StoreServiceRepository $storeServiceRepository,
        StoreServiceInfoRepository $storeServiceInfoRepository,
        TagRepository $tagRepository,
        FuncHelper $funcHelper
    ) {
        $this->userRepository = $userRepository;
        $this->userDesignerRepository = $userDesignerRepository;
        $this->imageRepository = $imageRepository;
        $this->storeServiceRepository = $storeServiceRepository;
        $this->storeServiceInfoRepository = $storeServiceInfoRepository;
        $this->tagRepository = $tagRepository;
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
        // 將新增資料 傳入 userDesigner repository 執行新增助理方法
        $userDesigner = $this->userDesignerRepository->create($sendData);
        // 判斷是否有傳入圖片檔案
        if (!empty($imgFile)) {
            $imgSendData = [
                "operator_id" => $data["operator_id"],
                "imgFile" => $imgFile,
                "imgType" => "designer",
            ];
            $this->imageRepository->create($imgSendData, $userDesigner);
        }
        // 將新增資料 傳入 user repository 執行新增使用者方法
        $user = $this->userRepository->create($sendData, $userDesigner);
        return ["userDesigner" => $userDesigner, "user" => $user];
    }
    /**
     * 更新服務提供者
     * @param object $data 更新服務提供者資料
     * @param $imgFile 圖片檔案
     */
    public function update($data, $imgFile = null)
    {
        // 圖片資料
        $imageData = $this->userDesignerRepository->getData("designer_id", $data["designer_id"])->image;
        // 表單資料
        $sendData = $data;
        // 判斷是否有需要更新密碼 如有傳送 password key 代表需重新更新密碼 因此需將密碼加密
        if (!empty($data["password"])) {
            // 密碼加密
            $sendData["password"] = bcrypt($data["password"]);
        }
        // 將新增資料 傳入 userDesigner repository 執行更新助理方法
        $userDesigner = $this->userDesignerRepository->update($sendData);
        // 判斷是否有傳入圖片檔案
        if (!empty($imgFile)) {
            $imgSendData = [
                "operator_id" => $data["operator_id"],
                "imgFile" => $imgFile,
                "imgType" => "designer",
            ];
            if ($imageData !== null) {
                $imgSendData["imageId"] = $imageData->image_id;
                $this->imageRepository->delete($imageData->img_path);
                $this->imageRepository->update($imgSendData);
            } else {
                $this->imageRepository->create($imgSendData, $userDesigner);
            }
        }
        // 將新增資料 傳入 user repository 執行更新使用者方法
        $user = $this->userRepository->update($sendData);
        return ["userDesigner" => $userDesigner, "user" => $user];
    }
    /**
     * 刪除圖片
     * @param string @imgPath 圖片路徑
     * @param string @designerId 服務提供者id
     */
    public function deleteImage($imgPath, $designerId)
    {
        // 取的圖片id
        $imageId = $this->userDesignerRepository->getData("designer_id", $designerId)->image->image_id;
        $image = $this->imageRepository->delete($imgPath, true, $imageId);
        return $image;
    }
    /**
    * 取的列表資料
    * @param object 分頁搜尋過濾資料
    */
    public function getLists($data)
    {
        $userDesignerLists = $this->userDesignerRepository->getLists($data);
        return $userDesignerLists;
    }
    /**
     * 取得指定id列表資料
     * @param string $operatorId user_operators表id
     * @param object $data 分頁搜尋過濾資料
     */
    public function getListsById($operatorId, $data)
    {
        $userDesignerLists = $this->userDesignerRepository->getListsById($operatorId, $data);
        return $userDesignerLists;
    }
    /**
     * 取得可選擇列表
     * @param string $operatorId 管理者id
     */
    public function getSelectLists($operatorId)
    {
        $userDesignerLists = $this->userDesignerRepository->getSelectLists($operatorId);
        return $userDesignerLists;
    }
    /**
     * 使用帳號取得單ㄧ資料
     * @param string $account 服務提供者帳號
     */
    public function getDataByAccount($account)
    {
        $userDesigner = $this->userDesignerRepository->getData("account", $account);
        return $userDesigner;
    }

    /**
     * 新建服務
     * @param object $data 服務資料
     * @param object $infoData 服務詳細資料
     * @param string $designerId 服務提供者id
     */
    public function createService($data, $infoData, $designerId)
    {
        // 關聯表id
        $id = $this->userDesignerRepository->getData("designer_id", $designerId)->designer_id;
        // 判斷是否有傳入 tagIds key
        if (isset($infoData["tagIds"])) {
            // 新增服務項目 包含 tag
            $service = $this->storeServiceRepository->create($data, $id, "App\Models\User\UserDesigner", $infoData["tagIds"]);
        } else {
            // 新增服務項目 不包含 tag
            $service = $this->storeServiceRepository->create($data, $id, "App\Models\User\UserDesigner");
        }
        // 取得關聯的 servcie_id
        $infoData["service_id"] = $service["service_id"];
        // 新增服務項目詳細資料
        $serviceInfo = $this->storeServiceInfoRepository->create($infoData);
        // 回傳變數
        $serviceData = $service;
        $serviceData["info"] = $serviceInfo;
        return $serviceData;
    }
    /**
     * 更新服務
     * @param object $data 服務資料
     * @param object $infoData 服務詳細資料
     * @param string $designerId 服務提供者id
     */
    public function updateService($data, $infoData, $designerId)
    {
        // 關聯表id
        $id = $this->userDesignerRepository->getData("designer_id", $designerId)->designer_id;
        // 判斷是否有傳入 tagIds key
        if (isset($infoData["tagIds"])) {
            // 新增服務項目 包含tag
            $service = $this->storeServiceRepository->update($data, $id, "App\Models\User\UserDesigner", $infoData["tagIds"]);
        } else {
            // 新增服務項目 不包含tag
            $service = $this->storeServiceRepository->update($data, $id, "App\Models\User\UserDesigner");
        }
        // 取得關聯的 servcie_id
        $infoData["service_id"] = $service["service_id"];
        // 新增服務項目詳細資料
        $serviceInfo = $this->storeServiceInfoRepository->update($infoData);
        // 回傳變數
        $serviceData = $service;
        $serviceData["info"] = $serviceInfo;
        return $serviceData;
    }

    /**
     * 取得服務列表
     * @param string $designerId 服務提供者id
     * @param object $data 分頁搜尋過濾資料
     */
    public function getServiceLists($designerId, $data)
    {
        $serviceLists = $this->storeServiceRepository->getLists("App\Models\User\UserDesigner", $designerId, $data);
        return $serviceLists;
    }
    /**
     * 取得服務列表
     * @param string $designerId 服務提供者id
     */
    public function getServiceSelectLists($designerId)
    {
        $serviceLists = $this->storeServiceRepository->getSelectLists("App\Models\User\UserDesigner", $designerId);
        return $serviceLists;
    }
}
