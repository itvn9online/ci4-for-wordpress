<?php

/**
 * Với các website sử dụng chế độ backup riêng thì gọi đến file này để hiển thị code đó ra ngoài
 */

//  
$show_backup = isset($_GET['show_backup']) ? 1 : 0;

// 
$local_bak_bash = base_url('backups/local_bak_bash') . '?to=' . $_SERVER['HTTP_HOST'];
$db_url_bash = base_url('backups/db_bak_bash');





/**
 * lệnh backup code về localhost
 */
$bash_localhost_bak = '
bak_curl_token="' . CRONJOB_TOKEN . '"
bak_curl_url="' . $local_bak_bash . '"
# bash <( curl -k --data "source=localhost&year=' . date('Y') . '&token="$bak_curl_token $bak_curl_url )
';
$bash_localhost_bak = trim($bash_localhost_bak);

//
$last_localhost_backup = ROOTPATH . '___local_bak_done.txt';
$last_localhost_filemtime = file_exists($last_localhost_backup) ? filemtime($last_localhost_backup) : 0;




/**
 * lệnh backup db định dạng sql
 */
$bash_bak_db = '
cd ~
cat > "/home/bash_db_bak" <<END
#!/bin/bash
bak_curl_token="' . CRONJOB_TOKEN . '"
bak_curl_url="' . $db_url_bash . '"
/usr/bin/bash <( /usr/bin/curl -k --data "source=localhost&year=2024&token="\$bak_curl_token \$bak_curl_url )
END
#
/usr/bin/chmod +x /home/bash_db_bak
#
if [ -f /home/bash_db_bak ]; then
echo \'59 3 * * * /home/bash_db_bak\' | crontab -
fi
#
# /usr/bin/bash /home/bash_db_bak
# echo $(/usr/bin/cat /home/bash_db_bak)
# crontab -l
/usr/bin/nano /home/bash_db_bak
';
$bash_bak_db = trim($bash_bak_db);

//
$last_db_backup = ROOTPATH . '___db_bak_done.txt';
$last_db_filemtime = file_exists($last_db_backup) ? filemtime($last_db_backup) : 0;


//
$path_usage = ROOTPATH . '___disk_usage.txt';
$disk_usage = file_exists($path_usage) ? trim(file_get_contents($path_usage)) : '';




?>
<p class="medium18 bold">Lệnh backup thư mục public_html về localhost:</p>
<?php

//
if (time() - $last_localhost_filemtime > DAY) {
?>
    <p class="big">
        <span class="redcolor bold">Dữ liệu về localhost chưa được backup!</span>
        <a href="<?php echo CUSTOM_ADMIN_URI; ?>?show_backup=1" class="bluecolor">More...</a>
    </p>
<?php
} else {
?>
    <p class="medium18">
        <span class="greencolor"><i class="fa fa-lightbulb-o"></i> Backup cuối: <?php echo date('r', $last_localhost_filemtime); ?> (<?php echo number_format((time() - $last_localhost_filemtime) / 3600, 1); ?> giờ trước)</span>
        <a href="<?php echo CUSTOM_ADMIN_URI; ?>?show_backup=1" class="bluecolor">More...</a>
    </p>
<?php
}

// 
if ($show_backup > 0) {
?>
    <div>
        <textarea rows="<?php echo count(explode("\n", $bash_localhost_bak)); ?>" onDblClick="click2Copy(this);" class="s12 form-control" readonly><?php echo $bash_localhost_bak; ?></textarea>
    </div>
    <p><a href="<?php echo $local_bak_bash; ?>" target="_blank"><?php echo $local_bak_bash; ?></a></p>
    <p class="greencolor">* Code này được copy và gắn vào file backups/bash của EBv3.</p>
    <br>
    <br>
    <p class="medium18 bold">Lệnh backup định kỳ database vào thư mục admin_backups:</p>
<?php
}

//
if (time() - $last_db_filemtime > DAY) {
?>
    <p class="big">
        <span class="redcolor bold">Database chưa được backup hàng ngày!</span>
        <a href="<?php echo CUSTOM_ADMIN_URI; ?>?show_backup=1" class="bluecolor">More...</a>
    </p>
<?php
} else {
?>
    <p class="medium18">
        <span class="greencolor"><i class="fa fa-lightbulb-o"></i> Backup cuối: <?php echo date('r', $last_db_filemtime); ?> (<?php echo number_format((time() - $last_db_filemtime) / 3600, 1); ?> giờ trước)</span>
        <a href="<?php echo CUSTOM_ADMIN_URI; ?>?show_backup=1" class="bluecolor">More...</a>
    </p>
<?php
}

// 
if ($show_backup > 0) {
?>
    <div>
        <textarea rows="<?php echo count(explode("\n", $bash_bak_db)); ?>" onDblClick="click2Copy(this);" class="s12 form-control" readonly><?php echo $bash_bak_db; ?></textarea>
    </div>
    <p class="greencolor">* Code này được copy chạy trong server <?php echo $_SERVER['SERVER_ADDR']; ?> để add cronjob chạy hàng ngày.</p>
    <div>
        <input type="text" value="/usr/bin/nano /home/bash_db_bak" onDblClick="click2Copy(this);" class="form-control" readonly />
    </div>
    <div>
        <input type="text" value="/usr/bin/bash /home/bash_db_bak" onDblClick="click2Copy(this);" class="form-control" readonly />
    </div>
    <p><a href="<?php echo $db_url_bash; ?>" target="_blank"><?php echo $db_url_bash; ?></a></p>
    <br>
    <br>
    <p class="medium18 bold">Size, Used, Avail, Memory, Swap:</p>
    <div>
        <textarea rows="<?php echo count(explode("\n", $disk_usage)); ?>" class="form-control s12" readonly><?php echo $disk_usage; ?></textarea>
    </div>
    <br>
<?php
}
?>
<br>