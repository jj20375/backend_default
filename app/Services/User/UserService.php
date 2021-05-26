<?php
namespace App\Services\User;

// 導入 users 資料庫操作方法
use App\Repositories\User\UserRepository;
// 導入 user_designers 資料庫操作方法
use App\Repositories\User\UserOperatorRepository;
// 導入 user_designers 資料庫操作方法
use App\Repositories\User\UserDesignerRepository;
// 導入 user_assistants 資料庫操作方法
use App\Repositories\User\UserAssistantRepository;
// 導入 user_subs 資料庫操作方法
use App\Repositories\User\UserSubRepository;
// 導入 user_systems 資料庫操作方法
use App\Repositories\User\UserSystemRepository;
// 導入 PermissionGroupService 方法
use App\Services\Permission\PermissionGroupService;
// 導入 PermissionUserService 方法
use App\Services\Permission\PermissionUserService;
// 導入 共用方法
use App\Helpers\FuncHelper;
// 身份驗證套件
use Illuminate\Support\Facades\Auth;

class UserService
{

    // UserRepository Repository 指定變數
    protected $userRepository;
    // UserOperatorRepository Repository 指定變數
    protected $userOperatorRepository;
    // UserDesignerRepository Repository 指定變數
    protected $userDesignerRepository;
    // UserAssistantRepository Repository 指定變數
    protected $userAssistantRepository;
    // UserSubRepository Repository 指定變數
    protected $userSubRepository;
    // UserSystemRepository Repository 指定變數
    protected $userSystemRepository;
    // 導入共用方法 指定變數
    protected $funcHelper;
    // PermissionGroupService 指定變數
    protected $permissionGroupService;
    // PermissionUserService 指定變數
    protected $permissionUserService;


    /*
    *  將需要使用的Repository通過建構函式例項化
    */
    public function __construct(
        UserRepository $userRepository,
        UserOperatorRepository $userOperatorRepository,
        UserDesignerRepository $userDesignerRepository,
        UserAssistantRepository $userAssistantRepository,
        UserSubRepository $userSubRepository,
        UserSystemRepository $userSystemRepository,
        PermissionGroupService $permissionGroupService,
        PermissionUserService $permissionUserService,
        FuncHelper $funcHelper
    ) {
        $this->userRepository = $userRepository;
        $this->userOperatorRepository = $userOperatorRepository;
        $this->userDesignerRepository = $userDesignerRepository;
        $this->userAssistantRepository = $userAssistantRepository;
        $this->userSubRepository = $userSubRepository;
        $this->userSystemRepository = $userSystemRepository;
        $this->funcHelper = $funcHelper;
        $this->permissionGroupService = $permissionGroupService;
        $this->permissionUserService = $permissionUserService;
    }

    public function update($data, $infoData)
    {
        // 表單資料
        $sendData = $data;
        // 判斷是否有輸入更新密碼
        if (!empty($sendData["password"])) {
            // 密碼加密
            $sendData["password"] = bcrypt($data["password"]);
        }
        // 更新使用者帳密
        $user = $this->userRepository->update($sendData);
        // 取得使用者群組 (用來判斷身份使用)
        $userGroup = $this->userRepository->getData("user_id", $user->user_id)->group;
        // 判斷使用者是否為子帳號
        $checkGroupIsSub = $userGroup->is_sub;
        switch (true) {
            // 系統使用者更新
            case ($userGroup->group_code === "SYSTEM"):
                $infoData["system_id"] = $user->userable->system_id;
                $userInfo = $this->userSystemRepository->update($infoData);
                return ["user" => $user, "userInfo" => $userInfo];
                break;
            // 管理者更新
            case ($userGroup->group_code === "OPERATOR" && $checkGroupIsSub === 0):
                $infoData["operator_id"] = $user->userable->operator_id;
                $userInfo = $this->userOperatorRepository->update($infoData);
                return ["user" => $user, "userInfo" => $userInfo];
                break;
            // 子帳號更新
            case ($userGroup->group_code === "OPERATOR" && $checkGroupIsSub === 1):
                $infoData["sub_id"] = $user->userable->sub_id;
                $userInfo = $this->userSubRepository->update($infoData);
                return ["user" => $user, "userInfo" => $userInfo];
                break;
            // 服務提供者更新
            case ($userGroup->group_code === "DESIGNER"):
                $infoData["designer_id"] = $user->userable->designer_id;
                $userInfo = $this->userDesignerRepository->update($infoData);
                return ["user" => $user, "userInfo" => $userInfo];
                break;
            // 助理更新
            case ($userGroup->group_code === "ASSISTANT"):
                $infoData["assistant_id"] = $user->userable->assistant_id;
                $userInfo = $this->userAssistantRepository->update($infoData);
                return ["user" => $user, "userInfo" => $userInfo];
                break;
        }
    }
    
    /**
     * 登入方法
     * @param $account 登入帳號
     * @param $password 登入密碼
     */
    public function login($account, $password)
    {
        // 取得登入使用者資料
        $userOrm = $this->userRepository->getData("account", $account);
        // 判斷是否有此帳號
        if ($userOrm === null) {
            return ["msg" => "登入失敗", "success" => false];
        }
        // 判斷此使用者是否被封鎖 5 = 啟用狀態
        if ($userOrm->userable->status !== 5) {
            return ["msg" => "無法登入", "success" => false];
        }
        //有存在登入token且未過期，做註銷原token的動作
        if ($userOrm['token'] && $userOrm['token_time'] >= time()) {
            $guard = $this->funcHelper->getGuard();
            Auth::guard($guard)->setToken($userOrm['token'])->invalidate();
        }
        // 執行登入方法
        $userLogin = $this->userRepository->login($account, $password);
        // 登入失敗 帳密錯誤回傳值
        if (!$userLogin["success"]) {
            // 登入失敗回傳值
            return ["msg" => "帳密錯誤", "success" => false];
        }
        // 登入成功回傳值
        return $userLogin;
    }

    /**
     * 取得使用者資料
     */
    public function getData()
    {
        $user = auth()->user();
        $userData = $this->userRepository->getData("account", $user->account);
        return $userData;
    }
    /**
     * 取得使用者資料
     */
    public function getDataByAccount($account)
    {
        $userData = $this->userRepository->getData("account", $account);
        return $userData;
    }
    /**
     * 判斷是否帳號重複
     */
    public function checkDuplicateAccount($account)
    {
        $isDuplicate = $this->userRepository->checkDuplicate("account", $account);
        return $isDuplicate;
    }

    /**
     * 重取token
     */
    public function refreshToken()
    {
        $token = $this->userRepository->refreshToken();
        return $token;
    }
    /**
     * 取得登入者可使用的選單
     */
    public function getMenu()
    {
        // 取的個人權限是否啟用設定值
        $openUserPermission = auth()->user()->open_user_permission;
        // 判斷是否有啟用個人權限
        if ($openUserPermission === 0) {
            // 取的群組權限選單
            $permissionMenu = $this->permissionGroupService->getMenu();
        } else {
            // 判斷有無個人權限資料 如果沒有一樣執行 群組權限
            if (empty(auth()->user()->permissionUser->toArray())) {
                $permissionMenu = $this->permissionGroupService->getMenu();
            } else {
                // 取的個人權限選單
                $permissionMenu = $this->permissionUserService->getMenu();
            }
        }
        return $permissionMenu;
    }

    /**
     * 取得伺服器時間
     */
    public function getServerTime()
    {
        $serverTime = $this->userRepository->getServerTime();
        return $serverTime;
    }
}
