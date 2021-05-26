<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// 導入 groups 表 model
use App\Models\Group;
// 導入 user_systems 表 model
use App\Models\User\UserSystem;
// 資料庫操作方法
use DB;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 禁用 foreign key 外鍵檢查
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // 刪除所有群組資料
        Group::truncate();
        // 啟用 foreign key 外鍵檢查
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $groups = [
            [
                "group_name" => "系統",
                "group_code" => "SYSTEM",
                "permission_rule" => 1,
            ],
            [
                "group_name" => "管理",
                "group_code" => "OPERATOR",
                "permission_rule" => 2,
            ],
            [
                "group_name" => "服務提供者",
                "group_code" => "DESIGNER",
                "permission_rule" => 4,
            ],
            [
                "group_name" => "助理",
                "group_code" => "ASSISTANT",
                "permission_rule" => 8,
            ],
        ];
        foreach ($groups as $item) {
            $group = new Group;
            $saveData = [
                "group_name" => $item["group_name"],
                "group_code" => $item["group_code"],
                "permission_rule" => $item["permission_rule"],
            ];
            $groupableOrm = UserSystem::find(1);
            $group->groupable()->associate($groupableOrm);
            $group->fill($saveData);
            $group->save();
        }
    }

    
}
