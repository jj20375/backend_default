<?php
namespace App\Services;

// 導入 groups 資料庫操作方法
use App\Repositories\GroupRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class GroupService
{
    // Group Repository 指定變數
    protected $groupRepository;
    // 導入共用方法 指定變數
    protected $funcHelper;


    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(GroupRepository $groupRepository, FuncHelper $funcHelper)
    {
        $this->groupRepository = $groupRepository;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增群組
     * @param object $data 新增群組資料
     */
    public function create($data)
    {
        // 判斷如果非系統身份時 不可建立系統使用者群組
        if (auth()->user()->group->group_code !== "SYSTEM" && $data["group_code"] === "SYSTEM") {
            return $this->funcHelper->errorBack("新增失敗", 500);
        }
        // 判斷登入者是否為系統身份
        if (auth()->user()->group->group_code === "SYSTEM") {
            // 取得系統身份資料
            $ableOrm = auth()->user()->userable;
        } else {
            // 取得管理者身份資料
            $ableOrm = auth()->user()->userable->userOperator;
        }
        // 更新群組
        $group = $this->groupRepository->create($data, $ableOrm);
        return $group;
    }
    /**
     * 更新群組
     * @param object $data 更新群組資料
     */
    public function update($data)
    {
        // 判斷如果非系統身份時 不可更新群組代碼
        if (auth()->user()->group->group_code !== "SYSTEM" ) {
            unset($data["group_code"]);
        }
        // 更新群組
        $group = $this->groupRepository->update($data);
        // 判斷是否更新成功
        if ($group === false) {
            return $this->funcHelper->errorBack("更新失敗", 500);
        }
        return $group;
    }
    /**
    * 取得列表
    * @param object $data 分頁傳送過濾資料
    */
    public function getLists($data = null)
    {
        $groupLists = $this->groupRepository->getLists($data);
        return $groupLists;
    }

    /**
     * 取得可用列表
     * @param string $groupCode 群組代碼
     * @param string $operatorId 管理者id
     * @param string $isSub 是否為子帳號
     */
    public function getSelectLists($groupCode, $operatorId, $isSub = false)
    {
        $groupLists = $this->groupRepository->getSelectLists($groupCode, $operatorId, $isSub);
        return $groupLists;
    }

    /**
     * 取得群組資料
     * @param string $groupId 群組id
     */
    public function getData($groupId)
    {
        $group = $this->groupRepository->getData($groupId);
        return $group;
    }
}
