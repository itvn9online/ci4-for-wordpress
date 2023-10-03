<div class="row align-equal global-profile-main">
    <?php

    //
    if ($isMobile == true) {
        // nạp menu tổng (bản mobile)
        include __DIR__ . '/users_mobile_menu.php';
    } else {
    ?>
        <div class="col small-12 medium-3 large-3">
            <div class="col-inner global-profile-menu">
                <?php
                // nạp menu tổng
                include __DIR__ . '/users_menu.php';
                ?>
            </div>
        </div>
    <?php
    }

    ?>
    <div class="col small-12 medium-9 large-9">
        <div class="col-inner global-profile-content">
            <?php

            echo $main;

            ?>
        </div>
    </div>
</div>