<?php

//
$post_model->the_ads( 'doi-tac-tieu-bieu' );

//
$post_model->the_ads( 'lien-ket-website' );

?>
<footer class="w96">
    <div class="w90">
        <div class="cf footer-container">
            <div class="lf f20 fullsize-if-mobile">
                <?php
                $option_model->the_logo( $getconfig, 'logofooter', 'logo_footer_height' );
                ?>
            </div>
            <div class="lf f80 fullsize-if-mobile">
                <div class="footer-info">
                    <div class="footer-company">
                        <?php
                        echo $getconfig->company_name;
                        ?>
                    </div>
                    <div class="footer-address">
                        <?php
                        echo nl2br( $getconfig->address );
                        ?>
                    </div>
                    <div class="footer-phone">SĐT: <?php echo $getconfig->phone; ?> &nbsp; | &nbsp; Fax: <?php echo $getconfig->fax; ?> </div>
                    <div class="footer-website">Website: <?php echo $getconfig->website; ?> </div>
                </div>
                <div class="whitecolor text-right center-if-mobile">Copyright &copy; <?php echo $getconfig->name . ' ' .date('Y'); ?> </div>
            </div>
        </div>
    </div>
</footer>
