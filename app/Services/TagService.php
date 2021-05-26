<?php
namespace App\Services;

// 導入 tags 資料庫操作方法
use App\Repositories\TagRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class TagService
{

    // TagRepository 指定變數
    protected $tagRepository;
    // 導入共用方法 指定變數
    protected $funcHelper;
    
    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(TagRepository $tagRepository, FuncHelper $funcHelper)
    {
        $this->tagRepository = $tagRepository;
        $this->funcHelper = $funcHelper;
    }

    /**
    * 新增標籤
    * @param object $data 新增逼遷資料
    */
    public function create($data)
    {
        // 判斷帳號是否重複
        if ($this->tagRepository->checkDuplicate("key", $data["key"])) {
            return $this->funcHelper->errorBack("key重複", 500);
        }
        // 判斷是否為系統身份
        if (auth()->user()->group->group_code === "SYSTEM") {
            // 取得系統身份資料
            $ableOrm = auth()->user()->userable;
        } else {
            // 取得管理者身份資料
            $ableOrm = auth()->user()->userable->userOperator;
        }
        $tag = $this->tagRepository->create($data, $ableOrm);
        return $tag;
    }
    /**
     * 更新標籤
     * @param object $data 更新標籤資料
     */
    public function update($data)
    {
        $tag = $this->tagRepository->update($data);
        // 判斷是否更新成功
        if ($tag === false) {
            return $this->funcHelper->errorBack("更新失敗", 500);
        }
        return $tag;
    }
    /**
     * 取得列表
     * @param object $data 列表搜尋過濾資料
     */
    public function getLists($data = null)
    {
        $tagLists = $this->tagRepository->getLists($data);
        return $tagLists;
    }
    /**
     * 取得可用列表
     * @param string $tagType 群組代碼
     * @param string $operatorId 管理者id
     */
    public function getSelectLists($tagType, $operatorId)
    {
        $tagLists = $this->tagRepository->getSelectLists($tagType, $operatorId);
        return $tagLists;
    }
    /**
     * 取得單一資料
     * @param string $tagId 標籤id
     */
    public function getData($tagId)
    {
        $tag = $this->tagRepository->getData("tag_id", $tagId);
        $tagType = config("app.tagType");
        // 選中的群組身份
        $selectPermissionRule = collect([]);
        // 將可選擇的群組身份 執行回圈匹配
        foreach ($tagType as $key => $value) {
            // 判斷方式採用 數位權限 二進制判斷 如果匹配失敗時 值會等於 0
            if (($tag->permission_rule & $value) !== 0) {
                $selectPermissionRule->push($value);
            }
        }
        return ["data" => $tag, "selectPermissionRule" => $selectPermissionRule];
    }
}
