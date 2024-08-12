<ul class="admin-breadcrumb">
    <li>Logs</li>
</ul>
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
        $fname = 'log-' . date('Y-m-d', $current_time - ($i * DAY)) . '.log';
        $f = $dir_log . '/' . $fname;
        // echo $f . '<br>' . PHP_EOL;

        //
        if (!is_file($f)) {
            continue;
        }
        // continue;

        // tính dung lượng file
        $log_size = filesize($f) / 1024;
        // nếu file quá lớn -> không cho xem trực tiếp
        if ($log_size > $m20) {
    ?>
            <h2 class="bold <?php echo ($log_size > $m10 ? 'redcolor' : ''); ?>"><?php echo $f; ?> (<?php echo number_format($log_size, 2); ?>)</h2>
            <br>
        <?php
            continue;
        }

        //
        if ($i > 0 && $file_log != $fname) {
        ?>
            <p><a href="sadmin/logs?f=<?php echo $fname; ?>" class="bold s15 <?php echo ($log_size > $m10 ? 'redcolor' : ''); ?>"><?php echo $f; ?> (<?php echo number_format($log_size, 2); ?>)</a></p>
        <?php
            continue;
        }

        ?>
        <p class="bold s15 <?php echo ($log_size > $m10 ? 'redcolor' : ''); ?>"><?php echo $f; ?> (<?php echo number_format($log_size, 2); ?>)</p>
        <p class="white-space-preline">
            <?php
            // in nội dung file
            echo file_get_contents($f);
            ?>
        </p>
        <br>
    <?php
    }

    ?>
    <br>
</div>
<div class="text-right">
    <form action="" method="post" target="target_eb_iframe" onsubmit="return confirm('Confirm clear all log!');">
        <button type="submit" class="btn btn-danger"><i class="fa fa-magic"></i> Clear logs</button>
    </form>
</div>