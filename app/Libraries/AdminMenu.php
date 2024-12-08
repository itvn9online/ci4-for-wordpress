<?php

namespace App\Libraries;

class AdminMenu
{
    public static function menu_list()
    {
        return [
            CUSTOM_ADMIN_URI => [
                'role' => [],
                'name' => 'Dashboard',
                'icon' => 'fa fa-home',
                'arr' => []
            ],
            'sadmin/posts' => [
                // quyền truy cập của loại tài khoản
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::POST),
                'icon' => 'fa fa-product-hunt',
                'arr' => [
                    'sadmin/posts/add' => [
                        'name' => PostType::typeList(PostType::POST),
                        'icon' => 'fa fa-plus',
                    ],
                    'sadmin/terms' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::POSTS, true),
                        'icon' => 'fa fa-cubes',
                    ],
                    /*
                    'sadmin/postoptions' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::OPTIONS, true),
                        'icon' => 'fa fa-filter',
                    ],
                    */
                    'sadmin/tags' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::TAGS, true),
                        'icon' => 'fa fa-tag',
                    ],
                ]
            ],
            'sadmin/orders' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::ORDER),
                'icon' => 'fa fa-shopping-bag',
                'arr' => []
            ],
            'sadmin/adss' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::ADS),
                'icon' => 'fa fa-picture-o',
                'arr' => [
                    'sadmin/adss/add' => [
                        'name' => PostType::typeList(PostType::ADS),
                        'icon' => 'fa fa-plus',
                    ],
                    'sadmin/adsoptions' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::ADS, true),
                        'icon' => 'fa fa-cubes',
                    ],
                ]
            ],
            /*
            'sadmin/blogs' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::BLOG),
                'icon' => 'fa fa-newspaper-o',
                'arr' => [
                    'sadmin/blogs/add' => [
                        'name' => PostType::typeList(PostType::BLOG),
                        'icon' => 'fa fa-plus',
                    ],
                    'sadmin/blogcategory' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::BLOGS, true),
                        'icon' => 'fa fa-cubes',
                    ],
                    'sadmin/blogtags' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::BLOG_TAGS, true),
                        'icon' => 'fa fa-tag',
                    ],
                ]
            ],
            */
            'sadmin/pages' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::PAGE),
                'icon' => 'fa fa-file',
                'arr' => [
                    'sadmin/pages/add' => [
                        'name' => PostType::typeList(PostType::PAGE),
                        'icon' => 'fa fa-plus',
                    ],
                ]
            ],
            // product -> sử dụng chức năng của woocomerce
            'sadmin/products' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::PROD),
                'icon' => 'fa fa-newspaper-o',
                'arr' => [
                    'sadmin/products/add' => [
                        'name' => PostType::typeList(PostType::PROD),
                        'icon' => 'fa fa-plus',
                    ],
                    'sadmin/productcategory' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::PROD_CATS, true),
                        'icon' => 'fa fa-cubes',
                    ],
                    'sadmin/productoptions' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::PROD_OTPS, true),
                        'icon' => 'fa fa-filter',
                    ],
                    'sadmin/producttags' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::PROD_TAGS, true),
                        'icon' => 'fa fa-tag',
                    ],
                    'sadmin/coupons' => [
                        'name' => TaxonomyType::typeList(TaxonomyType::SHOP_COUPON, true),
                        'icon' => 'fa fa-gift',
                    ],
                ]
            ],
            'sadmin/uploads' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::MEDIA),
                'tag' => 'Images',
                'icon' => 'fa fa-camera',
                'arr' => [
                    'sadmin/uploads/optimize' => [
                        'name' => 'Optimize image',
                        'icon' => 'fa fa-file-archive-o',
                    ],
                ]
            ],
            'sadmin/comments' => [
                'role' => [
                    UsersType::AUTHOR,
                    UsersType::MOD,
                ],
                'name' => CommentType::typeList(CommentType::COMMENT),
                'icon' => 'fa fa-comment-o',
                'arr' => [
                    'sadmin/reviews' => [
                        'role' => [
                            UsersType::AUTHOR,
                            UsersType::MOD,
                        ],
                        'name' => CommentType::typeList(CommentType::REVIEW),
                        'icon' => 'fa fa-star-o',
                        //'arr' => []
                    ],
                    'sadmin/contacts' => [
                        'role' => [
                            UsersType::AUTHOR,
                            UsersType::MOD,
                        ],
                        'name' => CommentType::typeList(CommentType::CONTACT),
                        'icon' => 'fa fa-envelope-o',
                        //'arr' => []
                    ],
                    'sadmin/mailqueues' => [
                        'role' => [
                            UsersType::AUTHOR,
                            UsersType::MOD,
                        ],
                        'name' => 'Mail queue',
                        'icon' => 'fa fa-spinner',
                        //'arr' => []
                    ],
                ]
            ],
            'sadmin/menus' => [
                'role' => [
                    UsersType::MOD,
                ],
                'name' => PostType::typeList(PostType::MENU),
                'icon' => 'fa fa-bars',
                'arr' => [
                    'sadmin/htmlmenus' => [
                        'name' => PostType::typeList(PostType::HTML_MENU),
                        'icon' => 'fa fa-code',
                    ],
                ]
            ],
            'sadmin/users' => [
                'role' => [
                    UsersType::MOD,
                ],
                'name' => UsersType::ALL,
                'tag' => 'Members',
                'icon' => 'fa fa-users',
                'arr' => [
                    'sadmin/guests' => [
                        'name' => UsersType::typeList(UsersType::GUEST),
                        'icon' => 'fa fa-question-circle-o',
                    ],
                    'sadmin/subs' => [
                        'name' => UsersType::typeList(UsersType::SUB),
                        'icon' => 'fa fa-eye',
                    ],
                    'sadmin/members' => [
                        'name' => UsersType::typeList(UsersType::MEMBER),
                        'icon' => 'fa fa-user',
                    ],
                    'sadmin/customers' => [
                        'name' => UsersType::typeList(UsersType::CUSTOMER),
                        'icon' => 'fa fa-user',
                    ],
                    'sadmin/authors' => [
                        'name' => UsersType::typeList(UsersType::AUTHOR),
                        'icon' => 'fa fa-magic',
                    ],
                    'sadmin/mods' => [
                        'name' => UsersType::typeList(UsersType::MOD),
                        'icon' => 'fa fa-modx',
                    ],
                    'sadmin/admins' => [
                        'role' => [
                            UsersType::ADMIN,
                        ],
                        'name' => UsersType::typeList(UsersType::ADMIN),
                        'icon' => 'fa fa-diamond',
                    ],
                ]
            ],
            'sadmin/configs' => [
                'name' => 'Settings',
                'tag' => 'Setting, email, phone, dien thoai, logo, favicon, address, dia chi',
                'icon' => 'fa fa-cogs',
                'arr' => [
                    'sadmin/displays' => [
                        'name' => ConfigType::typeList(ConfigType::DISPLAY),
                        'icon' => 'fa fa-desktop',
                    ],
                    'sadmin/socials' => [
                        'name' => ConfigType::typeList(ConfigType::SOCIAL),
                        'tag' => 'Google Adsense, facebook, Zalo, Youtube, Tawk, TikTok',
                        'icon' => 'fa fa-facebook',
                    ],
                    'sadmin/confighomes' => [
                        'name' => ConfigType::typeList(ConfigType::HOME),
                        'tag' => 'Trang chu',
                        'icon' => 'fa fa-home',
                    ],
                    'sadmin/configcats' => [
                        'name' => ConfigType::typeList(ConfigType::CATEGORY),
                        'tag' => 'Category, danh muc bai viet',
                        'icon' => 'fa fa-cog',
                    ],
                    'sadmin/configposts' => [
                        'name' => ConfigType::typeList(ConfigType::POST),
                        'tag' => 'News, blogs, tin tuc, chi tiet bai viet',
                        'icon' => 'fa fa-product-hunt',
                    ],
                    /*
                    'sadmin/configblogss' => [
                        'name' => ConfigType::typeList(ConfigType::BLOGS),
                        'icon' => 'fa fa-cog',
                    ],
                    'sadmin/configblogs' => [
                        'name' => ConfigType::typeList(ConfigType::BLOG),
                        'icon' => 'fa fa-newspaper-o',
                    ],
                    */
                    'sadmin/configprodcats' => [
                        'name' => ConfigType::typeList(ConfigType::PROD_CATS),
                        'tag' => 'Products category, danh muc san pham',
                        'icon' => 'fa fa-cog',
                    ],
                    'sadmin/configprods' => [
                        'name' => ConfigType::typeList(ConfigType::PROD),
                        'tag' => 'Products details, chi tiet san pham',
                        'icon' => 'fa fa-newspaper-o',
                    ],
                    'sadmin/smtps' => [
                        'name' => ConfigType::typeList(ConfigType::SMTP),
                        'tag' => 'PHPMailer',
                        'icon' => 'fa fa-envelope',
                    ],
                    'sadmin/checkouts' => [
                        'name' => ConfigType::typeList(ConfigType::CHECKOUT),
                        'tag' => 'banks, payments, paypal, shipping, thanh toan, van chuyen',
                        'icon' => 'fa fa-dollar',
                    ],
                    'sadmin/nummons' => [
                        'name' => ConfigType::typeList(ConfigType::NUM_MON),
                        'icon' => 'fa fa-sort-numeric-asc',
                    ],
                    'sadmin/checkboxs' => [
                        'name' => ConfigType::typeList(ConfigType::CHECKBOX),
                        'icon' => 'fa fa-check-square-o',
                    ],
                    'sadmin/translates' => [
                        'name' => ConfigType::typeList(ConfigType::TRANS),
                        'tag' => 'Language, ngon ngu, ban dich',
                        'icon' => 'fa fa-globe',
                    ],
                    'sadmin/rewriterule' => [
                        'name' => 'Rewrite Rule',
                        'tag' => 'rewrites, redirects, 404',
                        'icon' => 'fa fa-share-square-o',
                    ],
                    'sadmin/firebases' => [
                        'name' => 'Recaptcha/ Firebase',
                        'tag' => 'login, recaptcha',
                        'icon' => 'fa fa-code-fork',
                    ],
                    'sadmin/zalooas' => [
                        'name' => ConfigType::typeList(ConfigType::ZALO),
                        'icon' => 'fa fa-comment-o',
                    ],
                ]
            ],
            'sadmin/dev' => [
                'role' => [
                    UsersType::ADMIN,
                ],
                'name' => 'Technique',
                'icon' => 'fa fa-bug',
                'arr' => [
                    'sadmin/dashboard/cleanup_cache' => [
                        'name' => ' Website cleanup',
                        'icon' => 'fa fa-magic',
                    ],
                    'sadmin/optimizes' => [
                        'name' => 'Optimize code',
                        'icon' => 'fa fa-code',
                    ],
                    'sadmin/dashboards/update_code' => [
                        'name' => 'Update code',
                        'icon' => 'fa fa-upload',
                    ],
                    'sadmin/constants' => [
                        'name' => ConfigType::typeList(ConfigType::CONSTANTS),
                        'icon' => 'fa fa-paragraph',
                    ],
                    'sadmin/logs' => [
                        'name' => 'Logs',
                        'icon' => 'fa fa-history',
                    ],
                    'sadmin/dev/php_info' => [
                        'name' => 'PHP info',
                        'icon' => 'fa fa-info-circle',
                    ],
                ]
            ],
        ];
    }
}
