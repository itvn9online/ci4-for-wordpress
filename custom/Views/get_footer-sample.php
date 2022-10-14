<?php
/**
 * file footer riêng của từng theme
 * giả lập tương tự như hàm get_footer() của wordpress
 * nếu có file này trong theme -> nó sẽ được nạp vào cuối </body>
 * Lưu ý: flatsome không chạy chung với vuejs được, chỗ nào chạy flatsome thì phải tách ra khỏi vuejs
 */

// hỗ trợ flatsome bản thấp hơn
//include VIEWS_PATH . 'includes/flatsome-3.15.7.php';
// flatsome 3.16.x xung đột preventDefault nên không phải code nào cũng dùng được
include VIEWS_PATH . 'includes/flatsome.php';