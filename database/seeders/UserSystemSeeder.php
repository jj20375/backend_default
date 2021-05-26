<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// 導入 users 表 model
use App\Models\User\User;
// 導入 user_systems 表 model
use App\Models\User\UserSystem;
// 資料庫操作方法
use DB;

class UserSystemSeeder extends Seeder
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
        UserSystem::truncate();
        User::truncate();
        // 啟用 foreign key 外鍵檢查
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $users = [
            [
                "name" => "系統1",
                "status" => 5,
                "group_id" => 1,
                "account" => "jj20375",
                "password" => "aa2717880",
                "remarks" => "系統測試員1",
            ],
            [
                "name" => "系統2",
                "status" => 5,
                "group_id" => 1,
                "account" => "james30414",
                "password" => "aa2717880",
                "remarks" => "系統測試員2",
            ],
        ];

        foreach ($users as $user) {
            //$userSystem = userSystem::create($user);
            $userSystem = new UserSystem;
            $saveSystem = [
                "name" => $user['name'],
                "status" => $user['status'],
                "account" => $user['account'],
                // "remarks" => $user['remarks'],
            ];
            $userSystem->fill($saveSystem);
            $userSystem->save();
            $this->addUser($user, $userSystem);
        }
    }

    public function addUser($data, $userableOrm)
    {
        $userDb = new User;
        $userDb->userable()->associate($userableOrm);
        $saveData = [
            "account" => $data["account"],
            "password" => bcrypt($data["password"]),
            "create_ip" => request()->ip(),
            "remarks" => $data["remarks"],
            // "group_id" => 1,
            // "perId" => $data["perId"],
            // "groupId" => $data["groupId"],
        ];
        $userDb->fill($saveData);
        $userDb->save();
    }
}
