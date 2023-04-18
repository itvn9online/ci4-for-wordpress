<?php

//
//$post_model->the_ads( 'doi-tac-tieu-bieu' );

//
//$post_model->the_ads( 'lien-ket-website' );

?>
<section class="default-bg footer-section">
    <div class="row row-collapse">
        <div class="col medium-3 small-12 large-3">
            <div class="col-inner">
                <div>
                    <?php $option_model->the_footer_logo($getconfig); ?>
                </div>
                <div class="footer-info">
                    <div class="footer-company">
                        <?php
                        echo $getconfig->company_name;
                        ?>
                    </div>
                    <div class="footer-address">
                        <?php
                        echo nl2br($getconfig->address);
                        ?>
                    </div>
                    <div class="footer-phone">SĐT: <?php echo $getconfig->phone; ?> &nbsp; | &nbsp; Fax: <?php echo $getconfig->fax; ?> </div>
                    <div class="footer-website">Website: <?php echo $getconfig->website; ?> </div>
                </div>
            </div>
            <div>
                <?php $option_model->the_btc_logo($getconfig); ?>
            </div>
        </div>
        <div class="col medium-3 small-12 large-3">
            <div class="col-inner">
                <h4 class="footer-title upper bold">
                    <?php $lang_model->the_text('custom_text0', 'custom_text0'); ?>
                </h4>
                <?php
                $menu_model->the_menu('footer2-menu');
                ?>
            </div>
        </div>
        <div class="col medium-3 small-12 large-3">
            <div class="col-inner">
                <h4 class="footer-title upper bold">
                    <?php $lang_model->the_text('custom_text1', 'custom_text1'); ?>
                </h4>
                <?php
                $menu_model->the_menu('footer3-menu');
                ?>
            </div>
        </div>
        <div class="col medium-3 small-12 large-3">
            <div class="col-inner">
                <h4 class="footer-title upper bold">
                    <?php $lang_model->the_text('custom_text2', 'custom_text2'); ?>
                </h4>
                <div>
                    <?php $lang_model->the_text('custom_text3', 'custom_text3'); ?>
                </div>
                <br>
                <h4 class="footer-title bold">
                    <?php $lang_model->the_text('custom_text4', 'custom_text4'); ?>
                </h4>
                <?php
                $menu_model->the_menu('footer4-menu');
                ?>
            </div>
        </div>
    </div>
</section>
<section class="default2-bg footer2-section">
    <div class="text-center center-if-mobile">
        <?php $lang_model->the_web_license($getconfig); ?>
    </div>
</section>