<header>
    <?php

    if ( $isMobile == true ) {
        ?>
    <div class="cf mobile-nav">
        <div class="lf f25 text-center"><a href="./"><i class="fa fa-home"></i></a></div>
        <div class="lf f50">&nbsp;</div>
        <div class="lf f25">
            <div class="text-center cur click-mobile-nav"><i class="fa fa-bars"></i></div>
            <div class="active-mobile-nav">
                <?php
                $menu_model->the_menu( 'top-nav-menu', 'mobile-nav-menu' );
                ?>
            </div>
        </div>
    </div>
    <?php
    }

    ?>
    <div class="top-bg hide-if-mobile">
        <div class="w90 text-right bold">
            <?php

            // nếu đã đăng nhập -> hiển thị menu profile
            if ( !empty( $session_data ) && isset( $session_data[ 'userID' ] ) && $session_data[ 'userID' ] > 0 ) {
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

            $menu_model->the_menu( 'top-lang-menu' );

            ?>
        </div>
    </div>
    <div class="w96 top-position">
        <div class="w90 cf top-menu">
            <div class="lf f15 fullsize-if-mobile">
                <?php
                if ( $isMobile == true ) {
                    $option_model->the_logo( $getconfig, 'logo_mobile', 'logo_mobile_height' );
                } else {
                    $option_model->the_logo( $getconfig );
                }
                ?>
            </div>
            <div class="lf f85 fullsize-if-mobile">
                <div class="cf top-nav-padding">
                    <?php
                    if ( $isMobile != true ) {
                        $menu_model->the_menu( 'top-nav-menu', 'main-nav-menu lf f85 fullsize-if-mobile' );
                        $menu_model->the_menu( 'top-icon-menu', 'lf f15 hide-if-mobile' );
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</header>