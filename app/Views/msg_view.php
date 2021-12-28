<?php
/*
 * thông điệp lỗi trả về nếu có
 */
if ( $base_model->msg_session() != '' ) {
    ?>
<div class="text-submit-msg greencolor"><?php echo $base_model->msg_session(); ?></div>
<?php
$base_model->msg_session( '' );
}
if ( $base_model->msg_error_session() != '' ) {
    ?>
<div class="text-submit-msg redcolor"><?php echo $base_model->msg_error_session(); ?></div>
<?php
$base_model->msg_error_session( '' );
}
