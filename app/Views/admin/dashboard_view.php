<?php

use App\ Libraries\ UsersType;

?>
<p>Website sử dụng giao diện: <strong><?php echo THEMENAME; ?></strong> - được phát triển bởi <a href="https://echbay.com/" target="_blank" rel="nofollow"><strong>EchBay.com</strong></a></p>
<p>Sử dụng framework <a href="https://codeigniter.com/" target="_blank" rel="nofollow"><strong>Codeigniter <?php echo CodeIgniter\CodeIgniter::CI_VERSION; ?></strong></a> kết hợp với cấu trúc database nền tảng của <a href="https://wordpress.org/" target="_blank" rel="nofollow"><strong>Wordpress</strong></a> nhằm đem lại khả năng tùy biến linh hoạt với tốc độ tối ưu.</p>
<p>PHP version: <strong><?php echo PHP_VERSION; ?></strong> (Khuyên dùng 7.4++)</p>
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

// hiển thị chức năng bật/ tắt debug đối với admin
if ( $session_data[ 'member_type' ] == UsersType::ADMIN ) {
    // nếu debug đang bật -> hiển thị cảnh báo và nút tắt debug
    if ( $debug_enable === true ) {
        ?>
<p class="redcolor medium"><i class="fa fa-warning"></i> Chế độ debug đang được kích hoạt. Vui lòng tắt nó đi khi website chính thức hoạt động.</p>
<?php

if ( file_exists( PUBLIC_HTML_PATH . '.env' ) ) {
    ?>
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
<p class="greencolor"><i class="fa fa-check"></i> Chế độ debug đã được tắt. Nguy cơ lộ diện các vấn đề cần bảo mật sẽ được đảm bảo hơn.</p>
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
