<?php

// mobile
if ( $isMobile == true ) {
    include APPPATH . 'Views/includes/header_mobile1.php';
}
// desktop
else {
    ?>
<div class="top-bg">
    <div class="w90 text-right bold cf">
        <?php

        // nếu đã đăng nhập -> hiển thị menu profile
        if ( $current_user_id > 0 ) {
            // hiển thị thêm menu cho admin
            if ( isset( $session_data[ 'userLevel' ] ) && $session_data[ 'userLevel' ] > 0 ) {
                $menu_model->the_menu( 'top-admin-menu', 'top-login-menu' );
            }
            $menu_model->the_menu( 'top-profile-menu', 'top-login-menu' );
        }
        // chưa thì hiển thị menu đăng nhập/ đăng ký
        else {
            $menu_model->the_menu( 'top-login-menu' );
        }

        ?>
    </div>
</div>
<div class="w96 top-position">
    <div class="w90 cf top-menu d-flex">
        <div class="lf f15">
            <?php
            $option_model->the_logo( $getconfig );
            ?>
        </div>
        <div class="lf f75 top-nav-padding div-valign-center">
            <?php
            $menu_model->the_menu( 'top-nav-menu', 'main-nav-menu lf f85' );
            ?>
        </div>
        <div class="lf f10 cf div-valign-center">
            <button type="button" class="btn btn-primary btn-mobile-menu rf" data-bs-toggle="modal" data-bs-target="#searchModal" aria-label="Search"><i class="fa fa-search"></i> Tìm kiếm</button>
        </div>
    </div>
</div>
<?php
} // END desktop

//
include APPPATH . 'Views/includes/header_search.php';
