<?php

/**
 * ngôn ngữ hiển thị của website
 * các ngôn ngữ được hỗ trợ
 **/
/*
define(
    'SITE_LANGUAGE_SUPPORT',
    [
        [
            'value' => 'vn',
            'text' => 'Tiếng Việt',
            'css_class' => 'text-muted'
        ],
        // muốn chạy bao nhiêu ngôn ngữ thì thêm bằng đấy mảng vào đây
        [
            'value' => 'en',
            'text' => 'English',
            'css_class' => 'text-success'
        ],
    ]
);
*/


/*
// khi cần thay label cho trang /sadmin/translates để dễ hiểu hơn thì thêm các thông số vào đây
define( 'TRANS_TRANS_LABEL', [
    'custom_text0' => 'Bản dịch số 0',
    //'custom_textarea0' => 'Bản dịch số 0',
] );
*/

// khi cần thay label cho trang /sadmin/nummons để dễ hiểu hơn thì thêm các thông số vào đây
/*
define(
    'TRANS_NUMS_LABEL',
    [
        'custom_num_mon0' => 'Tùy chỉnh số 0',
    ]
);
*/

// khi cần thay label cho trang /sadmin/checkboxs để dễ hiểu hơn thì thêm các thông số vào đây
/*
define(
    'TRANS_CHECKBOXS_LABEL',
    [
        'custom_checkbox0' => 'Checkbox số 0',
    ]
);
*/

/**
 * Thêm menu cho admin
 * Ngoài các menu mặc định, với mỗi website có thể thêm các menu tùy chỉnh khác nhau vào đây theo công thức mẫu
 **/
/*
function register_admin_menu()
{
    return [
        'sadmin/controller' => [
            // nếu có phân quyền thì nhập phân quyền vào đây, không thì xóa nó đi -> quyền admin
            'role' => [
                UsersType::ADMIN
            ],
            'name' => 'Custom admin menu',
            'icon' => 'fa fa-bug',
            'arr' => [
                'sadmin/sub_controller' => [
                    'name' => 'Sub menu',
                    'icon' => 'fa fa-plus',
                    //'target' => '_blank',
                ],
            ],
            'order' => 95,
        ]
    ];
}
*/

// URL tùy chỉnh của từng tags hoặc custom taxonomy
//define('WGR_CUS_TAX_PERMALINK', []);