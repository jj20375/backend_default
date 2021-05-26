<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// 導入 permission_groups 表 model
use App\Models\Permission\PermissionGroup;
// 導入 permission_defaults 表 model
use App\Models\Permission\PermissionDefault;
// 資料庫操作方法
use DB;

class UserPermissionGroupSeeder extends Seeder
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
        PermissionGroup::truncate();
        // 啟用 foreign key 外鍵檢查
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $permissionGroups = [
            [
                "group_id" => 1,
                "permission_id" => 1,
                "key" => "system",
                "per_create" => 1,
                "per_read" => 1,
                "per_update" => 1,
                "per_delete" => 1
            ],
            [
                "group_id" => 1,
                "permission_id" => 2,
                "key" => "system_permission_list",
                "per_create" => 1,
                "per_read" => 1,
                "per_update" => 1,
                "per_delete" => 1
            ],
            [
                "group_id" => 1,
                "permission_id" => 3,
                "key" => "system_template_list",
                "per_create" => 1,
                "per_read" => 1,
                "per_update" => 1,
                "per_delete" => 1
            ],
            [
                "group_id" => 1,
                "permission_id" => 4,
                "key" => "system_roles",
                "per_create" => 1,
                "per_read" => 1,
                "per_update" => 1,
                "per_delete" => 1
            ],
            [
                "group_id" => 1,
                "permission_id" => 5,
                "key" => "system_roles_update",
                "per_create" => 1,
                "per_read" => 1,
                "per_update" => 1,
                "per_delete" => 1
            ],
        ];
        foreach ($permissionGroups as $item) {
            $ormData = PermissionDefault::where("key", $item["key"])->first();
            $permissionGroup = new PermissionGroup;
            $permissionGroup->fill($item);
            $permissionGroup->save();
        }
        PermissionGroup::reguard();
    }
}
