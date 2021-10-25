<?php

use App\ Libraries\ UsersType;

?>
<p>Website sử dụng giao diện: <strong><?php echo THEMENAME; ?></strong> - được phát triển bởi <a href="https://echbay.com/" target="_blank" rel="nofollow"><strong>EchBay.com</strong></a></p>
<p>Sử dụng framework <a href="https://codeigniter.com/" target="_blank" rel="nofollow"><strong>Codeigniter <?php echo CodeIgniter\CodeIgniter::CI_VERSION; ?></strong></a> kết hợp với cấu trúc database nền tảng của <a href="https://wordpress.org/" target="_blank" rel="nofollow"><strong>Wordpress</strong></a> nhằm đem lại khả năng tùy biến linh hoạt với tốc độ tối ưu.</p>
<p>PHP version: <strong><?php echo phpversion(); ?></strong> (
    <?php

    //
    if ( phpversion() >= '7.4' ) {
        ?>
    <span class="greencolor">Xin chúc mừng! Phiên bản php bạn đang sử dụng đang ở mức khuyến nghị của chúng tôi</span>
    <?php
    } else {
        ?>
    <span class="redcolor">Để tối ưu hiệu suất hệ thống. Vui lòng sử dụng phiên bản PHP <strong>7.4</strong> trở lên</span>
    <?php
    }

    ?>
    )</p>
<p>Server software: <strong><?php echo $_SERVER['SERVER_SOFTWARE']; ?></strong></p>
<p>Database: <strong>
    <?php
    if ( $current_dbname != '' ) {
        echo '******' . substr( $current_dbname, 6 );
    }
    ?>
    </strong></p>
<p>Server IP: <strong><?php echo $_SERVER['SERVER_ADDR']; ?></strong></p>
<p>Server time: <strong><?php echo date('Y-m-d H:i:s'); ?></strong></p>
<hr>
<?php


/*
 * hiển thị chức năng bật/ tắt debug đối với admin
 */
if ( $session_data[ 'member_type' ] == UsersType::ADMIN ) {
    // nếu debug đang bật -> hiển thị cảnh báo và nút tắt debug
    if ( $debug_enable === true ) {
        ?>
<p class="redcolor medium"><i class="fa fa-warning"></i> Chế độ debug thường được kích hoạt để thu thập thêm thông tin chi tiết về lỗi hoặc lỗi trang web, nhưng có thể chứa thông tin nhạy cảm không có sẵn trên một trang web công khai. Vui lòng chỉ bật debug khi cần sửa lỗi liên quan đến code.</p>
<?php

if ( file_exists( PUBLIC_HTML_PATH . '.env' ) ) {
    ?>
<p class="orgcolor"><i class="fa fa-lightbulb-o"></i> Chế độ debug sẽ được tự động TẮT vào lúc <strong><?php echo date('r', filemtime( PUBLIC_HTML_PATH . '.env' ) + $auto_disable_debug); ?></strong>.</p>
<a href="admin/dashboard/disable_env" class="btn btn-danger" target="target_eb_iframe"><i class="fa fa-bug"></i> TẮT chế độ debug</a>
<?php
} else {
    ?>
<p class="orgcolor"><i class="fa fa-cog"></i> Chế độ debug đang được thiết lập thủ công, không qua file <strong>.env</strong>! Bạn chỉ có thể BẬT/ TẮT thủ công.</p>
<?php
} // END file_exists .env

}
// nếu debug đang tắt -> hiển thị chức năng bật debug nếu muốn
else {
    ?>
<p class="greencolor"><i class="fa fa-check"></i> Chế độ debug đã được tắt. Giảm thiểu nguy cơ lộ diện các vấn đề nhạy cảm liên quan đến code.</p>
<?php

if ( file_exists( PUBLIC_HTML_PATH . '.env-bak' ) ) {
    ?>
<a href="admin/dashboard/enable_env" onClick="return confirm('Xin lưu ý! Chỉ bật chế độ debug khi cần thiết!');" class="btn btn-primary" target="target_eb_iframe"><i class="fa fa-bug"></i> BẬT chế độ debug</a>
<?php
} else {
    ?>
<p class="orgcolor"><i class="fa fa-cog"></i> Chế độ debug đang được thiết lập thủ công, không qua file <strong>.env</strong>! Bạn chỉ có thể BẬT/ TẮT thủ công.</p>
<?php
} // END file_exists .env

} // END debug_enable
} // END member_type ADMIN

//
//print_r( $_SERVER );
