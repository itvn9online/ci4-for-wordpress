<ul class="admin-breadcrumb">
    <li>Logs</li>
</ul>
<div class="text-right">
    <form action="" method="post" target="target_eb_iframe" onsubmit="return confirm('Confirm clear all log!');">
        <button type="submit" class="btn btn-danger"><i class="fa fa-magic"></i> Clear logs</button>
    </form>
</div>
<!-- -->
<div class="flatsome">
    <div class="bold redcolor upper">term_level log</div>
    <div class="bold"><?php echo $file_log; ?> (<?php echo (is_file($file_log) ? number_format(filesize($file_log) / 1024, 2) : 0); ?>)</div>
    <?php echo nl2br($content_log); ?>
    <br>
</div>
<!-- -->
<div class="flatsome">
    <div class="bold redcolor upper">System log</div>
    <?php

    //
    $m10 = 10 * 1024;
    $m20 = 20 * 1024;

    // hiển thị log vài ngày gần nhất
    $current_time = time();
    for ($i = 0; $i < 10; $i++) {
        $file_log = WRITEPATH . 'logs/log-' . date('Y-m-d', $current_time - ($i * DAY)) . '.log';
        // echo $file_log . '<br>' . PHP_EOL;

        //
        if (!is_file($file_log)) {
            continue;
        }
        // continue;

        // tính dung lượng file
        $log_size = filesize($file_log) / 1024;
        // nếu file quá lớn -> không cho xem trực tiếp
        if ($log_size > $m20) {
    ?>
            <h2 class="bold <?php echo ($log_size > $m10 ? 'redcolor' : ''); ?>"><?php echo $file_log; ?> (<?php echo number_format($log_size, 2); ?>)</h2>
        <?php
            continue;
        }

        ?>
        <div class="bold <?php echo ($log_size > $m10 ? 'redcolor' : ''); ?>"><?php echo $file_log; ?> (<?php echo number_format($log_size, 2); ?>)</div>
    <?php
        // in nội dung file
        echo nl2br(file_get_contents($file_log));
    }

    ?>
    <br>
</div>