<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

// 導入 permission_defaults 表 model
use App\Models\Permission\PermissionDefault;
// 資料庫操作方法
use DB;

class PermissionDefaultSeeder extends Seeder
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
        PermissionDefault::truncate();
        // 啟用 foreign key 外鍵檢查
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $permissions = [
            [
                'key' => 'system',
                'custom_key' => '',
                'permission_rule' => 1,
                'str' => '系統管理',
                'is_menu' => 1,
                'is_option' => 0,
                'children' => [
                    [
                        'key' => 'system_permission_list',
                        "custom_key" => "",
                        "permission_rule" => 1,
                        "str" => "路由權限",
                        "is_menu" => 1,
                        "is_option" => 0,
                    ],
                    [
                        'key' => 'system_template_list',
                        "custom_key" => "",
                        "permission_rule" => 1,
                        "str" => "樣板列表",
                        "is_menu" => 1,
                        "is_option" => 0,
                    ],
                    [
                        "key" => "system_roles",
                        "custom_key" => "",
                        "permission_rule" => 1,
                        "str" => "群組列表",
                        "is_menu" => 1,
                        "is_option" => 0,
                    ],
                    [
                        "key" => "system_roles_update",
                        "custom_key" => "",
                        "permission_rule" => 1,
                        "str" => "群組權限設定",
                        "is_menu" => 0,
                        "is_option" => 0,
                    ],
                    [
                        "key" => "system_sms_list",
                        "custom_key" => "",
                        "permission_rule" => 1,
                        "str" => "簡訊商列表",
                        "is_menu" => 1,
                        "is_option" => 0,
                    ],
                ],
            ],
            [
                'key' => 'webControl',
                'custom_key' => '',
                'permission_rule' => 1,
                'str' => '網站管理',
                'is_menu' => 1,
                'is_option' => 0,
                "children" => [
                    [
                        'key' => 'webControl_category',
                        'custom_key' => '',
                        'permission_rule' => 1,
                        'str' => '分類列表',
                        'is_menu' => 1,
                        'is_option' => 0,
                    ],
                    [
                        'key' => 'webControl_storeService',
                        'custom_key' => '',
                        'permission_rule' => 1,
                        'str' => '服務列表',
                        'is_menu' => 1,
                        'is_option' => 0,
                    ],
                    [
                        'key' => 'webControl_tag',
                        'custom_key' => '',
                        'permission_rule' => 1,
                        'str' => '標籤列表',
                        'is_menu' => 1,
                        'is_option' => 0,
                    ]
                ]
            ],
            [
                'key' => 'orderControl',
                'custom_key' => '',
                'permission_rule' => 1,
                'str' => '訂單管理',
                'is_menu' => 1,
                'is_option' => 0,
                "children" => [
                    [
                        'key' => 'orderControl_point_order_list',
                        'custom_key' => '',
                        'permission_rule' => 3,
                        'str' => '點數訂單',
                        'is_menu' => 1,
                        'is_option' => 0,
                    ],
                ]
            ],
            [
                'key' => 'accountControl',
                'custom_key' => '',
                'permission_rule' => 1,
                'str' => '帳號管理',
                'is_menu' => 1,
                'is_option' => 0,
                "children" => [
                    [
                        'key' => 'accountControl_system',
                        'custom_key' => '',
                        'permission_rule' => 1,
                        'str' => '系統使用者列表',
                        'is_menu' => 1,
                        'is_option' => 0,
                    ],
                    [
                        'key' => 'accountControl_operator',
                        'custom_key' => '',
                        'permission_rule' => 1,
                        'str' => '經營者列表',
                        'is_menu' => 1,
                        'is_option' => 0,
                        "have_options" => '[
                            {
                                "key": "accountControl_designer",
                                "name": "服務提供者"
                            },
                            {
                                "key": "accountControl_assistant",
                                "name": "助理"
                            },
                            {
                                "key": "accountControl_sub",
                                "name": "子帳號"
                            },
                            {
                                "key": "accountControl_operator_permissionSet",
                                "name": "個人權限"
                            },
                            {
                                "key": "webControl_storeService",
                                "name": "服務"
                            },
                            {
                                "key": "accountControl_member",
                                "name": "會員"
                            },
                            {
                                "key": "otherControl_salary",
                                "name": "薪水"
                            }
                        ]'
                    ],
                    [
                        'key' => 'accountControl_designer',
                        'custom_key' => '',
                        'permission_rule' => 1,
                        'str' => '服務提供者列表',
                        'is_menu' => 1,
                        'is_option' => 0,
                        "have_options" => '[
                            {
                                "key": "accountControl_designer_panel",
                                "name": "服務提供者控制台"
                            },
                            {
                                "key": "accountControl_operator_permissionSet",
                                "name": "權限"
                            }
                        ]'
                    ],
                    [
                        'key' => 'accountControl_designer_panel',
                        'custom_key' => '',
                        'permission_rule' => 1,
                        'str' => '服務提供者控制台',
                        'is_menu' => 0,
                        'is_option' => 1,
                    ],
                    [
                        'key' => 'accountControl_assistant',
                        'custom_key' => '',
                        'permission_rule' => 1,
                        'str' => '助理列表',
                        'is_menu' => 1,
                        'is_option' => 0,
                        "have_options" => '[
                            {
                                "key": "accountControl_operator_permissionSet",
                                "name": "權限"
                            }
                        ]'
                    ],
                    [
                        'key' => 'accountControl_sub',
                        'custom_key' => '',
                        'permission_rule' => 1,
                        'str' => '子帳號列表',
                        'is_menu' => 1,
                        'is_option' => 0,
                        "have_options" => '[
                            {
                                "key": "accountControl_operator_permissionSet",
                                "name": "權限"
                            }
                        ]'
                    ],
                    [
                        'key' => 'accountControl_operator_permissionSet',
                        'custom_key' => '',
                        'permission_rule' => 1,
                        'str' => '經營者個人權限設定',
                        'is_menu' => 0,
                        'is_option' => 1,
                    ],
                    [
                        'key' => 'accountControl_member',
                        'custom_key' => '',
                        'permission_rule' => 1,
                        'str' => '會員列表',
                        'is_menu' => 1,
                        'is_option' => 0,
                        "have_options" => '[
                            {
                                "key": "member_addPoint",
                                "name": "新增點數"
                            }
                        ]'
                    ]
                ]
            ],
            [
                'key' => 'otherControl',
                'custom_key' => '',
                'permission_rule' => 1,
                'str' => '其它管理',
                'is_menu' => 1,
                'is_option' => 0,
                "children" => [
                    [
                        'key' => 'otherControl_chat',
                        'custom_key' => '',
                        'permission_rule' => 1,
                        'str' => '聊天室',
                        'is_menu' => 1,
                        'is_option' => 0,
                    ],
                    [
                        'key' => 'otherControl_smsSend',
                        'custom_key' => '',
                        'permission_rule' => 1,
                        'str' => '簡訊發送',
                        'is_menu' => 1,
                        'is_option' => 0,
                    ],
                ]
            ],
        ];
        foreach ($permissions as $group) {
            PermissionDefault::create($group);
        }
        PermissionDefault::reguard();
    }
}
