<?php

// 
if (!empty($session_data)) {
    $data_email = $session_data['user_email'];
    $data_fullname = $session_data['display_name'];
    $data_login = $session_data['user_login'];
} else {
    $data_email = '';
    $data_fullname = '';
    $data_login = '';
}

?>
<div class="global-page-module w90">
    <div class="padding-global-content cf-xoa row">
        <div class="col col-main-content custom-width-global-main custom-width-page-main fullsize-if-mobile">
            <div class="col-main-padding col-page-padding">
                <div class="lien-he">
                    <div class="cf">
                        <div class="lf f40 fullsize-if-mobile">
                            <div class="img-max-width">
                                <?php

                                if (isset($data['post_meta']['image'])) {
                                ?>
                                    <img src="<?php echo $data['post_meta']['image']; ?>" />
                                <?php
                                }

                                ?>
                            </div>
                        </div>
                        <div class="lf f60 fullsize-if-mobile">
                            <div class="left-menu-space40">
                                <div class="medium l20 global-details-content <?php echo $data['post_type']; ?>-details-content ul-default-style">
                                    <h1 data-type="<?php echo $data['post_type']; ?>" data-id="<?php echo $data['ID']; ?>" class="page-details-title global-details-title global-module-title">
                                        <?php echo $data['post_title']; ?>
                                    </h1>
                                    <p><span class="mcb"><i class="fa fa-map-marker"></i></span>
                                        <span class="white-space-preline">
                                            <?php
                                            echo $getconfig->address;
                                            ?>
                                        </span>
                                    </p>
                                    <?php
                                    $data['post_content'] = str_replace('][/i]', '></i>', $data['post_content']);
                                    $data['post_content'] = str_replace('[i ', '<i ', $data['post_content']);
                                    echo $data['post_content'];
                                    ?>
                                </div>
                                <hr>
                            </div>
                        </div>
                    </div>
                    <form name="contact_form" accept-charset="utf-8" action="./contact/put" method="post" target="target_eb_iframe" onsubmit="return delay_for_submit_form();">
                        <?php $base_model->anti_spam_field(); ?>
                        <input type="hidden" name="to" value="comments" />
                        <input type="hidden" name="redirect" value="<?php echo $_SERVER['REQUEST_URI']; ?>" />
                        <input type="hidden" name="data[login]" value="<?php echo $data_login; ?>" />
                        <div class="cf eb-contact-form">
                            <div class="lf f40 fullsize-if-mobile">
                                <div class="form-group">
                                    <label>Họ và tên <span class="redcolor">*</span></label>
                                    <input type="text" name="data[fullname]" class="form-control" value="<?php echo $data_fullname; ?>" aria-required="true" required>
                                </div>
                                <br>
                                <div class="form-group">
                                    <label>Địa chỉ email <span class="redcolor">*</span></label>
                                    <input type="email" name="data[email]" class="form-control" value="<?php echo $data_email; ?>" aria-required="true" required>
                                </div>
                                <br>
                                <div class="form-group">
                                    <label>Tiêu đề thông điệp <span class="redcolor">*</span></label>
                                    <input type="text" name="data[title]" class="form-control" value="" aria-required="true" required>
                                </div>
                            </div>
                            <div class="lf f60 fullsize-if-mobile">
                                <div class="left-menu-space40">
                                    <div class="form-group">
                                        <label>Nội dung thông điệp <span class="redcolor">*</span></label>
                                        <textarea name="data[content]" class="form-control" rows="7" aria-required="true" required></textarea>
                                    </div>
                                    <br>
                                    <div class="form-group">
                                        <label>
                                            <input type="checkbox" value="on" name="send_my_email" />
                                            Gửi một bản copy thông điệp này đến email của bạn</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="text-center eb-contact-form">
                            <button type="submit" class="btn btn-primary">Gửi thông điệp</button>
                        </div>
                    </form>
                </div>
                <br />
                <?php
                // html_for_fb_comment
                ?>
                <br>
                <div class="global-page-widget">
                    <?php
                    // str_for_details_sidebar
                    ?>
                </div>
            </div>
        </div>
        <div class="col col-sidebar-content custom-width-global-sidebar custom-width-page-sidebar fullsize-if-mobile">
            <div class="page-right-space global-right-space">
                <?php
                // str_sidebar
                ?>
            </div>
        </div>
    </div>
</div>