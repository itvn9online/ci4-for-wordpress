<?php
/*
 * thông điệp lỗi trả về nếu có
 */
//$get_msg_flash = $session->getFlashdata( 'msg' );
if ( $base_model->MY_session( 'msg' ) != '' ) {
    ?>
<div class="text-submit-msg greencolor"><?php echo $base_model->MY_session( 'msg' ); ?></div>
<?php
$base_model->MY_session( 'msg', '' );
}
//$get_msg_flash = $session->getFlashdata( 'msg_error' );
if ( $base_model->MY_session( 'msg_error' ) != '' ) {
    ?>
<div class="text-submit-msg redcolor"><?php echo $base_model->MY_session( 'msg_error' ); ?></div>
<?php
$base_model->MY_session( 'msg_error', '' );
}
