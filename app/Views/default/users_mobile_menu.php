<div class="global-profile-menu">
    <div class="rf">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userMobileMenuModal" aria-label="User Menu"><i class="fa fa-bars"></i></button>
    </div>
    <!-- user mobile menu modal -->
    <div class="modal fade" id="userMobileMenuModal" tabindex="-1" aria-labelledby="userMobileMenuModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userMobileMenuModalLabel"><i class="fa fa-bars"></i> User Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body mobile-show-menu">
                    <?php

                    //
                    include __DIR__ . '/users_menu.php';

                    ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>
