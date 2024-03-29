<?php

// mobile
if ($isMobile == true) {
    include VIEWS_PATH . 'includes/header_mobile1.php';
}
// desktop
else {
?>
    <section id="top-bar" class="default-bg top-section medium">
        <div class="row row-collapse">
            <div class="col small-12 medium-12 large-12 text-right">
                <div class="col-inner">
                    <?php

                    // nếu đã đăng nhập -> hiển thị menu profile
                    if ($current_user_id > 0) {
                        // hiển thị thêm menu cho admin
                        if (isset($session_data['userLevel']) && $session_data['userLevel'] > 0) {
                            $menu_model->the_menu('top-admin-menu', 'top-login-menu');
                        }
                        $menu_model->the_menu('top-profile-menu', 'top-login-menu');
                    }
                    // chưa thì hiển thị menu đăng nhập/ đăng ký
                    else {
                        $menu_model->the_menu('top-login-menu');
                    }

                    ?>
                </div>
            </div>
        </div>
    </section>
    <section id="masthead" class="header-main">
        <div class="row row-collapse align-middle">
            <div class="col small-3 medium-3 large-3">
                <div class="col-inner">
                    <?php
                    $option_model->the_logo($getconfig);
                    ?>
                </div>
            </div>
            <div class="col small-6 medium-6 large-6">
                <div class="col-inner">
                    <?php
                    include VIEWS_PATH . 'includes/header_search.php';
                    ?>
                </div>
            </div>
        </div>
    </section>
    <section id="wide-nav" class="header-bottom text-center">
        <?php
        $menu_model->the_menu('top-nav-menu');
        ?>
    </section>
<?php
} // END desktop

// tìm kiếm tin tức (mặc định)
include VIEWS_PATH . 'includes/header_modal_search.php';
// tìm kiếm sản phẩm (nếu đã gọi tìm tin tức rồi thì thôi đoạn này)
//include VIEWS_PATH . 'includes/header_modal_products_search.php';
