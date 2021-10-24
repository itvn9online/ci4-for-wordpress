<p>Website sử dụng giao diện: <strong><?php echo THEMENAME; ?></strong> - được phát triển bởi <a href="https://echbay.com/" target="_blank" rel="nofollow"><strong>EchBay.com</strong></a></p>
<p>Sử dụng framework <a href="https://codeigniter.com/" target="_blank" rel="nofollow"><strong>Codeigniter <?php echo CodeIgniter\CodeIgniter::CI_VERSION; ?></strong></a> kết hợp với cấu trúc database nền tảng của <a href="https://wordpress.org/" target="_blank" rel="nofollow"><strong>Wordpress</strong></a> nhằm đem lại khả năng tùy biến linh hoạt với tốc độ tối ưu.</p>
<p>PHP version: <strong><?php echo PHP_VERSION; ?></strong></p>
<p>Server software: <strong><?php echo $_SERVER['SERVER_SOFTWARE']; ?></strong></p>
<p>Database: <strong>
    <?php
    if ($current_dbname != '') {
        echo '******' . substr($current_dbname, 6);
    }
    ?>
    </strong></p>
<?php


//
//print_r( $_SERVER );

