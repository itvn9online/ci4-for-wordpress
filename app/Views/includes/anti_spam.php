<?php

/**
 * Chức năng này sẽ tạo ra một số input ẩn để lừa bot
 * 1: input ẩn nên thường chỉ có bot mới fill data vào đấy -> nếu có data -> bot
 **/

//
//echo RAND_ANTI_SPAM . '<br>' . PHP_EOL;

// in ra input chỉ định sẽ alert nếu lỗi
if ($hide_captcha === false) {
?>
    <input type="hidden" name="<?php echo RAND_ANTI_SPAM; ?>_alert" placeholder="alert" value="<?php echo time(); ?>" aria-required="true" required />
<?php
}

// tạo ID cho thẻ DIV -> để gây khó khăn cho việc xác định thuộc tính của DIV
$anti_div_id_spam = '_' . substr(md5(time()), rand(0, 12), 12);

?>
<style>
    <?php echo 'div#' . $anti_div_id_spam; ?> {
        position: absolute;
        left: -9999px;
        z-index: -1;
        opacity: 0;
    }
</style>
<div id="<?php echo $anti_div_id_spam; ?>">
    <?php

    //
    $i = 1;
    $j = rand($i, count($this->input_anti_spam));
    //$j = 1;
    foreach ($this->input_anti_spam as $k => $v) {
        // tạo 1 input kiểu ngẫu nhiên để gán dữ liệu định sẵn
        $val = '';
        $attr_required = '';
        if ($j == $i) {
            $val = RAND_ANTI_SPAM . '_' . $k;
            //echo $val . '<br>' . PHP_EOL;
            $val = md5($val);
            if ($v == 'email') {
                $val .= '@' . $_SERVER['HTTP_HOST'];
            }

            // input nào có giá trị thì gắn cờ required -> cố tình xóa thì khỏi submit luôn
            $attr_required = 'aria-required="true" required';
        }
        $i++;
    ?>
        <input type="<?php echo $v; ?>" name="<?php echo RAND_ANTI_SPAM; ?>_<?php echo $k; ?>" placeholder="<?php echo $k; ?>" value="<?php echo $val; ?>" <?php echo $attr_required; ?> />
    <?php
    }

    // thêm thời gian hết hạn nếu có
    $time_expired *= 1;
    if ($time_expired <= 0) {
        // mặc định là hết trong 1 ngày nếu ko có
        $time_expired = 24 * 3600;
    }
    $time_expired = $time_expired + time();

    ?>
    <input type="number" name="<?php echo RAND_ANTI_SPAM; ?>_to" placeholder="to" value="<?php echo $time_expired; ?>" aria-required="true" required />
    <input type="text" name="<?php echo RAND_ANTI_SPAM; ?>_token" placeholder="token" value="<?php echo md5(RAND_ANTI_SPAM . $time_expired); ?>" aria-required="true" required />
</div>