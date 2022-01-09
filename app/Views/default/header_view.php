<header>
    <?php

    // mobile
    if ( $isMobile == true ) {
        ?>
    <div class="mobile-menu">
        <div class="cf default-bg mobile-fixed-menu">
            <div class="lf f25">
                <button type="button" class="btn-mobile-menu" data-bs-toggle="modal" data-bs-target="#mobileMenuModal" aria-label="Menu"><i class="fa fa-bars"></i></button>
            </div>
            <div class="lf f50">
                <?php

                $option_model->the_logo( $getconfig, 'logo_mobile', 'logo_mobile_height' );

                ?>
            </div>
            <div class="lf f25 cf">
                <button type="button" class="btn-mobile-menu rf" data-bs-toggle="modal" data-bs-target="#searchModal" aria-label="Search"><i class="fa fa-search"></i></button>
            </div>
        </div>
    </div>
    <!-- mobile menu Modal -->
    <div class="modal fade" id="mobileMenuModal" tabindex="-1" aria-labelledby="mobileMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mobileMenuModalLabel"><i class="fa fa-bars"></i> Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body mobile-show-menu">
                    <?php

                    //
                    $menu_model->the_menu( 'top-nav-menu', 'mobile-nav-menu' );

                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Đóng</button>
                </div>
            </div>
        </div>
    </div>
    <?php
    } // END mobile
    // desktop
    else {
        ?>
    <div class="top-bg">
        <div class="w90 text-right bold">
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

            $menu_model->the_menu( 'top-lang-menu' );

            ?>
        </div>
    </div>
    <div class="w96 top-position">
        <div class="w90 cf top-menu">
            <div class="lf f15">
                <?php
                $option_model->the_logo( $getconfig );
                ?>
            </div>
            <div class="lf f75">
                <div class="cf top-nav-padding">
                    <?php
                    $menu_model->the_menu( 'top-nav-menu', 'main-nav-menu lf f85' );
                    $menu_model->the_menu( 'top-icon-menu', 'lf f15' );
                    ?>
                </div>
            </div>
            <div class="lf f10 cf">
                <button type="button" class="btn-mobile-menu rf" data-bs-toggle="modal" data-bs-target="#searchModal" aria-label="Search"><i class="fa fa-search"></i></button>
            </div>
        </div>
    </div>
    <?php
    } // END desktop

    ?>
    <!-- search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="get" action="search">
                    <div class="modal-header">
                        <h5 class="modal-title" id="searchModalLabel"><i class="fa fa-search"></i> Tìm kiếm</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <input type="search" name="s" class="form-control" value="" placeholder="Tìm kiếm" onClick="this.select();" aria-required="true" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Đóng</button>
                        <button type="submit" class="btn btn-primary" aria-label="Search"><i class="fa fa-search"></i> Tìm kiếm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</header>
