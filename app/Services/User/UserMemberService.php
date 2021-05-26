<?php
namespace App\Services\User;

// 導入 users 資料庫操作方法
use App\Repositories\User\UserRepository;
// 導入 user_members 資料庫操作方法
use App\Repositories\User\UserMemberRepository;
// 導入 user_member_infos 資料庫操作方法
use App\Repositories\User\UserMemberInfoRepository;
// 導入 WhiteList 服務
use App\Services\WhiteListService;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserMemberService
{
    // UserRepository Repository 指定變數
    protected $userRepository;
    // UserMemberRepository 指定變數
    protected $userMemberRepository;
    // UserMemberInfoRepository 指定變數
    protected $userMemberInfoRepository;
    // WhiteListService Service 指定變數
    protected $whiteListService;
    // 導入共用方法 指定變數
    protected $funcHelper;

    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(UserRepository $userRepository, UserMemberRepository $userMemberRepository, UserMemberInfoRepository $userMemberInfoReposiory, WhiteListService $whiteListService, FuncHelper $funcHelper)
    {
        $this->userRepository = $userRepository;
        $this->userMemberRepository = $userMemberRepository;
        $this->userMemberInfoRepository = $userMemberInfoReposiory;
        $this->whiteListService = $whiteListService;
        $this->funcHelper = $funcHelper;
    }
    /**
     * 新增會員
     * @param object $data 新增會員資料
     * @param infoData { type Object(物件) } 會員詳細資料
     */
    public function create($data, $infoData)
    {
        // 判斷帳號是否重複
        if ($this->userRepository->checkDuplicate("account", $data["account"])) {
            return ["success" => false, "message" => "帳號重複"];
        }
        // 表待資料
        $sendData = $data;
        // 密碼加密
        $sendData["password"] = bcrypt($data["password"]);
        // 創建ip
        $sendData["create_ip"] = request()->ip();
        // 新增會員資料
        $userMember = $this->userMemberRepository->create($sendData);
        // 將新增資料 傳入 user repository 執行新增使用者方法
        $user = $this->userRepository->create($sendData, $userMember);
        // 會員詳細資料
        $infoData["member_id"] = $userMember["member_id"];
        // 新增會員詳細資料方法
        $userMemberInfo = $this->userMemberInfoRepository->create($infoData);
        // 如果傳入的operator_id 本身登入管理者id 時 且登入身份非系統身份時 創建會員會失敗
        if (!$userMember) {
            $this->funcHelper->errorBack("新增失敗", 500);
        }
        return ["userMember" => $userMember, "userMemberInfo" => $userMemberInfo, "user" => $user];
    }
    /**
     * 更新會員
     * @param object $data 更新會員資料
     * @param infoData { type Object(物件) } 會員詳細資料
     */
    public function update($data, $infoData)
    {
        // 表單資料
        $sendData = $data;
        // 判斷是否有需要更新密碼 如有傳送 password key 代表需重新更新密碼 因此需將密碼加密
        if (!empty($data["password"])) {
            // 密碼加密
            $sendData["password"] = bcrypt($data["password"]);
        }
        // 將更新資料 傳入 userMember repository 執行更新經營者方法
        $userMember = $this->userMemberRepository->update($sendData);
        // 將更新資料 傳入 user repository 執行更新使用者方法
        $user = $this->userRepository->update($sendData);
        // 會員詳細資料
        $userMemberInfo = $this->userMemberInfoRepository->getData("id", $infoData["member_info_id"]);
        // 判斷使否有會員詳細資料
        if ($userMemberInfo === null && $infoData["member_info_id"] === 0) {
            $infoData["member_id"] = $userMember["member_id"];
            // 新增會員詳細資料
            $userMemberInfo = $this->userMemberInfoRepository->create($infoData);
        }
        // 新增會員詳細資料id
        $infoData["id"] = $userMemberInfo["id"];
        // 更新會員詳細資料方法
        $userMemberInfo = $this->userMemberInfoRepository->update($infoData);
        // 回傳資料
        $responseData = ["userMember" => $userMember, "memberInfo" => $userMemberInfo, "user" => $user];
        // 判斷是否有傳入白名單
        if (isset($data["lists"])) {
            // 更新ip白名單
            $whiteLists = $this->whiteListService->update(["lists" => $data["lists"], "ip_id" => $data["ip_id"]], $data["user_id"]);
            $responseData["whiteLists"] = $whiteLists;
        }
        return $responseData;
    }
    /**
     * 取得列表
     * @param object $data 列表搜尋過濾資料
     */
    public function getLists($data = null)
    {
        $userMemberLists = $this->userMemberRepository->getLists($data);
        return $userMemberLists;
    }
    /**
     * 取得指定id列表資料
     * @param string $operatorId user_operators表id
     * @param object $data 分頁搜尋過濾資料
     */
    public function getListsById($operatorId, $data)
    {
        $userMemberLists = $this->userMemberRepository->getListsById($operatorId, $data);
        return $userMemberLists;
    }
    /**
     * 取得可用列表
     * @param string $operatorId 管理者id
     */
    public function getSelectLists($operatorId)
    {
        $userMemberLists = $this->userMemberRepository->getSelectLists($operatorId);
        return $userMemberLists;
    }
    /**
     * 取得單一資料
     * @param string $account 會員帳號
     */
    public function getDataByAccount($account)
    {
        $userMember = $this->userMemberRepository->getData("account", $account);
        return $userMember;
    }
    /**
     * 使用id取得單一資料
     * @param string $memberId 會員id
     * @param isGetData { type Boolean(布林值) } 判斷是否只回傳資料不回傳http code
     */
    public function getDataById($memberId, $isGetData = false)
    {
        $userMember = $this->userMemberRepository->getData("member_id", $memberId);
        if ($isGetData) {
            return $userMember;
        }
        return $userMember;
    }

    /**
     * 取得簡訊發送名單
     * @param params { type Object(物件) } 搜尋參數
     */
    public function getSmsSendLists($params)
    {
        $lists = $this->userMemberRepository->getSmsSendLists($params);
        return $lists;
    }
    /**
     * 模糊比對搜尋使用
     * @param params { type String(字串) }搜尋參數
     */
    public function remoteLists($params)
    {
        $lists = $this->userMemberRepository->remoteLists($params);
        return $lists;
    }
}
