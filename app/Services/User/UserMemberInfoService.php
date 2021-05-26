<?php
namespace App\Services\User;

// 導入 user_member_infos 資料庫操作方法
use App\Repositories\User\UserMemberInfoRepository;
// 導入 user_members 資料庫操作方法
use App\Repositories\User\UserMemberRepository;
// 導入 point_orders 資料庫操作方法
use App\Repositories\Order\PointOrderRepository;
// 導入 logs 資料庫操作方法
use App\Repositories\LogRepository;
// 導入 共用方法
use App\Helpers\FuncHelper;

class UserMemberInfoService
{

    // UserMemberInfoRepository 指定變數
    protected $userMemberInfoRepository;
    // UserMemberRepository 指定變數
    protected $userMemberRepository;
    // PointOrderRepository 指定變數
    protected $pointOrderRepository;
    // LogRepository 指定變數
    protected $logRepository;
    // 導入共用方法 指定變數
    protected $funcHelper;

    /*
    * 將需要使用的Repository通過建構函式例項化
    */
    public function __construct(UserMemberInfoRepository $userMemberInfoRepository, PointOrderRepository $pointOrderRepository, UserMemberRepository $userMemberRepository, LogRepository $logRepository, FuncHelper $funcHelper)
    {
        $this->userMemberInfoRepository = $userMemberInfoRepository;
        $this->userMemberRepository = $userMemberRepository;
        $this->logRepository = $logRepository;
        $this->pointOrderRepository = $pointOrderRepository;
        $this->funcHelper = $funcHelper;
    }

    /**
     * 新增會員詳細資料
     * @param object $data 會員詳細資料
     */
    public function create($data)
    {
        $userMemberInfo = $this->userMemberInfoRepository->create($data);
        return $userMemberInfo;
    }
    /**
     * 更新會員詳細資料
     * @param object $data 會員詳細資料
     */
    public function update($data)
    {
        // 取得會員詳細資料
        $userMemberInfo = $this->userMemberInfoRepository->getData("id", $data["member_info_id"]);
        // 如果memberInfo 傳入 0 時 以及 取不到會員詳細資料時 代表 尚未創建 user_member_infos表資料 因此執行新增方法
        if ($userMemberInfo === null && $data["member_info_id"] == 0) {
            $userMemberInfo = $this->userMemberInfoRepository->create($data);
        }
        $userMemberInfo = $this->userMemberInfoRepository->update(["id" => $data["member_info_id"], "custom_id" => $data["custom_id"]]);
        return $userMemberInfo;
    }

    /**
     * 新增點數
     * @param data type Object 新增點數資料
     */
    public function addPoint($data)
    {
        // 取得會員詳細資料
        $userMemberInfo = $this->userMemberInfoRepository->getData("id", $data["member_info_id"]);
        // 將傳入點數數值整數話
        $data["point"] = (int)$data["point"];
        // 更新之前點數預設值
        $beforePoint = 0;
        // 更新之後點數預設值
        $afterPoint = 0;
        // 如果memberInfo 傳入 0 時 以及 取不到會員詳細資料時 代表 尚未創建 user_member_infos表資料 因此執行新增方法
        if ($userMemberInfo === null && $data["member_info_id"] == 0) {
            // 新增會員詳細資料
            $userMemberInfo = $this->userMemberInfoRepository->create(["member_id" => $data["memberId"], "point" => $data["point"]]);
        } else {
            // 取得更新前會員點數
            $beforePoint = $userMemberInfo->point;
            // 更新會員點數
            $this->userMemberInfoRepository->update(["id" => $data["member_info_id"], "point" => ($userMemberInfo->point += $data["point"])]);
        }
        // 取得更新後會員點數
        $afterPoint = $userMemberInfo->point;
        // 新增點數訂單
        $pointOrder = $this->pointOrderRepository->create([
            "before_point" => $beforePoint,
            "after_point" => $afterPoint,
            "point" => $data["point"],
            "member_id" => $data["memberId"],
            "operator_id" => $data["operatorId"],
            "user_id" => $data["userId"],
            "remarks" => $data["remarks"]
        ]);

        // 記錄參數
        $logData = [
            "user_id" => auth()->user()->user_id,
            "type_key" => "addPoint",
            "type_name" => "新增點數",
            "req_data" => json_encode($pointOrder),
            "res_data" => json_encode($pointOrder),
        ];
        // 新增記錄
        $this->logRepository->create($logData, $this->userMemberRepository->getData("member_id", $data["memberId"]));
        return $pointOrder;
    }
}
