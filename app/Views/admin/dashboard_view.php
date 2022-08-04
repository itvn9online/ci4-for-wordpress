<?php

// nạp view riêng của từng theme nếu có
$theme_default_view = $admin_root_views . 'default/' . basename( __FILE__ );
// nạp file kiểm tra private view
include VIEWS_PATH . 'private_view.php';