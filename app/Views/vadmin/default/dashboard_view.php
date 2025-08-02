<?php

//
//echo $current_user_id;


/**
 * Chỉnh lại nội dung cho file htaccess
 * Chức năng này sẽ tự động chỉnh nội dung trong file htaccess cho phù hợp
 * Để tắt chức năng này, hãy khai báo constant WGR_DISABLE_AUTO_HTACCESS trong function của child-theme
 */
$path_htaccess = PUBLIC_PUBLIC_PATH . ".htaccess";
$content_htaccess = file_get_contents($path_htaccess);

if (strpos($content_htaccess, '{my_domain.com}') !== false) {
    if (strpos($content_htaccess, '# Header always set Permissions-Policy ') !== false) {
        if (!defined('WGR_DISABLE_AUTO_HTACCESS')) {
            // thay thành domain hiện tại
            $content_htaccess = str_replace('{my_domain.com}', str_replace('www.', '', $_SERVER['HTTP_HOST']), $content_htaccess);
            // set header
            $content_htaccess = str_replace('# Header always set Permissions-Policy ', 'Header always set Permissions-Policy ', $content_htaccess);
            // cập nhật content
            file_put_contents($path_htaccess, $content_htaccess, LOCK_EX);
            echo 'update {my_domain.com} for ' . $path_htaccess . '<br>' . PHP_EOL;
        } else {
            echo 'WGR_DISABLE_AUTO_HTACCESS is defined' . '<br>' . PHP_EOL;
        }
    } else {
        echo 'content_htaccess has {my_domain.com}' . '<br>' . PHP_EOL;
    }
}


// xem có URL nào trùng lặp không
if ($check_dup_url !== false) {
    // print_r($check_dup_url);

?>
    <h4>Dữ liệu có URL trùng khớp:</h4>
    <table class="table table-bordered table-striped with-check table-list eb-table" style="width: auto;">
        <tr>
            <?php
            // tạo thẻ TH
            foreach ($check_dup_url[0] as $k => $v) {
                echo '<th>' . $k . '</th>';
            }
            ?>
        </tr>
        <?php

        // tạo thẻ TD
        foreach ($check_dup_url as $v) {
        ?>
            <tr>
                <?php
                foreach ($v as $v2) {
                    echo '<td>' . $v2 . '</td>';
                }
                ?>
            </tr>
        <?php
        }
        ?>
    </table>
    <p class="redcolor medium">* Ở đây sẽ liệt kê các loại dữ liệu khác nhau nhưng có URL trùng nhau. Bạn cần truy cập và thay đổi URL để tránh trùng lặp.</p>
<?php
}


//
$ci_last_version = 460;

//
$Vue_version = '{{Vue.version}}';
if ($debug_enable === true) {
    $Vue_version = 'Development Version';
}

?>
<div id="app" class="s14 ng-main-content">
    <div v-if="robots_exist > 1">
        <p class="redcolor medium18 text-center"><i class="fa fa-warning"></i> Vui lòng kiểm tra lại độ chuẩn xác của <a href="sadmin/configs?support_tab=data_robots" target="_blank"><strong class="bluecolor">file robots.txt
                    <i class="fa fa-edit"></i></strong></a></p>
        <p class="text-center"><a :href="base_url + 'robots.txt'" class="bluecolor" target="_blank">{{base_url}}robots.txt</a></p>
        <br>
    </div>
    <h4>Dashboard:</h4>
    <p>Website sử dụng giao diện: <strong>
            <?php echo THEMENAME; ?>
        </strong> - được phát triển bởi <a href="<?php echo PARTNER_WEBSITE; ?>" target="_blank" rel="nofollow"><strong>
                <?php echo PARTNER_BRAND_NAME; ?>
            </strong></a>. Cập nhật lần cuối:
        <strong>{{ datetime(last_ci4_update*1000) }}</strong>
        (<em><strong>{{calculate_ci4_update(last_ci4_update)}}</strong> ngày trước</em>)
        <?php

        // lấy theo version
        if (is_file(APPPATH . 'VERSION')) {
            echo ' - Phiên bản: <strong>' . file_get_contents(APPPATH . 'VERSION', 1) . '</strong>';
        }

        ?>
    </p>
    <p>Nền tảng chính framework <a href="https://codeigniter.com/download/" target="_blank" rel="nofollow"><strong :class="warning_ci_version(ci_version, ci_last_version)">Codeigniter {{ci_version}}</strong></a>
        <?php

        //
        if (is_file(PUBLIC_HTML_PATH . 'system.zip')) {
            echo '(<em>Cập nhật lần cuối: ' . date(EBE_DATETIME_FORMAT, filemtime(PUBLIC_HTML_PATH . 'system.zip')) . '</em>)';
        }

        ?>
        kết hợp với cấu trúc database nền tảng của <a href="https://wordpress.org/download/" target="_blank" rel="nofollow"><strong>Wordpress</strong></a> nhằm đem lại khả năng tùy biến linh hoạt với tốc độ tối ưu.
    </p>
    <div class="p d-inlines">PHP version: <strong>
            <?php echo PHP_VERSION; ?>
        </strong> (
        <div v-if="phpversion >= 82">
            <div v-if="phpversion >= 83">
                <div class="greencolor">Xin chúc mừng! Phiên bản PHP{{phpversion}} bạn đang sử dụng đang ở mức khuyến
                    nghị của chúng tôi</div>
            </div>
            <div v-if="phpversion < 83">
                <div class="bluecolor">Xin chúc mừng! Phiên bản PHP{{phpversion}} của bạn tương đối tốt. Tuy nhiên,
                    chúng tôi vẫn khuyến nghị bạn sử dụng phiên bản <strong>PHP 8.2</strong> trở lên.</div>
            </div>
        </div>
        <div v-if="phpversion < 82">
            <div class="redcolor">Để tối ưu hiệu suất hệ thống! Vui lòng sử dụng phiên bản PHP <strong>8.0</strong> trở
                lên. Khuyên dùng: PHP <strong>8.1</strong></div>
        </div>
        )
    </div>
    <div class="left-menu-space">
        <div class="cf">
            <div class="lf f50">
                <div class="row">
                    <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <div class="p"><a href="sadmin/constants?support_tab=data_MY_APP_TIMEZONE">Server timezone</a>: <strong><?php echo MY_APP_TIMEZONE; ?></strong>
                        </div>
                    </div>
                    <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <div class="p">Server time:
                            <strong><?php echo date(EBE_DATETIME_FORMAT); ?></strong>
                        </div>
                    </div>
                    <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <div class="p">Server IP:
                            <a href="https://www.iplocation.net/ip-lookup?query=<?php echo $_SERVER['SERVER_ADDR']; ?>" target="_blank" rel="nofollow" class="bold server-info_ip"><?php echo $_SERVER['SERVER_ADDR']; ?></a>
                        </div>
                    </div>
                    <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <div class="p">Database:
                            <span v-if="current_dbname != ''" class="bold">******{{ current_dbname.slice(-6) }}</span>
                        </div>
                    </div>
                    <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <div class="p">Server OS:
                            <strong><?php echo PHP_OS; ?></strong>
                        </div>
                    </div>
                    <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <div class="p">Server software:
                            <strong><?php echo $_SERVER['SERVER_SOFTWARE']; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="lf f50">
                <div class="row">
                    <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <div class="p">Client timezone: <strong>{{ client_timezone() }}</strong></div>
                    </div>
                    <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <div class="p">Client time: <strong>{{ datetime(Date_now) }}</strong></div>
                    </div>
                    <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <div class="p">Client IP:
                            <a href="https://www.iplocation.net" target="_blank" rel="nofollow" class="bold greencolor user-info_ip"><?php echo $request_ip; ?></a>
                        </div>
                    </div>
                    <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <div class="p">Client OS: <strong>{{ client_os }}</strong></div>
                    </div>
                    <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <div class="p">VueJS version:
                            <a href="https://v2.vuejs.org/v2/guide/installation.html" target="_blank" rel="nofollow" class="bold bluecolor"><?php echo $Vue_version; ?></a>
                        </div>
                    </div>
                    <div class="col col-xl-6 col-lg-6 col-md-6 col-sm-12">
                        <div class="p">Online session:
                            <span class="number-format"><?php echo $count_sessions; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <h4>Một số khuyến nghị cho website của bạn hoạt động tốt hơn:</h4>
    <div class="p d-inlines"><strong>Imagick:</strong>
        <div v-if="imagick_exist > 0" class="greencolor">Xin chúc mừng, <strong>Imagick</strong> đã được cài đặt! Các chức năng xử lý hình ảnh sẽ hoạt động ổn định hơn.</div>
        <div v-if="imagick_exist < 1" class="orgcolor">Vui lòng cài đăt thêm <strong>Imagick</strong> để các chức năng xử lý hình ảnh hoạt động ổn định hơn.</div>
    </div>
    <!-- OPcache -->
    <div class="p d-inlines"><strong>OPcache:</strong>
        <div v-if="opcache_exist > 0" class="greencolor"> Xin chúc mừng, <strong>OPcache</strong> đã được cài đặt!
        </div>
        <div v-if="opcache_exist < 1" class="orgcolor"> Nên bổ sung thêm OPcache sẽ giúp tăng đáng kể hiệu suất website
            của bạn. </div>
    </div>
    <!-- END OPcache -->
    <div class="p d-inlines"><a href="sadmin/constants?support_tab=data_MY_CACHE_HANDLER" class="bold"><i class="fa fa-cog"></i> Cache driver</a> (<strong>{{cache_handler}}</strong> handler):
        <div v-if="cache_actived > 0" class="greencolor">Xin chúc mừng! Website của bạn vận hành thông qua
            <strong>Cache</strong>, điều này giúp tăng hiệu suất của website lên rất nhiều.
            <div>Bạn có thể <a href="sadmin/dashboard/cleanup_cache" class="btn btn-primary btn-mini"><i class="fa fa-magic"></i> vào đây</a> và dọn dẹp cache để website nhận dữ liệu mới nhất.</div>
        </div>
        <div v-if="cache_actived < 1" class="orgcolor">Vui lòng kiểm tra và sử dụng <strong>Cache</strong> để tăng hiệu
            suất cho website của bạn.</div>
    </div>
    <div v-if="cache_actived > 0">
        <!-- khuyên dùng redis -->
        <div class="p d-inlines"><strong>Redis:</strong>
            <div v-if="cache_handler == 'redis'" class="greencolor">Website của bạn đang sử dụng <strong>redis {{redis_exist}}</strong>
                làm bộ nhớ đệm, đây là phương thức cache khá tốt mà chúng tôi khuyên dùng.</div>
            <div v-if="cache_handler != 'redis'" :class="cache_handler == 'file' ? 'orgcolor' : ''">Website của bạn đang
                sử dụng <strong>{{cache_handler}}</strong> làm bộ nhớ đệm.
                <div v-if="redis_exist != ''" class="greencolor"><strong>Redis {{redis_exist}}</strong> hiện khả dụng trên hosting của
                    bạn, hãy cân nhắc việc kích hoạt nó cho website này.</div>
                <div v-if="redis_exist == ''">Nếu có thể, hãy sử dụng <strong class="bluecolor">Redis</strong> sẽ giúp
                    cải thiện hiệu suất website. <a href="sadmin/dev/php_info" class="btn btn-primary btn-mini"><i class="fa fa-search"></i> Vào đây</a> để xem hosting này có hỗ trợ redis không.</div>
            </div>
        </div>
        <!-- END redis -->
        <!-- không thì Memcached cũng quá ok -->
        <div class="p d-inlines"><strong>Memcached:</strong>
            <div v-if="memcached_exist > 0" class="greencolor">Xin chúc mừng, <strong>Memcached</strong> đã được cài
                đặt!
                <div v-if="cache_handler == 'memcached'" class="greencolor">Và Website của bạn đang sử dụng <strong>memcached</strong> làm bộ nhớ đệm.
                </div>
                <div v-if="cache_handler == 'file'" class="greencolor">Nếu bạn đang sử dụng hosting hoặc RAM của VPS từ 2GB trở lên thì hãy chỉnh tham số <strong>MY_CACHE_HANDLER</strong> thành <strong>memcached</strong>.
                </div>
            </div>
            <div v-if="memcached_exist < 1">
                <div>Memcached chưa được cài đặt.</div>
                <div v-if="cache_handler == 'file'" class="orgcolor">Nếu bạn đang sử dụng VPS với lượng RAM đủ lớn, hãy cài đặt thêm <strong>Memcached</strong> và config cho cache sử dụng Memcached <em>hoặc</em>
                    <div>hosting có hỗ trợ extension <strong>Memcached</strong> thì hãy kích hoạt nó lên để tốc độ website đạt mức tốt hơn so với mặc định là sử dụng cache qua ổ cứng.</div>
                </div>
                <div v-if="cache_handler != 'file'">
                    <div v-if="cache_handler == 'memcached'" class="orgcolor">Bạn đang kích hoạt cache qua memcached, nhưng hiện tại <strong>memcached</strong> không khả dụng trên hosting của bạn.</div>
                    <div v-if="cache_handler != 'memcached'">
                        <!-- không cần thông báo gì ở đây nữa -->
                    </div>
                </div>
            </div>
        </div>
        <!-- END Memcached -->
    </div>
    <!-- Session driver -->
    <div>
        <p><a href="sadmin/constants?support_tab=data_MY_SESSION_DRIVE" class="bold"><i class="fa fa-cog"></i> Session driver:</a> <span :class="warning_session_drive(session_drive, cache_handler)">{{session_drive}}</span></p>
        <p><em>* Chúng tôi khuyên dùng <strong>Redis</strong> làm Cache driver và <strong>Memcached</strong> làm Session driver. Nếu VPS/ Hosting của bạn có hỗ trợ 2 driver này thì nên sử dụng nó nhằm tăng tốc độ truy vấn cho website.</em></p>
    </div>
    <br>
    <div>
        <!-- pagespeed -->
        <p><strong>Page speed:</strong> <a :href="'https://pagespeed.web.dev/report?url=' + encode_url" target="_blank" rel="nofollow" class="btn btn-success btn-mini"><i class="fa fa-flash"></i> vào đây</a> để phân tích tốc
            độ website của bạn và độ thân thiện với các công cụ tìm kiếm (tối ưu SEO). Tối thiểu nên ở mức điểm
            <button type="button" class="btn btn-warning">80</button>
            khuyến nghị điểm
            <button type="button" class="btn btn-success">90</button>
        </p>
        <!-- END pagespeed -->
        <!-- schema -->
        <p><strong>Structured data:</strong> <a :href="'https://validator.schema.org/#url=' + encode_url" target="_blank" rel="nofollow" class="btn btn-success btn-mini"><i class="fa fa-lightbulb-o"></i> vào
                đây</a> để kiểm tra dữ liệu có cấu trúc cho website của bạn. Một cấu trúc tốt sẽ giúp website dễ dàng
            SEO hơn.</p>
        <!-- END schema -->
        <!-- Open Graph Facebook -->
        <p><strong>Open Graph Facebook:</strong> <a :href="'https://developers.facebook.com/tools/debug/?q=' + encode_url" target="_blank" rel="nofollow" class="btn btn-success btn-mini"><i class="fa fa-facebook"></i> vào đây</a> để phân tích dữ liệu có cấu trúc
            đối với Facebook.</p>
        <!-- END Open Graph Facebook -->
        <!-- Open Graph Zalo -->
        <p><strong>Open Graph Zalo:</strong> <a :href="'https://developers.zalo.me/tools/debug-sharing?q=' + encode_url" target="_blank" rel="nofollow" class="btn btn-success btn-mini"><i class="fa fa-bug"></i> vào đây</a> để
            phân tích dữ liệu có cấu trúc đối với Zalo.</p>
        <!-- END Open Graph Zalo -->
        <!-- securityheaders -->
        <p><strong>Security headers:</strong> <a :href="'https://securityheaders.com/?q=' + encode_url + '&followRedirects=on'" target="_blank" rel="nofollow" class="btn btn-success btn-mini"><i class="fa fa-shield"></i> vào đây</a> để kiểm tra độ
            bảo mật thông qua header trên website của bạn. Tối thiểu nên ở mức điểm
            <button type="button" class="btn btn-warning">B</button>
            khuyến nghị điểm
            <button type="button" class="btn btn-success">A</button>
        </p>
        <!-- END securityheaders -->
    </div>
    <!-- -->
    <div class="p redcolor medium" :class="current_protocol != 'https:' ? '' : 'd-none'"><i class="fa fa-warning"></i>
        Kết nối hiện tại <strong>{{current_protocol}}</strong> chưa hỗ trợ redirect sang <strong>https</strong>. Vui
        lòng kích hoạt và sử dụng redirect <strong>https</strong> để giúp website bảo mật và nhanh hơn.</div>
    <!-- -->
    <div class="p orgcolor medium" :class="current_www == 'www' ? '' : 'd-none'"><i class="fa fa-warning"></i> Khuyên
        dùng kết nối qua định dang tên miền <strong>non-www</strong> để tránh việc chồng chéo dữ liệu. Kiểu kết nối hiện
        tại: <strong>{{current_www}}</strong>.</div>
    <hr>
    <?php

    /*
     * hiển thị chức năng bật/ tắt debug đối với admin
     */
    if ($session_data['member_type'] == $user_type['admin']) {
    ?>
        <!-- DEBUG -->
        <div>
            <div v-if="debug_enable > 0">
                <p class="redcolor medium"><i class="fa fa-warning"></i> Chế độ debug thường được kích hoạt để thu thập thêm
                    thông tin chi tiết về lỗi hoặc lỗi trang web, nhưng có thể chứa thông tin nhạy cảm không có sẵn trên một
                    trang web công khai.<br>
                    Vui lòng chỉ bật debug khi cần sửa lỗi liên quan đến code.</p>
                <div v-if="exists_f_env > 0">
                    <p class="orgcolor"><i class="fa fa-lightbulb-o"></i> Chế độ debug sẽ được tự động TẮT vào lúc
                        <strong>
                            <?php echo (is_file($f_env) ? date('r', filemtime($f_env) + $auto_disable_debug) : ''); ?>
                        </strong>.
                    </p>
                    <div><a href="sadmin/dashboard/disable_env" class="btn btn-danger" target="target_eb_iframe"><i class="fa fa-bug"></i> TẮT chế độ debug</a> </div>
                </div>
                <div v-if="exists_f_env < 1">
                    <p class="orgcolor"><i class="fa fa-cog"></i> Chế độ debug đang được thiết lập thủ công, không qua file
                        <strong>.env</strong>! Bạn chỉ có thể BẬT/ TẮT thủ công.
                    </p>
                </div>
            </div>
            <div v-if="debug_enable < 1">
                <p class="greencolor"><i class="fa fa-check"></i> Chế độ debug đã được tắt. Giảm thiểu nguy cơ lộ diện các
                    vấn đề nhạy cảm liên quan đến code.</p>
                <div v-if="exists_f_backup_env > 0">
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
                                    <a href="sadmin/dashboard/enable_env" class="d-inline" target="target_eb_iframe">
                                        <button type="button" class="btn btn-primary"><i class="fa fa-thumbs-o-up"></i>
                                            Confirm</button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="exists_f_backup_env < 1">
                    <p class="orgcolor"><i class="fa fa-cog"></i> Chế độ debug đang được thiết lập thủ công, không qua file
                        <strong>.env</strong>! Bạn chỉ có thể BẬT/ TẮT thủ công.
                    </p>
                </div>
            </div>
        </div>
        <br>
        <br>
        <!-- UPDATE CORE -->
        <div v-if="system_zip > 0" class="hide-after-unzip-system">
            <p class="bluecolor"><i class="fa fa-cloud-upload"></i> Update system. Dùng khi cần cập nhật bản mới cho
                Codeigniter 4. File <strong>system.zip</strong> sẽ được update lên <strong>public_html</strong>, và hàm này
                sẽ hỗ trợ việc giải nén file ra. Thư mục system cũ sẽ được backup vào:
                <strong>system-{{ci_version}}</strong>.
            </p>
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
                        <div class="modal-body">Xin lưu ý! chức năng chỉ dành cho kỹ thuật viên! Vui lòng không sử dụng nếu
                            bạn không có khả năng bảo hành lỗi code.</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <a href="sadmin/dashboard/unzip_system" target="target_eb_iframe">
                                <button type="button" class="btn btn-danger"><i class="fa fa-file-archive-o"></i> Unzip
                                    <strong>system.zip</strong></button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <br>
        </div>
        <!-- UPDATE BASE CODE -->
        <div v-if="ci4_for_wordpress_zip > 0" class="hide-after-unzip-base_code">
            <p class="bluecolor"><i class="fa fa-cloud-upload"></i> Update system. Dùng khi cần cập nhật bản mới cho
                Codeigniter 4. File <strong>ci4-for-wordpress.zip</strong> sẽ được update lên <strong>public_html</strong>, và hàm này
                sẽ hỗ trợ việc giải nén file ra.
            </p>
            <!-- Button trigger modal -->
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#unzipBaseCodeModal"> <i class="fa fa-file-archive-o"></i> Unzip <strong>ci4-for-wordpress.zip</strong> </button>
            <!-- Modal -->
            <div class="modal fade" id="unzipBaseCodeModal" tabindex="-1" aria-labelledby="unzipBaseCodeModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="unzipBaseCodeModalLabel">Xác nhận cập nhật system</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">Xin lưu ý! chức năng chỉ dành cho kỹ thuật viên! Vui lòng không sử dụng nếu
                            bạn không có khả năng bảo hành lỗi code.</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <a href="sadmin/dashboard/unzip_base_code" target="target_eb_iframe">
                                <button type="button" class="btn btn-danger"><i class="fa fa-file-archive-o"></i> Unzip
                                    <strong>ci4-for-wordpress.zip</strong></button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <br>
        </div>
        <!-- UPDATE THEME CODE -->
        <div v-if="themename_zip > 0" class="hide-after-unzip-themename">
            <p class="bluecolor"><i class="fa fa-cloud-upload"></i> Update system. Dùng khi cần cập nhật bản mới cho
                Codeigniter 4. File <strong><?php echo THEMENAME; ?>.zip</strong> sẽ được update lên <strong>public_html</strong>, và hàm này sẽ hỗ trợ việc giải nén file ra.
            </p>
            <!-- Button trigger modal -->
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#unzipThemNameModal"> <i class="fa fa-file-archive-o"></i> Unzip <strong><?php echo THEMENAME; ?>.zip</strong> </button>
            <!-- Modal -->
            <div class="modal fade" id="unzipThemNameModal" tabindex="-1" aria-labelledby="unzipThemNameModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="unzipThemNameModalLabel">Xác nhận cập nhật system</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">Xin lưu ý! chức năng chỉ dành cho kỹ thuật viên! Vui lòng không sử dụng nếu
                            bạn không có khả năng bảo hành lỗi code.</div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <a href="sadmin/dashboard/unzip_themename" target="target_eb_iframe">
                                <button type="button" class="btn btn-danger"><i class="fa fa-file-archive-o"></i> Unzip
                                    <strong><?php echo THEMENAME; ?>.zip</strong></button>
                            </a>
                        </div>
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
//var_dump( PHP_OS );

//
$base_model->JSON_parse(
    [
        'vue_data' => [
            'base_url' => DYNAMIC_BASE_URL,
            'ci_version' => \CodeIgniter\CodeIgniter::CI_VERSION,
            // phiên bản CI hiện tại
            'ci_last_version' => $ci_last_version,
            // phiên bản CI mới nhất -> đổi màu để dễ nhận biết có bản mới hơn
            'robots_exist' => $robots_exist,
            'phpversion' => PHP_VERSION,
            'current_dbname' => $current_dbname,
            'debug_enable' => ($debug_enable === true ? 1 : 0),
            'exists_f_env' => (is_file($f_env) ? 1 : 0),
            'exists_f_backup_env' => (is_file($f_backup_env) ? 1 : 0),
            'system_zip' => (is_file(PUBLIC_HTML_PATH . 'system.zip') ? 1 : 0),
            'ci4_for_wordpress_zip' => (is_file(PUBLIC_HTML_PATH . 'ci4-for-wordpress.zip') ? 1 : 0),
            'themename_zip' => (is_file(PUBLIC_HTML_PATH . THEMENAME . '.zip') ? 1 : 0),
            'imagick_exist' => (class_exists('Imagick') ? 1 : 0),
            'cache_actived' => ($check_cache_active !== null ? 1 : 0),
            'memcached_exist' => (class_exists('Memcached') ? 1 : 0),
            'redis_exist' => phpversion('redis'),
            'cache_handler' => MY_CACHE_HANDLER,
            'session_drive' => CUSTOM_SESSION_DRIVER,
            'opcache_exist' => (function_exists('opcache_get_status') && is_array(opcache_get_status()) ? 1 : 0),
            'last_ci4_update' => (is_file(APPPATH . 'VERSION') ? filemtime(APPPATH . 'VERSION') : filemtime(APPPATH . 'Controllers/Layout.php')),
        ],
        'server_ip' => $_SERVER['SERVER_ADDR'],
    ]
);

//
$base_model->add_js('wp-admin/js/dashboard.js');
