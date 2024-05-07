<?php

/**
 * thêm captcha vào form bất kỳ -> có có POST captcha là sẽ có lệnh so khớp POST captcha với session captcha
 **/

//
use App\Libraries\ConfigType;

//
// nạp config cho phần đăng nhập
$firebase_config = $option_model->obj_config(ConfigType::FIREBASE);
//echo $firebase_config->g_recaptcha_site_key;

// chỉ sử dụng khi không có google recaptcha: https://www.google.com/recaptcha/
if (empty($firebase_config->g_recaptcha_site_key)) {
?>
    <br>
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
