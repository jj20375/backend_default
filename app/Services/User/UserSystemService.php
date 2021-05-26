<?php
namespace App\Services\User;

// 導入 user_systems 資料庫操作方法
use App\Repositories\User\UserSystemRepository;
// 導入 users 資料庫操作方法
use App\Repositories\User\UserRepository;
// 導入 WhiteList 服務
use App\Services\WhiteListService;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserSystemService
{

    // UserSystemRepository Repository 指定變數
    protected $userSystemRepository;
    // UserRepository Repository 指定變數
    protected $userRepository;
    // WhiteListService Service 指定變數
    protected $whiteListService;
    // 導入共用方法
    protected $funcHelper;
    
    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(UserSystemRepository $userSystemRepository, UserRepository $userRepository, WhiteListService $whiteListService, FuncHelper $funcHelper)
    {
        $this->userRepository = $userRepository;
        $this->userSystemRepository = $userSystemRepository;
        $this->whiteListService = $whiteListService;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增系統使用者
     * @param object $data 新增資料
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
        // 將新增資料 傳入 userSystem repository 執行新增經營者方法
        $userSystem = $this->userSystemRepository->create($sendData);
        // 將新增資料 傳入 user repository 執行新增使用者方法
        $user = $this->userRepository->create($sendData, $userSystem);
        return ["userSystem" => $userSystem, "user" => $user];
    }
    /**
     * 更新系統使用者
     * @param object $data 更新資料
     */
    public function update($data)
    {
        // 表單資料
        $sendData = $data;
        if (!empty($sendData["password"])) {
            // 密碼加密
            $sendData["password"] = bcrypt($data["password"]);
        }
        // 將新增資料 傳入 userSystem repository 執行新增經營者方法
        $userSystem = $this->userSystemRepository->update($sendData);
        // 將新增資料 傳入 user repository 執行新增使用者方法
        $user = $this->userRepository->update($sendData);
        // 判斷是否有傳入白名單
        if (isset($infoData["lists"])) {
            // 更新ip白名單
            $whiteLists = $this->whiteListService->update($data, $data["user_id"]);
            return ["userSystem" => $userSystem, "user" => $user, "whiteLists" => $whiteLists];
        }
        return ["userSystem" => $userSystem, "user" => $user];
    }
    /**
     * 取得列表
     * @param object $data 分頁搜尋過濾參數
     */
    public function getLists($data = null)
    {
        $userSystemLists = $this->userSystemRepository->getLists($data);
        return $userSystemLists;
    }

    /**
     * 取的系統使用者資料
     * @param string $account 系統帳號
     */
    public function getDataByAccount($account)
    {
        $userSystem = $this->userSystemRepository->getData("account", $account);
        return $userSystem;
    }
}
