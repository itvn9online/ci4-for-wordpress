<ul class="admin-breadcrumb">
    <li>Thông tin server</li>
</ul>
<!-- -->
<div class="flatsome">
    <div class="bold redcolor upper">term_level log</div>
    <?php echo nl2br($content_log); ?>
    <br>
</div>
<!-- -->
<div class="flatsome">
    <div class="bold redcolor upper">System log</div>
    <?php

    // hiển thị log 5 ngày gần nhất
    $current_time = time();
    for ($i = 0; $i < 5; $i++) {
        $file_log = WRITEPATH . 'logs/log-' . date('Y-m-d', $current_time - ($i * DAY)) . '.log';
        // echo $file_log . '<br>' . PHP_EOL;

        //
        if (!is_file($file_log)) {
            continue;
        }

    ?>
        <div class="bold"><?php echo nl2br($file_log); ?></div>
    <?php
        echo nl2br(file_get_contents($file_log));
    }

    ?>
    <br>
</div>
<!-- -->
<div class="flatsome">
    <div class="bold redcolor upper">PHP SESSION</div>
    <?php

    //
    $all_session['session_id'] = session_id();
    $all_session['MY_sessid'] = $base_model->MY_sessid();

    //
    foreach ($all_session as $k => $v) {
    ?>
        <div class="row row-small">
            <div class="col col-3"><?php echo $k; ?></div>
            <div class="col col-8">
                <?php

                //
                if (is_array($v)) {
                    echo json_encode($v);
                } else {
                    echo $v;
                }

                ?>
            </div>
        </div>
    <?php
    }

    ?>
</div>
<!-- -->
<div class="flatsome">
    <div class="bold redcolor upper">PHP COOKIES</div>
    <?php

    foreach ($all_cookie as $k => $v) {
    ?>
        <div class="row row-small">
            <div class="col col-3"><?php echo $k; ?></div>
            <div class="col col-8">
                <?php

                //
                if (is_array($v)) {
                    echo json_encode($v);
                } else {
                    echo $v;
                }

                ?>
            </div>
        </div>
    <?php
    }

    ?>
</div>
<!-- -->
<div class="flatsome">
    <div class="bold redcolor upper">SERVER (PHP Variables)</div>
    <?php

    foreach ($data as $k => $v) {
    ?>
        <div class="row row-small">
            <div class="col col-3"><?php echo $k; ?></div>
            <div class="col col-8">
                <?php

                //
                if (is_array($v)) {
                    echo json_encode($v);
                } else {
                    echo $v;
                }

                ?>
            </div>
        </div>
    <?php
    }

    ?>
</div>