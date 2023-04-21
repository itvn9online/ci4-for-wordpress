<?php
/*
 * thêm captcha vào form bất kỳ -> có có POST captcha là sẽ có lệnh so khớp POST captcha với session captcha
 */
?>
<br>
<?php
// ưu tiên sử dụng google recaptcha: https://www.google.com/recaptcha/
if (!empty($getconfig->g_recaptcha_site_key)) {
?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <div class="g-recaptcha" data-sitekey="<?php echo $getconfig->g_recaptcha_site_key; ?>"></div>
<?php
}
// không có thì mới sử dung cái captcha mình tự code
else {
?>
    <div class="row row-collapse">
        <div class="col medium-3 small-3 large-3">
            <div class="col-inner l35" style="background: url(./captcha/three?v=<?php echo time(); ?>) right center no-repeat;">&nbsp;</div>
        </div>
        <div class="col medium-1 small-1 large-1">
            <div class="col-inner">&nbsp;</div>
        </div>
        <div class="col medium-6 small-6 large-6">
            <div class="col-inner">
                <div class="form-group">
                    <input type="text" name="captcha" placeholder="Mã xác thực" maxlength="3" class="form-control" aria-required="true" required />
                </div>
            </div>
        </div>
    </div>
<?php
}
