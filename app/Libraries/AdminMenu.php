<?php

namespace App\ Libraries;

class AdminMenu {
    public static function menu_list() {
        return [
            CUSTOM_ADMIN_URI => [
                'role' => [],
                'name' => '<i class="icon icon-home"></i> <span>Tổng quan website</span>',
                'arr' => []
            ],
            'admin/posts?post_type=' . PostType::POST => [
                // quyền truy cập của loại tài khoản
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => '<i class="fa fa-product-hunt"></i> <span>' . PostType::list( PostType::POST ) . '</span>',
                'arr' => [
                    'admin/posts/add?post_type=' . PostType::POST => [
                        'name' => '<i class="fa fa-plus"></i> <span>Thêm mới ' . PostType::list( PostType::POST ) . '</span>',
                    ],
                    'admin/terms?taxonomy=' . TaxonomyType::POSTS => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . TaxonomyType::list( TaxonomyType::POSTS, true ) . '</span>',
                    ],
                    'admin/terms?taxonomy=' . TaxonomyType::OPTIONS => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . TaxonomyType::list( TaxonomyType::OPTIONS, true ) . '</span>',
                    ],
                    'admin/terms?taxonomy=' . TaxonomyType::TAGS => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . TaxonomyType::list( TaxonomyType::TAGS, true ) . '</span>',
                    ],
                ]
            ],
            'admin/posts?post_type=' . PostType::ADS => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => '<i class="fa fa-picture-o"></i> <span>' . PostType::list( PostType::ADS ) . '</span>',
                'arr' => [
                    'admin/posts/add?post_type=' . PostType::ADS => [
                        'name' => '<i class="fa fa-plus"></i> <span>Thêm mới ' . PostType::list( PostType::ADS ) . '</span>',
                    ],
                    'admin/terms?taxonomy=' . TaxonomyType::ADS => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . TaxonomyType::list( TaxonomyType::ADS, true ) . '</span>',
                    ],
                ]
            ],
            'admin/posts?post_type=' . PostType::BLOG => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => '<i class="fa fa-newspaper-o"></i> <span>' . PostType::list( PostType::BLOG ) . '</span>',
                'arr' => [
                    'admin/posts/add?post_type=' . PostType::BLOG => [
                        'name' => '<i class="fa fa-plus"></i> <span>Thêm mới ' . PostType::list( PostType::BLOG ) . '</span>',
                    ],
                    'admin/terms?taxonomy=' . TaxonomyType::BLOGS => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . TaxonomyType::list( TaxonomyType::BLOGS, true ) . '</span>',
                    ],
                    'admin/terms?taxonomy=' . TaxonomyType::BLOG_TAGS => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . TaxonomyType::list( TaxonomyType::BLOG_TAGS, true ) . '</span>',
                    ],
                ]
            ],
            'admin/posts?post_type=' . PostType::PAGE => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => '<i class="fa fa-file"></i> <span>' . PostType::list( PostType::PAGE ) . '</span>',
                'arr' => [
                    'admin/posts/add?post_type=' . PostType::PAGE => [
                        'name' => '<i class="fa fa-plus"></i> <span>Thêm mới ' . PostType::list( PostType::PAGE ) . '</span>',
                    ],
                ]
            ],
            'admin/uploads?post_type=' . PostType::MEDIA => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => '<i class="fa fa-camera"></i> <span>' . PostType::list( PostType::MEDIA ) . '</span>',
                'arr' => []
            ],
            'admin/comments?comment_type=' . CommentType::CONTACT => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => '<i class="fa fa-comments"></i> <span>' . CommentType::list( CommentType::CONTACT ) . '</span>',
                'arr' => [
                    'admin/comments?comment_type=' . CommentType::COMMENT => [
                        'name' => '<i class="fa fa-picture-o"></i> <span>' . CommentType::list( CommentType::COMMENT ) . '</span>',
                    ],
                ]
            ],
            'admin/menus?post_type=' . PostType::MENU => [
                'role' => [
                    UsersType::MOD,
                ],
                'name' => '<i class="icon icon-th-list"></i> <span>' . PostType::list( PostType::MENU ) . '</span>',
                'arr' => []
            ],
            'admin/users' => [
                'role' => [
                    UsersType::MOD,
                ],
                'name' => '<i class="fa fa-users"></i> <span>' . UsersType::list( UsersType::MEMBER ) . '</span>',
                'arr' => [
                    'admin/users?member_type=' . UsersType::GUEST => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . UsersType::list( UsersType::GUEST ) . '</span>',
                    ],
                    'admin/users?member_type=' . UsersType::MEMBER => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . UsersType::list( UsersType::MEMBER ) . '</span>',
                    ],
                    'admin/users?member_type=' . UsersType::AUTHOR => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . UsersType::list( UsersType::AUTHOR ) . '</span>',
                    ],
                    'admin/users?member_type=' . UsersType::MOD => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . UsersType::list( UsersType::MOD ) . '</span>',
                    ],
                    'admin/users?member_type=' . UsersType::ADMIN => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . UsersType::list( UsersType::ADMIN ) . '</span>',
                    ],
                ]
            ],
            'admin/configs' => [
                'name' => '<i class="icon icon-cog"></i> <span>Cài đặt</span>',
                'arr' => [
                    'admin/configs?config_type=' . ConfigType::CATEGORY => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . ConfigType::list( ConfigType::CATEGORY ) . '</span>',
                    ],
                    'admin/configs?config_type=' . ConfigType::POST => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . ConfigType::list( ConfigType::POST ) . '</span>',
                    ],
                    'admin/configs?config_type=' . ConfigType::BLOGS => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . ConfigType::list( ConfigType::BLOGS ) . '</span>',
                    ],
                    'admin/configs?config_type=' . ConfigType::BLOG => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . ConfigType::list( ConfigType::BLOG ) . '</span>',
                    ],
                    'admin/configs?config_type=' . ConfigType::TRANS => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . ConfigType::list( ConfigType::TRANS ) . '</span>',
                    ],
                    'admin/configs?config_type=' . ConfigType::SMTP => [
                        'name' => '<i class="fa fa-caret-right"></i> <span>' . ConfigType::list( ConfigType::SMTP ) . '</span>',
                    ],
                ]
            ],
        ];
    }
}