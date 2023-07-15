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
                    /*
                    'admin/postoptions' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::OPTIONS, true),
                        'icon' => 'fa fa-filter',
                    ],
                    */
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
            /*
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
            */
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
            // product -> sử dụng chức năng của woocomerce
            'admin/products' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::PROD),
                'icon' => 'fa fa-newspaper-o',
                'arr' => [
                    'admin/products/add' => [
                        'name' => PostType::typeList(PostType::PROD),
                        'icon' => 'fa fa-plus',
                    ],
                    'admin/productcategory' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::PROD_CATS, true),
                        'icon' => 'fa fa-cubes',
                    ],
                    'admin/productoptions' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::PROD_OTPS, true),
                        'icon' => 'fa fa-filter',
                    ],
                    'admin/producttags' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::PROD_TAGS, true),
                        'icon' => 'fa fa-tag',
                    ],
                ]
            ],
            'admin/uploads' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::MEDIA),
                'tag' => 'Images',
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
                'tag' => 'Members',
                'icon' => 'fa fa-users',
                'arr' => [
                    'admin/guests' => [
                        'name' => UsersType::typeList(UsersType::GUEST),
                        'icon' => 'fa fa-question-circle-o',
                    ],
                    'admin/subs' => [
                        'name' => UsersType::typeList(UsersType::SUB),
                        'icon' => 'fa fa-eye',
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
                    'admin/admins' => [
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
                'tag' => 'Setting',
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
                    'admin/configcats' => [
                        'name' => ConfigType::typeList(ConfigType::CATEGORY),
                        'icon' => 'fa fa-cog',
                    ],
                    'admin/configposts' => [
                        'name' => ConfigType::typeList(ConfigType::POST),
                        'icon' => 'fa fa-product-hunt',
                    ],
                    /*
                    'admin/configblogss' => [
                        'name' => ConfigType::typeList(ConfigType::BLOGS),
                        'icon' => 'fa fa-cog',
                    ],
                    'admin/configblogs' => [
                        'name' => ConfigType::typeList(ConfigType::BLOG),
                        'icon' => 'fa fa-newspaper-o',
                    ],
                    */
                    'admin/configprodcats' => [
                        'name' => ConfigType::typeList(ConfigType::PROD_CATS),
                        'icon' => 'fa fa-cog',
                    ],
                    'admin/configprods' => [
                        'name' => ConfigType::typeList(ConfigType::PROD),
                        'icon' => 'fa fa-newspaper-o',
                    ],
                    'admin/smtps' => [
                        'name' => ConfigType::typeList(ConfigType::SMTP),
                        'tag' => 'PHPMailer',
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
                        'tag' => 'Language',
                        'icon' => 'fa fa-globe',
                    ],
                    'admin/firebases' => [
                        'name' => 'Recaptcha/ Firebase',
                        'icon' => 'fa fa-code-fork',
                    ],
                    'admin/zalooas' => [
                        'name' => ConfigType::typeList(ConfigType::ZALO),
                        'icon' => 'fa fa-comment-o',
                    ],
                ]
            ],
            'admin/dev' => [
                'role' => [
                    UsersType::ADMIN,
                ],
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
                    'admin/constants' => [
                        'name' => ConfigType::typeList(ConfigType::CONSTANTS),
                        'icon' => 'fa fa-paragraph',
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
