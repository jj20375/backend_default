<?php
namespace App\Services\User;

// 導入 user_systems 資料庫操作方法
use App\Repositories\User\UserSubRepository;
// 導入 users 資料庫操作方法
use App\Repositories\User\UserRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserSubService
{

    // UserSubRepository Repository 指定變數
    protected $userSubRepository;
    // UserRepository Repository 指定變數
    protected $userRepository;
    // 導入共用方法
    protected $funcHelper;
    
    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(UserSubRepository $userSubRepository, UserRepository $userRepository, FuncHelper $funcHelper)
    {
        $this->userRepository = $userRepository;
        $this->userSubRepository = $userSubRepository;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增子帳號
     * @param object $data 新增資料
     * @param string $userId 使用者id
     */
    public function create($data, $userId)
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
        // 將新增資料 傳入 userSub repository 執行新增子帳號方法
        $userSub = $this->userSubRepository->create($sendData, $userId);
        // 將新增資料 傳入 user repository 執行新增使用者方法
        $user = $this->userRepository->create($sendData, $userSub);
        return ["userSub" => $userSub, "user" => $user];
    }
    /**
     * 更新子帳號
     * @param object $data 更新資料
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
        // 將新增資料 傳入 userSub repository 執行更新子帳號方法
        $userSub = $this->userSubRepository->update($sendData);
        // 將新增資料 傳入 user repository 執行更新使用者方法
        $user = $this->userRepository->update($sendData);
        return ["userSub" => $userSub, "user" => $user];
    }
    /**
     * 取得列表資料
     * @param object $data 分頁搜尋過濾資料
     */
    public function getLists($data)
    {
        $userSubLists = $this->userSubRepository->getLists($data);
        return $userSubLists;
    }
    /**
     * 取得指定id列表資料
     * @param string $userId users表id
     * @param object $data 分頁搜尋過濾資料
     */
    public function getListsById($userId, $data)
    {
        $userSubLists = $this->userSubRepository->getListsById($userId, $data);
        return $userSubLists;
    }
    /**
     * 取得可選擇列表
     * @param string $operatorId 管理者id
     */
    public function getSelectLists($operatorId)
    {
        $userSubLists = $this->userSubRepository->getSelectLists($operatorId);
        return $userSubLists;
    }
    /**
     * 使用帳號取得單一資料
     * @param string $account 子帳號設定帳號
     */
    public function getDataByAccount($account)
    {
        $userSub = $this->userSubRepository->getData("account", $account);
        return $userSub;
    }
}
