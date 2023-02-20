<?php

namespace App\Libraries;

class AdminMenu
{
    public static function menu_list()
    {
        return [
            CUSTOM_ADMIN_URI => [
                'role' => [],
                'name' => 'Tổng quan',
                'icon' => 'fa fa-home',
                'arr' => []
            ],
            'admin/posts' => [
                // quyền truy cập của loại tài khoản
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::POST),
                'icon' => 'fa fa-product-hunt',
                'arr' => [
                    'admin/posts/add' => [
                        'name' => PostType::typeList(PostType::POST),
                        'icon' => 'fa fa-plus',
                    ],
                    'admin/terms' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::POSTS, true),
                        'icon' => 'fa fa-cubes',
                    ],
                    'admin/postoptions' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::OPTIONS, true),
                        'icon' => 'fa fa-filter',
                    ],
                    'admin/tags' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::TAGS, true),
                        'icon' => 'fa fa-tag',
                    ],
                ]
            ],
            'admin/orders' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => 'Đơn hàng',
                'icon' => 'fa fa-shopping-bag',
                'arr' => []
            ],
            'admin/adss' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::ADS),
                'icon' => 'fa fa-picture-o',
                'arr' => [
                    'admin/adss/add' => [
                        'name' => PostType::typeList(PostType::ADS),
                        'icon' => 'fa fa-plus',
                    ],
                    'admin/adsoptions' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::ADS, true),
                        'icon' => 'fa fa-cubes',
                    ],
                ]
            ],
            'admin/blogs' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::BLOG),
                'icon' => 'fa fa-newspaper-o',
                'arr' => [
                    'admin/blogs/add' => [
                        'name' => PostType::typeList(PostType::BLOG),
                        'icon' => 'fa fa-plus',
                    ],
                    'admin/blogcategory' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::BLOGS, true),
                        'icon' => 'fa fa-cubes',
                    ],
                    'admin/blogtags' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::BLOG_TAGS, true),
                        'icon' => 'fa fa-tag',
                    ],
                ]
            ],
            'admin/pages' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::PAGE),
                'icon' => 'fa fa-file',
                'arr' => [
                    'admin/pages/add' => [
                        'name' => PostType::typeList(PostType::PAGE),
                        'icon' => 'fa fa-plus',
                    ],
                ]
            ],
            'admin/uploads' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::MEDIA),
                'icon' => 'fa fa-camera',
                'arr' => [
                    'admin/uploads/optimize' => [
                        'name' => 'Optimize image',
                        'icon' => 'fa fa-file-archive-o',
                    ],
                ]
            ],
            'admin/contacts' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => CommentType::typeList(CommentType::CONTACT),
                'icon' => 'fa fa-envelope-o',
                'arr' => []
            ],
            'admin/comments' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => CommentType::typeList(CommentType::COMMENT),
                'icon' => 'fa fa-comment-o',
                'arr' => []
            ],
            'admin/menus' => [
                'role' => [
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::MENU),
                'icon' => 'fa fa-bars',
                'arr' => [
                    'admin/htmlmenus' => [
                        'name' => PostType::typeList(PostType::HTML_MENU),
                        'icon' => 'fa fa-code',
                    ],
                ]
            ],
            'admin/users' => [
                'role' => [
                    UsersType::MOD,
                ],
                'name' => UsersType::ALL,
                'icon' => 'fa fa-users',
                'arr' => [
                    'admin/guests' => [
                        'name' => UsersType::typeList(UsersType::GUEST),
                        'icon' => 'fa fa-question-circle-o',
                    ],
                    'admin/members' => [
                        'name' => UsersType::typeList(UsersType::MEMBER),
                        'icon' => 'fa fa-user',
                    ],
                    'admin/authors' => [
                        'name' => UsersType::typeList(UsersType::AUTHOR),
                        'icon' => 'fa fa-magic',
                    ],
                    'admin/mods' => [
                        'name' => UsersType::typeList(UsersType::MOD),
                        'icon' => 'fa fa-modx',
                    ],
                    'admin/users?member_type=' . UsersType::ADMIN => [
                        'role' => [
                            UsersType::ADMIN,
                        ],
                        'name' => UsersType::typeList(UsersType::ADMIN),
                        'icon' => 'fa fa-diamond',
                    ],
                ]
            ],
            'admin/configs' => [
                'name' => 'Cài đặt chung',
                'icon' => 'fa fa-cogs',
                'arr' => [
                    'admin/displays' => [
                        'name' => ConfigType::typeList(ConfigType::DISPLAY),
                        'icon' => 'fa fa-desktop',
                    ],
                    'admin/socials' => [
                        'name' => ConfigType::typeList(ConfigType::SOCIAL),
                        'icon' => 'fa fa-facebook',
                    ],
                    'admin/configs?config_type=' . ConfigType::CATEGORY => [
                        'name' => ConfigType::typeList(ConfigType::CATEGORY),
                        'icon' => 'fa fa-cog',
                    ],
                    'admin/configs?config_type=' . ConfigType::POST => [
                        'name' => ConfigType::typeList(ConfigType::POST),
                        'icon' => 'fa fa-product-hunt',
                    ],
                    'admin/configs?config_type=' . ConfigType::BLOGS => [
                        'name' => ConfigType::typeList(ConfigType::BLOGS),
                        'icon' => 'fa fa-cog',
                    ],
                    'admin/configs?config_type=' . ConfigType::BLOG => [
                        'name' => ConfigType::typeList(ConfigType::BLOG),
                        'icon' => 'fa fa-newspaper-o',
                    ],
                    'admin/smtps' => [
                        'name' => ConfigType::typeList(ConfigType::SMTP),
                        'icon' => 'fa fa-envelope',
                    ],
                    'admin/checkouts' => [
                        'name' => ConfigType::typeList(ConfigType::CHECKOUT),
                        'icon' => 'fa fa-dollar',
                    ],
                    'admin/nummons' => [
                        'name' => ConfigType::typeList(ConfigType::NUM_MON),
                        'icon' => 'fa fa-sort-numeric-asc',
                    ],
                    'admin/checkboxs' => [
                        'name' => ConfigType::typeList(ConfigType::CHECKBOX),
                        'icon' => 'fa fa-check-square-o',
                    ],
                    'admin/translates' => [
                        'name' => ConfigType::typeList(ConfigType::TRANS),
                        'icon' => 'fa fa-globe',
                    ],
                ]
            ],
            'admin/dev' => [
                'name' => 'Kỹ thuật',
                'icon' => 'fa fa-bug',
                'arr' => [
                    'admin/dashboard/cleanup_cache' => [
                        'name' => 'Dọn dẹp website',
                        'icon' => 'fa fa-magic',
                    ],
                    'admin/optimize' => [
                        'name' => 'Optimize code',
                        'icon' => 'fa fa-code',
                    ],
                    'admin/dashboard/update_code' => [
                        'name' => 'Update code',
                        'icon' => 'fa fa-upload',
                    ],
                    'admin/dev/php_info' => [
                        'name' => 'PHP info',
                        'icon' => 'fa fa-info-circle',
                    ],
                ]
            ],
        ];
    }
}
