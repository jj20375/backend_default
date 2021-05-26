<?php
namespace App\Services\User;

// 導入 user_operators 資料庫操作方法
use App\Repositories\User\UserOperatorRepository;
// 導入 users 資料庫操作方法
use App\Repositories\User\UserRepository;
// 導入 services 資料庫操作方法
use App\Repositories\StoreServiceRepository;
// 導入 service_infos 資料庫操作方法
use App\Repositories\StoreServiceInfoRepository;
// 導入 tags 資料庫操作方法
use App\Repositories\TagRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserOperatorService
{
    
    // UserOperatorRepository Repository 指定變數
    protected $userOperatorRepository;
    // UserRepository Repository 指定變數
    protected $userRepository;
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
        UserOperatorRepository $userOperatorRepository,
        UserRepository $userRepository,
        StoreServiceRepository $storeServiceRepository,
        StoreServiceInfoRepository $storeServiceInfoRepository,
        TagRepository $tagRepository,
        FuncHelper $funcHelper
    ) {
        $this->userRepository = $userRepository;
        $this->userOperatorRepository = $userOperatorRepository;
        $this->storeServiceRepository = $storeServiceRepository;
        $this->storeServiceInfoRepository = $storeServiceInfoRepository;
        $this->tagRepository = $tagRepository;
        $this->funcHelper = $funcHelper;
    }
    /**
     * 新增經營者
     */
    public function create($data)
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
        // 將新增資料 傳入 userOperator repository 執行新增經營者方法
        $userOperator = $this->userOperatorRepository->create($sendData);
        // 將新增資料 傳入 user repository 執行新增使用者方法
        $user = $this->userRepository->create($sendData, $userOperator);
        return ["userOperator" => $userOperator, "user" => $user];
    }
    /**
     * 更新經營者
     */
    public function update($data)
    {
        // 表單資料
        $sendData = $data;
        // 判斷是否有需要更新密碼 如有傳送 password key 代表需重新更新密碼 因此需將密碼加密
        if (!empty($data["password"])) {
            // 密碼加密
            $sendData["password"] = bcrypt($data["password"]);
        }
        // 將更新資料 傳入 userOperator repository 執行更新經營者方法
        $userOperator = $this->userOperatorRepository->update($sendData);
        // 將更新資料 傳入 user repository 執行更新使用者方法
        $user = $this->userRepository->update($sendData);
        return ["userOperator" => $userOperator, "user" => $user];
    }
    /**
     * 判斷是否有重複資料
     * @param string $column 欄位名稱
     * @param string $data 欄位資料
     */
    public function isDuplicate($column = null, $data = null)
    {
        // 判斷指定資料庫欄位是否有重複資料
        $haveDuplicate = $this->userOperatorRepository->checkDuplicate($column, $data);
        return $haveDuplicate;
    }
    /**
     * 取得管理者資料
     * @param string $account 管理者帳號
     */
    public function getDataByAccount($account)
    {
        $userOperator = $this->userOperatorRepository->getData("account", $account);
        return $userOperator;
    }
    /**
     * 取得樹狀列表
     * @param object $operatorId 上層管理者id
     */
    public function getTreeLists($operatorId)
    {
        $userOperatorLists = $this->userOperatorRepository->getTreeLists($operatorId);
        return $userOperatorLists;
    }
    /**
     * 取得列表
     * @param object $operatorId 上層管理者id
     */
    public function getLists($operatorId)
    {
        $userOperatorLists = $this->userOperatorRepository->getLists($operatorId);
        return $userOperatorLists;
    }
    /**
     * 取得可選擇列表
     * @param string $operatorId 管理者id
     */
    public function getSelectLists($operatorId)
    {
        $userOpeatorLists = $this->userOperatorRepository->getSelectLists($operatorId);
        return $userOpeatorLists;
    }
    /**
     * 新建服務
     * @param object $data 服務資料
     * @param object $infoData 服務詳細資料
     * @param string $operatorId 管理者id
     */
    public function createService($data, $infoData, $operatorId)
    {
        // 關聯表id
        $id = $this->userOperatorRepository->getData("operator_id", $operatorId)->operator_id;
        // 判斷是否有傳入 tagIds key
        if (isset($infoData["tagIds"])) {
            // 新增服務項目 包含 tag
            $service = $this->storeServiceRepository->create($data, $id, "App\Models\User\UserOperator", $infoData["tagIds"]);
        } else {
            // 新增服務項目 不包含 tag
            $service = $this->storeServiceRepository->create($data, $id, "App\Models\User\UserOperator");
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
    * @param string $operatorId 管理者id
    */
    public function updateService($data, $infoData, $operatorId)
    {
        // 關聯表id
        $id = $this->userOperatorRepository->getData("operator_id", $operatorId)->operator_id;
        // 判斷是否有傳入 tagIds key
        if (isset($infoData["tagIds"])) {
            // 新增服務項目 包含tag
            $service = $this->storeServiceRepository->update($data, $id, "App\Models\User\UserOperator", $infoData["tagIds"]);
        } else {
            // 新增服務項目 不包含tag
            $service = $this->storeServiceRepository->update($data, $id, "App\Models\User\UserOperator");
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
     * @param string $operatorId 管理者id
     * @param object $data 分頁搜尋過濾資料
     */
    public function getServiceLists($operatorId, $data)
    {
        $serviceLists = $this->storeServiceRepository->getLists("App\Models\User\UserOperator", $operatorId, $data);
        return $serviceLists;
    }
    /**
     * 取得服務列表
     * @param string $operatorId 管理者id
     */
    public function getServiceSelectLists($operatorId)
    {
        $serviceLists = $this->storeServiceRepository->getSelectLists("App\Models\User\UserOperator", $operatorId);
        return $serviceLists;
    }
    /**
     * 模糊比對搜尋使用
     * @param userName { type String(字串) }管理者名稱
     * @param account { type String(字串) }帳號
     */
    public function remoteLists($userName = null, $account = null) {
        $lists = $this->userOperatorRepository->remoteLists($userName, $account);
        return $lists;
    }
}
