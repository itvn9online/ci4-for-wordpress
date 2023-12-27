<ul class="admin-breadcrumb">
    <li>Thông tin server</li>
</ul>
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