<?php

/**
 * Chức năng này sẽ tạo ra một số input ẩn để lừa bot
 * 1: input ẩn nên thường chỉ có bot mới fill data vào đấy -> nếu có data -> bot
 **/

//
//echo $this->rand_anti_spam . '<br>' . "\n";

// tạo ID cho thẻ DIV -> để gây khó khăn cho việc xác định thuộc tính của DIV
$anti_div_id_spam = '_' . $this->rand_anti_spam . mt_rand(99, 999);

?>
<style>
    <?php echo 'p.' . $anti_div_id_spam; ?> {
        position: absolute;
        left: -9999px;
        z-index: -1;
        opacity: 0;
    }
</style>
<div class="<?php echo $anti_div_id_spam; ?>">
    <?php

    //
    $i = 1;
    $j = mt_rand($i, count($this->input_anti_spam));
    //$j = 1;
    foreach ($this->input_anti_spam as $k => $v) {
        // tạo 1 input kiểu ngẫu nhiên để gán dữ liệu định sẵn
        $val = '';
        $attr_required = '';
        if ($j == $i) {
            $val = $this->rand_anti_spam . '_' . $k;
            //echo $val . '<br>' . "\n";
            $val = md5($val);
            // if ($v == 'email') {
            //     $val .= '@' . $_SERVER['HTTP_HOST'];
            // }

            // input nào có giá trị thì gắn cờ required -> cố tình xóa thì khỏi submit luôn
            $attr_required = 'aria-required="true" required';
        }
        $i++;
    ?>
        <input type="hidden" data-type="<?php echo $v; ?>" name="<?php echo $this->rand_anti_spam; ?>_<?php echo $k; ?>" aria-labelledby="<?php echo $v; ?>" autocomplete="off" readonly value="<?php echo $val; ?>" <?php echo $attr_required; ?> />
    <?php
    }

    // thêm thời gian hết hạn nếu có
    $ops['time_expired'] *= 1;
    if ($ops['time_expired'] < 1) {
        // mặc định là hết trong 1 ngày nếu ko có
        $ops['time_expired'] = 86400;
        $ops['time_expired'] -= mt_rand(0, 99);
    } else {
        $ops['time_expired'] += mt_rand(0, 33);
    }
    $ops['time_expired'] = $ops['time_expired'] + time();

    // tạo chuỗi ngẫu nhiên từ session id -> cố định cho input nhưng giá trị thay đổi liên tục
    $rand_code = $this->MY_sessid();
    $rand_code = substr($rand_code, mt_rand(0, strlen($rand_code) - $this->rand_len_code), $this->rand_len_code);

    //
    foreach (
        [
            // timeout
            'to' => $ops['time_expired'],
            // token -> dùng để xác thực với timeout
            'token' => md5($this->rand_anti_spam . $ops['time_expired']),
            // dùng để xác thực session id hiện tại
            'code' => $rand_code,
        ] as $k => $v
    ) {
    ?>
        <input type="hidden" name="<?php echo $this->rand_anti_spam; ?>_<?php echo $k; ?>" placeholder="<?php echo $k; ?>" value="<?php echo $v; ?>" aria-labelledby="<?php echo $k; ?>" readonly aria-required="true" required />
    <?php
    }

    ?>
</div>