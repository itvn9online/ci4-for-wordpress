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
            'admin/posts?post_type=' . PostType::POST => [
                // quyền truy cập của loại tài khoản
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::list( PostType::POST ),
                'icon' => 'fa fa-product-hunt',
                'arr' => [
                    'admin/posts/add?post_type=' . PostType::POST => [
                        'name' => PostType::list( PostType::POST ),
                        'icon' => 'fa fa-plus',
                    ],
                    'admin/terms?taxonomy=' . TaxonomyType::POSTS => [
                        'name' => TaxonomyType::list( TaxonomyType::POSTS, true ),
                    ],
                    'admin/terms?taxonomy=' . TaxonomyType::OPTIONS => [
                        'name' => TaxonomyType::list( TaxonomyType::OPTIONS, true ),
                    ],
                    'admin/terms?taxonomy=' . TaxonomyType::TAGS => [
                        'name' => TaxonomyType::list( TaxonomyType::TAGS, true ),
                    ],
                ]
            ],
            'admin/posts?post_type=' . PostType::ADS => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::list( PostType::ADS ),
                'icon' => 'fa fa-picture-o',
                'arr' => [
                    'admin/posts/add?post_type=' . PostType::ADS => [
                        'name' => PostType::list( PostType::ADS ),
                        'icon' => 'fa fa-plus',
                    ],
                    'admin/terms?taxonomy=' . TaxonomyType::ADS => [
                        'name' => TaxonomyType::list( TaxonomyType::ADS, true ),
                    ],
                ]
            ],
            'admin/posts?post_type=' . PostType::BLOG => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::list( PostType::BLOG ),
                'icon' => 'fa fa-newspaper-o',
                'arr' => [
                    'admin/posts/add?post_type=' . PostType::BLOG => [
                        'name' => PostType::list( PostType::BLOG ),
                        'icon' => 'fa fa-plus',
                    ],
                    'admin/terms?taxonomy=' . TaxonomyType::BLOGS => [
                        'name' => TaxonomyType::list( TaxonomyType::BLOGS, true ),
                    ],
                    'admin/terms?taxonomy=' . TaxonomyType::BLOG_TAGS => [
                        'name' => TaxonomyType::list( TaxonomyType::BLOG_TAGS, true ),
                    ],
                ]
            ],
            'admin/posts?post_type=' . PostType::PAGE => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::list( PostType::PAGE ),
                'icon' => 'fa fa-file',
                'arr' => [
                    'admin/posts/add?post_type=' . PostType::PAGE => [
                        'name' => PostType::list( PostType::PAGE ),
                        'icon' => 'fa fa-plus',
                    ],
                ]
            ],
            'admin/uploads?post_type=' . PostType::MEDIA => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::list( PostType::MEDIA ),
                'icon' => 'fa fa-camera',
                'arr' => []
            ],
            'admin/comments?comment_type=' . CommentType::CONTACT => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => CommentType::list( CommentType::CONTACT ),
                'icon' => 'fa fa-envelope-o',
                'arr' => [
                    'admin/comments?comment_type=' . CommentType::COMMENT => [
                        'name' => CommentType::list( CommentType::COMMENT ),
                        'icon' => 'fa fa-comments',
                    ],
                ]
            ],
            'admin/menus?post_type=' . PostType::MENU => [
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
                ]
            ],
            'admin/dev' => [
                'name' => 'Kỹ thuật',
                'icon' => 'fa fa-bug',
                'arr' => [
                    //
                ]
            ],
        ];
    }
}