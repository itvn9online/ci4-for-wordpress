<?php

namespace App\ Libraries;

class AdminMenu {
    public static function menu_list() {
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
                'name' => PostType::list( PostType::POST ),
                'icon' => 'fa fa-product-hunt',
                'arr' => [
                    'admin/posts/add' => [
                        'name' => PostType::list( PostType::POST ),
                        'icon' => 'fa fa-plus',
                    ],
                    'admin/terms' => [
                        'name' => TaxonomyType::list( TaxonomyType::POSTS, true ),
                        'icon' => 'fa fa-cubes',
                    ],
                    'admin/postoptions' => [
                        'name' => TaxonomyType::list( TaxonomyType::OPTIONS, true ),
                        'icon' => 'fa fa-filter',
                    ],
                    'admin/tags' => [
                        'name' => TaxonomyType::list( TaxonomyType::TAGS, true ),
                        'icon' => 'fa fa-tag',
                    ],
                ]
            ],
            'admin/adss' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::list( PostType::ADS ),
                'icon' => 'fa fa-picture-o',
                'arr' => [
                    'admin/adss/add' => [
                        'name' => PostType::list( PostType::ADS ),
                        'icon' => 'fa fa-plus',
                    ],
                    'admin/adsoptions' => [
                        'name' => TaxonomyType::list( TaxonomyType::ADS, true ),
                        'icon' => 'fa fa-cubes',
                    ],
                ]
            ],
            'admin/blogs' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::list( PostType::BLOG ),
                'icon' => 'fa fa-newspaper-o',
                'arr' => [
                    'admin/blogs/add' => [
                        'name' => PostType::list( PostType::BLOG ),
                        'icon' => 'fa fa-plus',
                    ],
                    'admin/blogcategory' => [
                        'name' => TaxonomyType::list( TaxonomyType::BLOGS, true ),
                        'icon' => 'fa fa-cubes',
                    ],
                    'admin/blogtags' => [
                        'name' => TaxonomyType::list( TaxonomyType::BLOG_TAGS, true ),
                        'icon' => 'fa fa-tag',
                    ],
                ]
            ],
            'admin/pages' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::list( PostType::PAGE ),
                'icon' => 'fa fa-file',
                'arr' => [
                    'admin/pages/add' => [
                        'name' => PostType::list( PostType::PAGE ),
                        'icon' => 'fa fa-plus',
                    ],
                ]
            ],
            'admin/uploads' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::list( PostType::MEDIA ),
                'icon' => 'fa fa-camera',
                'arr' => []
            ],
            'admin/contacts' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => CommentType::list( CommentType::CONTACT ),
                'icon' => 'fa fa-envelope-o',
                'arr' => []
            ],
            'admin/comments' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => CommentType::list( CommentType::COMMENT ),
                'icon' => 'fa fa-comment-o',
                'arr' => []
            ],
            'admin/menus' => [
                'role' => [
                    UsersType::MOD,
                ],
                'name' => PostType::list( PostType::MENU ),
                'icon' => 'fa fa-bars',
                'arr' => []
            ],
            'admin/users' => [
                'role' => [
                    UsersType::MOD,
                ],
                'name' => UsersType::ALL,
                'icon' => 'fa fa-users',
                'arr' => [
                    'admin/users?member_type=' . UsersType::GUEST => [
                        'name' => UsersType::list( UsersType::GUEST ),
                        'icon' => 'fa fa-question-circle-o',
                    ],
                    'admin/users?member_type=' . UsersType::MEMBER => [
                        'name' => UsersType::list( UsersType::MEMBER ),
                        'icon' => 'fa fa-user',
                    ],
                    'admin/users?member_type=' . UsersType::AUTHOR => [
                        'name' => UsersType::list( UsersType::AUTHOR ),
                        'icon' => 'fa fa-magic',
                    ],
                    'admin/users?member_type=' . UsersType::MOD => [
                        'name' => UsersType::list( UsersType::MOD ),
                        'icon' => 'fa fa-modx',
                    ],
                    'admin/users?member_type=' . UsersType::ADMIN => [
                        'role' => [
                            UsersType::ADMIN,
                        ],
                        'name' => UsersType::list( UsersType::ADMIN ),
                        'icon' => 'fa fa-lock',
                    ],
                ]
            ],
            'admin/configs' => [
                'name' => 'Cài đặt',
                'icon' => 'fa fa-cogs',
                'arr' => [
                    'admin/configs?config_type=' . ConfigType::CATEGORY => [
                        'name' => ConfigType::list( ConfigType::CATEGORY ),
                        'icon' => 'fa fa-cog',
                    ],
                    'admin/configs?config_type=' . ConfigType::POST => [
                        'name' => ConfigType::list( ConfigType::POST ),
                        //'icon' => 'fa fa-asterisk',
                    ],
                    'admin/configs?config_type=' . ConfigType::BLOGS => [
                        'name' => ConfigType::list( ConfigType::BLOGS ),
                        'icon' => 'fa fa-cog',
                    ],
                    'admin/configs?config_type=' . ConfigType::BLOG => [
                        'name' => ConfigType::list( ConfigType::BLOG ),
                        //'icon' => 'fa fa-asterisk',
                    ],
                    'admin/configs?config_type=' . ConfigType::TRANS => [
                        'name' => ConfigType::list( ConfigType::TRANS ),
                        'icon' => 'fa fa-globe',
                    ],
                    'admin/configs?config_type=' . ConfigType::SMTP => [
                        'name' => ConfigType::list( ConfigType::SMTP ),
                        'icon' => 'fa fa-envelope',
                    ],
                    'admin/dev' => [
                        'name' => 'Kỹ thuật',
                        'icon' => 'fa fa-bug',
                    ],
                    'admin/optimize' => [
                        'name' => 'Optimize code',
                        'icon' => 'fa fa-code',
                    ],
                ]
            ],
        ];
    }
}