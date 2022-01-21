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
                    ],
                    'admin/postoptions' => [
                        'name' => TaxonomyType::list( TaxonomyType::OPTIONS, true ),
                    ],
                    'admin/tags' => [
                        'name' => TaxonomyType::list( TaxonomyType::TAGS, true ),
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
                    ],
                    'admin/blogtags' => [
                        'name' => TaxonomyType::list( TaxonomyType::BLOG_TAGS, true ),
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
                'name' => UsersType::list( UsersType::MEMBER ),
                'icon' => 'fa fa-users',
                'arr' => [
                    'admin/users?member_type=' . UsersType::GUEST => [
                        'name' => UsersType::list( UsersType::GUEST ),
                    ],
                    'admin/users?member_type=' . UsersType::MEMBER => [
                        'name' => UsersType::list( UsersType::MEMBER ),
                    ],
                    'admin/users?member_type=' . UsersType::AUTHOR => [
                        'name' => UsersType::list( UsersType::AUTHOR ),
                    ],
                    'admin/users?member_type=' . UsersType::MOD => [
                        'name' => UsersType::list( UsersType::MOD ),
                    ],
                    'admin/users?member_type=' . UsersType::ADMIN => [
                        'role' => [
                            UsersType::ADMIN,
                        ],
                        'name' => UsersType::list( UsersType::ADMIN ),
                    ],
                ]
            ],
            'admin/configs' => [
                'name' => 'Cài đặt',
                'icon' => 'fa fa-cog',
                'arr' => [
                    'admin/configs?config_type=' . ConfigType::CATEGORY => [
                        'name' => ConfigType::list( ConfigType::CATEGORY ),
                    ],
                    'admin/configs?config_type=' . ConfigType::POST => [
                        'name' => ConfigType::list( ConfigType::POST ),
                    ],
                    'admin/configs?config_type=' . ConfigType::BLOGS => [
                        'name' => ConfigType::list( ConfigType::BLOGS ),
                    ],
                    'admin/configs?config_type=' . ConfigType::BLOG => [
                        'name' => ConfigType::list( ConfigType::BLOG ),
                    ],
                    'admin/configs?config_type=' . ConfigType::TRANS => [
                        'name' => ConfigType::list( ConfigType::TRANS ),
                    ],
                    'admin/configs?config_type=' . ConfigType::SMTP => [
                        'name' => ConfigType::list( ConfigType::SMTP ),
                    ],
                    'admin/dev' => [
                        'name' => 'Kỹ thuật',
                        'icon' => 'fa fa-bug',
                    ],
                ]
            ],
        ];
    }
}