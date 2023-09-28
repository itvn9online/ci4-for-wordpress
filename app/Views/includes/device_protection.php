<?php

/**
 * File này chỉ include phần php để có 1 số website họ muốn loại trừ 1 số URL không cần block user
 **/
// nạp view riêng của từng theme nếu có
$theme_default_view = VIEWS_PATH . 'default/' . basename(__FILE__);
// nạp file kiểm tra private view
include VIEWS_PATH . 'private_view.php';
