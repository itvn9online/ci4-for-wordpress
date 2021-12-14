<header>
    <div class="text-center">
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

        ?>
    </div>
</header>
