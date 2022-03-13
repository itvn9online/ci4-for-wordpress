<?php

use App\ Libraries\ UsersType;


// TEST xem cache có chạy hay không -> gọi đến cache được gọi trong dashboard để xem có NULL hay không
$check_cache_active = $base_model->scache( 'auto_sync_table_column' );
//echo $check_cache_active . '<br>' . "\n";
//echo $base_model->cache->deleteMatching( 'auto_sync_table_column*' ) . '<br>' . "\n";


//
//print_r( $_SERVER );
//print_r( $_SESSION );
//echo mysqli_get_client_info();
//echo mysql_get_server_info();

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
    $scope.base_url = '<?php echo urlencode( base_url() ); ?>';
    $scope.ci_version = '<?php echo \CodeIgniter\CodeIgniter::CI_VERSION; ?>'; // phiên bản CI hiện tại
    $scope.ci_last_version = 419; // phiên bản CI mới nhất -> đổi màu để dễ nhận biết có bản mới hơn
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
    $scope.cache_actived = <?php echo ($check_cache_active !== NULL ? 1 : 0); ?>;
    $scope.memcached_exist = <?php echo (class_exists( 'Memcached' ) ? 1 : 0); ?>;
    $scope.redis_exist = '<?php echo phpversion( 'redis' ); ?>';
    $scope.cache_handler = '<?php echo MY_CACHE_HANDLER; ?>';
    $scope.last_ci4_update = <?php echo (file_exists( APPPATH . 'VERSION' ) ? filemtime( APPPATH . 'VERSION' ) : filemtime( APPPATH . 'Controllers/Layout.php' )); ?>;
    $scope.calculate_ci4_update = function (last_time) {
        var current_time = Math.ceil(Date.now()/ 1000);
        var one_day = 24 * 3600;
        var cal_day = current_time - last_time;
        cal_day = cal_day/ one_day;
        return cal_day.toFixed(1) * 1;
    };
    $scope.warning_ci_version = function (a, b) {
        if (a.replace(/\./gi, '') * 1 < b) {
            return 'orgcolor';
        }
        return 'greencolor';
    };
    angular.element(document).ready(function () {
        $('.ng-main-content').removeClass('d-none');
    });
});
</script>

<div ng-app="myApp" ng-controller="myCtrl" class="s14 ng-main-content d-none">
    <div ng-if="robots_txt > 0 && robots_txt > 1">
        <p class="redcolor medium18 text-center"><i class="fa fa-warning"></i> Vui lòng kiểm tra lại độ chuẩn xác của <a href="admin/configs?support_tab=data_robots" target="_blank"><strong class="bluecolor">file robots.txt</strong></a></p>
        <br>
    </div>
    <h4>Tổng quan:</h4>
    <p>Website sử dụng giao diện: <strong><?php echo THEMENAME; ?></strong> - được phát triển bởi <a href="https://echbay.com/" target="_blank" rel="nofollow"><strong>EchBay.com</strong></a>. Cập nhật lần cuối: <strong>{{last_ci4_update*1000 | date:'yyyy-MM-dd HH:mm'}}</strong> (<em><strong>{{calculate_ci4_update(last_ci4_update)}}</strong> ngày trước</em>)
        <?php

        // lấy theo version
        if ( file_exists( APPPATH . 'VERSION' ) ) {
            echo ' - Phiên bản: <strong>' . file_get_contents( APPPATH . 'VERSION', 1 ) . '</strong>';
        }

        ?>
    </p>
    <p>Nền tảng chính framework <a href="https://codeigniter.com/download/" target="_blank" rel="nofollow"><strong ng-class="warning_ci_version(ci_version, ci_last_version)">Codeigniter {{ci_version}}</strong></a>
        <?php

        //
        if ( file_exists( PUBLIC_HTML_PATH . 'system.zip' ) ) {
            echo '(<em>Cập nhật lần cuối: ' . date( EBE_DATETIME_FORMAT, filemtime( PUBLIC_HTML_PATH . 'system.zip' ) ) . '</em>)';
        }

        ?>
        kết hợp với cấu trúc database nền tảng của <a href="https://wordpress.org/download/" target="_blank" rel="nofollow"><strong>Wordpress</strong></a> nhằm đem lại khả năng tùy biến linh hoạt với tốc độ tối ưu.</p>
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
    <br>
    <h4>Một số khuyến nghị cho website của bạn hoạt động tốt hơn:</h4>
    <div class="p d-inlines"><strong>Imagick:</strong>
        <div ng-if="imagick_exist > 0" class="greencolor">Xin chúc mừng, <strong>Imagick</strong> đã được cài đặt! Các chức năng xử lý hình ảnh sẽ hoạt động ổn định hơn.</div>
        <div ng-if="imagick_exist <= 0" class="orgcolor">Vui lòng cài đăt thêm <strong>Imagick</strong> để các chức năng xử lý hình ảnh hoạt động ổn định hơn.</div>
    </div>
    <div class="p d-inlines">Cache (<strong><?php echo MY_CACHE_HANDLER; ?></strong> handler):
        <div ng-if="cache_actived > 0" class="greencolor">Xin chúc mừng! Website của bạn vận hành thông qua <strong>Cache</strong>, điều này giúp tăng hiệu suất của website lên rất nhiều.
            <div>Bạn có thể <a href="admin/dashboard/cleanup_cache" class="btn btn-primary btn-mini"><i class="fa fa-magic"></i> vào đây</a> và dọn dẹp cache để website nhận dữ liệu mới nhất.</div>
        </div>
        <div ng-if="cache_actived <= 0" class="orgcolor">Vui lòng kiểm tra và sử dụng <strong>Cache</strong> để tăng hiệu suất cho website của bạn.</div>
    </div>
    <div ng-if="cache_actived > 0"> 
        <!-- khuyên dùng redis -->
        <div class="p d-inlines"><strong>Redis:</strong>
            <div ng-if="cache_handler == 'redis'" class="greencolor">Website của bạn đang sử dụng <strong>redis</strong> làm bộ nhớ đệm, đây là phương thức cache khá tốt mà chúng tôi khuyên dùng.</div>
            <div ng-if="cache_handler != 'redis'" ng-class="cache_handler == 'file' ? 'orgcolor' : ''">Website của bạn đang sử dụng <strong>{{cache_handler}}</strong> làm bộ nhớ đệm.
                <div ng-if="redis_exist != ''" class="greencolor"><strong>Redis</strong> hiện khả dụng trên hosting của bạn, hãy cân nhắc việc kích hoạt nó cho website này.</div>
                <div ng-if="redis_exist == ''">Nếu có thể, hãy sử dụng <strong class="bluecolor">Redis</strong> sẽ giúp cải thiện hiệu suất website. <a href="admin/dev/php_info" class="btn btn-primary btn-mini"><i class="fa fa-search"></i> Vào đây</a> để xem hosting này có hỗ trợ redis không.</div>
            </div>
        </div>
        <!-- END redis --> 
        <!-- không thì Memcached cũng quá ok -->
        <div class="p d-inlines"><strong>Memcached:</strong>
            <div ng-if="memcached_exist > 0" class="greencolor">Xin chúc mừng, <strong>Memcached</strong> đã được cài đặt!
                <div ng-if="cache_handler == 'memcached'" class="greencolor">Và Website của bạn đang sử dụng <strong>memcached</strong> làm bộ nhớ đệm.</div>
                <div ng-if="cache_handler == 'file'" class="greencolor">Nếu bạn đang sử dụng hosting hoặc RAM của VPS từ 2GB trở lên thì hãy chỉnh tham số <strong>MY_CACHE_HANDLER</strong> thành <strong>memcached</strong>.</div>
            </div>
            <div ng-if="memcached_exist <= 0">
                <div ng-if="cache_handler == 'file'" class="orgcolor">Nếu bạn đang sử dụng VPS với lượng RAM đủ lớn, hãy cài đặt thêm <strong>Memcached</strong> và config cho cache sử dụng Memcached <em>hoặc</em>
                    <div>hosting có hỗ trợ extension <strong>Memcached</strong> thì hãy kích hoạt nó lên để tốc độ website đạt mức tốt hơn so với mặc định là sử dụng cache qua ổ cứng.</div>
                </div>
                <div ng-if="cache_handler != 'file'">
                    <div ng-if="cache_handler == 'memcached'" class="orgcolor">Bạn đang kích hoạt cache qua memcached, nhưng hiện tại <strong>memcached</strong> không khả dụng trên hosting của bạn.</div>
                    <div ng-if="cache_handler != 'memcached'"><!-- không cần thông báo gì ở đây nữa --></div>
                </div>
            </div>
        </div>
        <!-- END Memcached --> 
        <!-- securityheaders -->
        <p><strong>Security headers:</strong> <a href="https://securityheaders.com/?q={{base_url}}&followRedirects=on" target="_blank" rel="nofollow" class="btn btn-success btn-mini"><i class="fa fa-shield"></i> vào đây</a> để kiểm tra độ bảo mật thông qua header trên website của bạn. Tối thiểu nên ở mức điểm <button type="button" class="btn btn-warning">B</button> khuyến nghị điểm <button type="button" class="btn btn-success">A</button></p>
        <!-- END securityheaders --> 
        <!-- pagespeed -->
        <p><strong>Page speed:</strong> <a href="https://pagespeed.web.dev/report?url={{base_url}}" target="_blank" rel="nofollow" class="btn btn-success btn-mini"><i class="fa fa-flash"></i> vào đây</a> để phân tích tốc độ website của bạn và độ thân thiện với các công cụ tìm kiếm (tối ưu SEO). Tối thiểu nên ở mức điểm <button type="button" class="btn btn-warning">80</button> khuyến nghị điểm <button type="button" class="btn btn-success">90</button></p>
        <!-- END pagespeed --> 
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
                Khi cần kiểm tra lỗi website, hãy
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#debugModal"> <i class="fa fa-bug"></i> BẬT chế độ debug </button>
                tại đây! 
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
        <p class="bluecolor"><i class="fa fa-cloud-upload"></i> Update system. Dùng khi cần cập nhật bản mới cho Codeigniter 4. File <strong>system.zip</strong> sẽ được update lên <strong>public_html</strong>, và hàm này sẽ hỗ trợ việc giải nén file ra. Thư mục system cũ sẽ được backup vào: <strong>system-{{ci_version}}</strong>.</p>
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
