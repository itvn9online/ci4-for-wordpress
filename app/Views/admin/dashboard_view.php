<?php

use App\ Libraries\ UsersType;


//
//print_r( $_SERVER );
//print_r( $_SESSION );
//echo mysqli_get_client_info();
//echo mysql_get_server_info();
//echo session_id() . '<br>' . "\n";

// kiểm tra file robots.txt
$robots_txt = PUBLIC_PUBLIC_PATH . 'robots.txt';
// mặc định là không có file robots
$robots_exist = 0;
if ( file_exists( $robots_txt ) ) {
    // có thì mới bắt đầu kiểm tra
    $robots_exist = 1;

    // nếu không xác định được nội dung cần thiết trong robot txt -> cảnh báo
    if ( strpos( file_get_contents( $robots_txt, 1 ), DYNAMIC_BASE_URL ) === false ) {
        $robots_exist = 2;
    }
}

?>
<script>
var current_full_domain = sessionStorage.getItem('WGR-current-full-domain');
var current_protocol = web_link;
var current_www = web_link;
if (current_full_domain !== null) {
    current_protocol = current_full_domain;
    current_www = current_full_domain;
}

//
angular.module('myApp', []).controller('myCtrl', function($scope) {
    $scope.robots_txt = <?php echo $robots_exist; ?>;
    $scope.phpversion = '<?php echo phpversion(); ?>'.replace('.', '').split('.')[0];
    $scope.current_dbname = '<?php echo $current_dbname; ?>';
    $scope.current_protocol = current_protocol.split('//')[0];
    $scope.current_www = current_www.split('.')[0].split('//')[1];
    $scope.debug_enable = <?php echo ($debug_enable === true ? 1 : 0); ?>;
    $scope.exists_f_env = <?php echo (file_exists( $f_env ) ? 1 : 0); ?>;
    $scope.exists_f_backup_env = <?php echo (file_exists( $f_backup_env ) ? 1 : 0); ?>;
    $scope.system_zip = <?php echo (file_exists( PUBLIC_HTML_PATH . 'system.zip') ? 1 : 0); ?>;
    $scope.imagick_exist = <?php echo (class_exists( 'Imagick' ) ? 1 : 0); ?>;
});
</script>

<div ng-app="myApp" ng-controller="myCtrl">
    <div ng-if="robots_txt > 0 && robots_txt > 1">
        <p class="redcolor medium18 text-center"><i class="fa fa-warning"></i> Vui lòng kiểm tra lại độ chuẩn xác của <a href="admin/configs?support_tab=data_robots" target="_blank"><strong class="bluecolor">file robots.txt</strong></a></p>
        <br>
    </div>
    <p>Website sử dụng giao diện: <strong><?php echo THEMENAME; ?></strong> - được phát triển bởi <a href="https://echbay.com/" target="_blank" rel="nofollow"><strong>EchBay.com</strong></a>. Cập nhật lần cuối:
        <?php

        // lấy theo version
        if ( file_exists( APPPATH . 'VERSION' ) ) {
            echo date( EBE_DATETIME_FORMAT, filemtime( APPPATH . 'VERSION' ) ) . ' - Phiên bản: <strong>' . file_get_contents( APPPATH . 'VERSION', 1 ) . '</strong>';
        }
        // hoặc lấy theo file layout
        else {
            echo date( EBE_DATETIME_FORMAT, filemtime( APPPATH . 'Controllers/Layout.php' ) );
        }

        ?>
    </p>
    <p>Nền tảng chính framework <a href="https://codeigniter.com/" target="_blank" rel="nofollow"><strong>Codeigniter <?php echo \CodeIgniter\CodeIgniter::CI_VERSION; ?></strong></a>
        <?php

        //
        if ( file_exists( PUBLIC_HTML_PATH . 'system.zip' ) ) {
            echo '(<em>Cập nhật lần cuối: ' . date( EBE_DATETIME_FORMAT, filemtime( PUBLIC_HTML_PATH . 'system.zip' ) ) . '</em>)';
        }

        ?>
        kết hợp với cấu trúc database nền tảng của <a href="https://wordpress.org/" target="_blank" rel="nofollow"><strong>Wordpress</strong></a> nhằm đem lại khả năng tùy biến linh hoạt với tốc độ tối ưu.</p>
    <div class="p d-inlines">PHP version: <strong><?php echo phpversion(); ?></strong> (
        <div ng-if="phpversion >= 73">
            <div ng-if="phpversion >= 74">
                <div class="greencolor">Xin chúc mừng! Phiên bản PHP{{phpversion}} bạn đang sử dụng đang ở mức khuyến nghị của chúng tôi</div>
            </div>
            <div ng-if="phpversion < 74">
                <div class="bluecolor">Xin chúc mừng! Phiên bản PHP{{phpversion}} của bạn tương đối tốt. Tuy nhiên, chúng tôi vẫn khuyến nghị bạn sử dụng phiên bản <strong>PHP 7.4</strong> trở lên.</div>
            </div>
        </div>
        <div ng-if="phpversion < 73">
            <div class="redcolor">Để tối ưu hiệu suất hệ thống! Vui lòng sử dụng phiên bản PHP <strong>7.3</strong> trở lên. Khuyên dùng: PHP <strong>7.4</strong></div>
        </div>
        )</div>
    <p>Server software: <strong><?php echo $_SERVER['SERVER_SOFTWARE']; ?></strong></p>
    <div class="p">Database: <span ng-if="current_dbname != ''"> <strong><?php echo '******' . substr( $current_dbname, 6 ); ?></strong> </span> </div>
    <p>Server IP: <strong><?php echo $_SERVER['SERVER_ADDR']; ?></strong></p>
    <p>Server time: <strong><?php echo date(EBE_DATETIME_FORMAT); ?></strong></p>
    <div class="p d-inlines">Imagick:
        <div ng-if="imagick_exist > 0" class="greencolor">Xin chức mừng, <strong>Imagick</strong> đã được cài đặt! Các chức năng xử lý hình ảnh sẽ hoạt động ổn định hơn.</div>
        <div ng-if="imagick_exist <= 0" class="orgcolor">Vui lòng cài đăt thêm <strong>Imagick</strong> để các chức năng xử lý hình ảnh hoạt động ổn định hơn.</div>
    </div>
    <!-- -->
    <div class="p redcolor medium" ng-class="current_protocol != 'https:' ? '' : 'd-none'"><i class="fa fa-warning"></i> Kết nối hiện tại <strong>{{current_protocol}}</strong> chưa hỗ trợ redirect sang <strong>https</strong>. Vui lòng kích hoạt và sử dụng redirect <strong>https</strong> để giúp website bảo mật và nhanh hơn.</div>
    <!-- -->
    <div class="p orgcolor medium" ng-class="current_www == 'www' ? '' : 'd-none'"><i class="fa fa-warning"></i> Khuyên dùng kết nối qua định dang tên miền <strong>non-www</strong> để tránh việc chồng chéo dữ liệu. Kiểu kết nối hiện tại: <strong>{{current_www}}</strong>.</div>
    <hr>
    <?php

    /*
     * hiển thị chức năng bật/ tắt debug đối với admin
     */
    if ( $session_data[ 'member_type' ] == UsersType::ADMIN ) {
        ?>
    <!-- DEBUG -->
    <div>
        <div ng-if="debug_enable > 0">
            <p class="redcolor medium"><i class="fa fa-warning"></i> Chế độ debug thường được kích hoạt để thu thập thêm thông tin chi tiết về lỗi hoặc lỗi trang web, nhưng có thể chứa thông tin nhạy cảm không có sẵn trên một trang web công khai.<br>
                Vui lòng chỉ bật debug khi cần sửa lỗi liên quan đến code.</p>
            <div ng-if="exists_f_env > 0">
                <p class="orgcolor"><i class="fa fa-lightbulb-o"></i> Chế độ debug sẽ được tự động TẮT vào lúc <strong><?php echo (file_exists( $f_env ) ? date('r', filemtime( $f_env ) + $auto_disable_debug) : ''); ?></strong>.</p>
                <div><a href="admin/dashboard/disable_env" class="btn btn-danger" target="target_eb_iframe"><i class="fa fa-bug"></i> TẮT chế độ debug</a> </div>
            </div>
            <div ng-if="exists_f_env <= 0">
                <p class="orgcolor"><i class="fa fa-cog"></i> Chế độ debug đang được thiết lập thủ công, không qua file <strong>.env</strong>! Bạn chỉ có thể BẬT/ TẮT thủ công.</p>
            </div>
        </div>
        <div ng-if="debug_enable <= 0">
            <p class="greencolor"><i class="fa fa-check"></i> Chế độ debug đã được tắt. Giảm thiểu nguy cơ lộ diện các vấn đề nhạy cảm liên quan đến code.</p>
            <div ng-if="exists_f_backup_env > 0"> 
                <!-- Button trigger modal -->
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#debugModal"> <i class="fa fa-bug"></i> BẬT chế độ debug </button>
                <!-- Modal -->
                <div class="modal fade" id="debugModal" tabindex="-1" aria-labelledby="debugModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="debugModalLabel">Xác nhận bật chế độ debug</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">Xin lưu ý! Chỉ bật chế độ debug khi cần thiết!</div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <a href="admin/dashboard/enable_env" class="d-inline" target="target_eb_iframe">
                                <button type="button" class="btn btn-primary"><i class="fa fa-thumbs-o-up"></i> Confirm</button>
                                </a> </div>
                        </div>
                    </div>
                </div>
            </div>
            <div ng-if="exists_f_backup_env <= 0">
                <p class="orgcolor"><i class="fa fa-cog"></i> Chế độ debug đang được thiết lập thủ công, không qua file <strong>.env</strong>! Bạn chỉ có thể BẬT/ TẮT thủ công.</p>
            </div>
        </div>
    </div>
    <br>
    <br>
    <!-- UPDATE CORE -->
    <div ng-if="system_zip > 0" class="hide-after-unzip-system">
        <p class="bluecolor"><i class="fa fa-cloud-upload"></i> Update system. Dùng khi cần cập nhật bản mới cho Codeigniter 4. File <strong>system.zip</strong> sẽ được update lên <strong>public_html</strong>, và hàm này sẽ hỗ trợ việc giải nén file ra. Thư mục system cũ sẽ được backup vào: <strong>system-<?php echo \CodeIgniter\CodeIgniter::CI_VERSION; ?></strong>.</p>
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#unzipSystemModal"> <i class="fa fa-file-archive-o"></i> Unzip <strong>system.zip</strong> </button>
        <!-- Modal -->
        <div class="modal fade" id="unzipSystemModal" tabindex="-1" aria-labelledby="unzipSystemModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="unzipSystemModalLabel">Xác nhận cập nhật system</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">Xin lưu ý! chức năng chỉ dành cho kỹ thuật viên! Vui lòng không sử dụng nếu bạn không có khả năng bảo hành lỗi code.</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <a href="admin/dashboard/unzip_system" target="target_eb_iframe">
                        <button type="button" class="btn btn-danger"><i class="fa fa-file-archive-o"></i> Unzip <strong>system.zip</strong></button>
                        </a> </div>
                </div>
            </div>
        </div>
        <br>
        <br>
    </div>
    <?php
    } // END member type ADMIN
    ?>
</div>
<?php

//
$base_model->add_js( 'admin/js/dashboard.js' );
