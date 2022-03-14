<div class="row row-collapse align-middle mobile-menu default-bg mobile-fixed-menu">
    <div class="col medium-3 small-3 large-3">
        <div class="col-inner text-center">
            <button type="button" class="btn btn-light btn-mobile-menu" data-bs-toggle="modal" data-bs-target="#mobileMenuModal" aria-label="Menu"><i class="fa fa-bars"></i></button>
        </div>
    </div>
    <div class="col medium-6 small-6 large-6">
        <div class="col-inner">
            <?php

            $option_model->the_logo( $getconfig, 'logo_mobile', 'logo_mobile_height' );

            ?>
        </div>
    </div>
    <div class="col medium-3 small-3 large-3">
        <div class="col-inner text-center">
            <button type="button" class="btn btn-light btn-mobile-menu" data-bs-toggle="modal" data-bs-target="#searchModal" aria-label="Search"><i class="fa fa-search"></i></button>
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
