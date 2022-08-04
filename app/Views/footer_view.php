<footer id="wgr__footer">
    <?php

    //$option_model = new\ App\ Models\ Option();
    //$post_model = new\ App\ Models\ Post();

    // nạp view riêng của từng theme nếu có
    $theme_default_view = VIEWS_PATH . 'default/' . basename( __FILE__ );
    // nạp file kiểm tra private view
    include VIEWS_PATH . 'private_view.php';

    ?>
</footer>
