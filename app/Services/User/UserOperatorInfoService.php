<?php
namespace App\Services\User;

// 導入 user_operator 資料庫操作方法
use App\Repositories\User\UserOperatorInfoRepository;
// 導入 user_operators 資料庫操作方法
use App\Repositories\User\UserOperatorRepository;
// 導入 users 資料庫操作方法
use App\Repositories\User\UserRepository;
// 導入 WhiteList 服務
use App\Services\WhiteListService;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserOperatorInfoService
{

    // UserOperatorInfoRepository Repository 指定變數
    protected $userOperatorInfoRepository;
    // UserOperatorRepository Repository 指定變數
    protected $userOperatorRepository;
    // UserRepository Repository 指定變數
    protected $userRepository;
    // WhiteListService Service 指定變數
    protected $whiteListService;
    // 導入共用方法
    protected $funcHelper;
    
    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(UserOperatorInfoRepository $userOperatorInfoRepository, UserOperatorRepository $userOperatorRepository, UserRepository $userRepository, WhiteListService $whiteListService, FuncHelper $funcHelper)
    {
        $this->userOperatorInfoRepository = $userOperatorInfoRepository;
        $this->userOperatorRepository = $userOperatorRepository;
        $this->userRepository = $userRepository;
        $this->whiteListService = $whiteListService;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增經營者詳細資料
     * @param object $data = 經營者資料
     * @param object $infoData = 經營者詳細資料
     */
    public function create($data, $infoData)
    {
        // 判斷帳號是否重複
        if ($this->userOperatorRepository->checkDuplicate("account", $data["account"])) {
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
        // 將關聯 id 存入 userOperatorInfo中
        $infoData["operator_id"] = $userOperator["operator_id"];
        // 將新增資料 傳入 userOperator repository 執行新增經營者詳細資料方法
        $userOperatorInfo = $this->userOperatorInfoRepository->create($infoData);
        return ["userOperator" => $userOperator, "userOperatorInfo" => $userOperatorInfo, "user" => $user];
    }
    /**
     * 更新經營者詳細資料
     * @param object $data = 經營者資料
     * @param object $infoData = 經營者詳細資料
     */
    public function update($data, $infoData, $logoFile = null)
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
        // 將更新資料 傳入 user repository 執行新增使用者方法
        $user = $this->userRepository->update($sendData);
        // 判斷 是否有傳入 logo
        if ($logoFile !== null) {
            $infoData["logo"] = $logoFile;
        }
        // 將新增資料 傳入 userOperator repository 執行新增經營者詳細資料方法
        $userOperatorInfo = $this->userOperatorInfoRepository->update($infoData);
        // 判斷是否有傳入白名單
        if (isset($infoData["lists"])) {
            // 更新ip白名單
            $whiteLists = $this->whiteListService->update($infoData, $infoData["user_id"]);
            return ["userOperator" => $userOperator, "userOperatorInfo" => $userOperatorInfo, "user" => $user, "whiteLists" => $whiteLists];
        }
        return ["userOperator" => $userOperator, "userOperatorInfo" => $userOperatorInfo, "user" => $user];
    }
}
