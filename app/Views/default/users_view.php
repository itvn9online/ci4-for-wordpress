<div class="row">
    <div class="col medium-3 small-12 large-3">
        <?php

        // menu mặc định
        include APPPATH . 'Views/includes/user_menu.php';

        //
        $menu_model->the_menu( 'user-profile-menu' );

        ?>
    </div>
    <div class="col small-12 medium-9 large-9">
        <?php

        echo $main;

        ?>
    </div>
</div>
