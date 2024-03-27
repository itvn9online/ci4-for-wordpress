<?php

/**
 * File dùng để tạo code backup từ VPS chạy centos về localhost
 */

namespace App\Controllers;

// Libraries
//use App\Libraries\DeletedStatus;
use App\Libraries\UsersType;

//
class Backups extends Layout
{
    // chức năng này không cần nạp header
    public $preload_header = false;

    /**
     * sử dụng lệnh: which sshpass -> để xem đường dẫn tuyệt đối
     * đường dẫn tuyệt đối dùng để chạy sshpass trong cronjob
     */
    // protected $cmd_rsync = 'rsync -avzhe';
    protected $cmd_rsync = '/usr/bin/rsync -avzhe';
    protected $ssh_path_pass = '/opt/homebrew/bin/sshpass';
    protected $mkdir_full_path = '/bin/mkdir';
    protected $chown_full_path = '/usr/sbin/chown';
    protected $chmod_full_path = '/bin/chmod';
    protected $curl_full_path = '/usr/bin/curl';
    protected $rm_full_path = '/bin/rm';
    protected $ssh_copy_id_full_path = '/usr/bin/ssh-copy-id';
    protected $backups_error = '';
    protected $ssh_port_bak = '';
    protected $admin_user_backups = '';
    protected $admin_dir_backups = '';

    // 
    public function __construct()
    {
        // file này xử lý code trên server -> hạn chế lỗi -> nếu có lỗi thì phải xử lý ngay
        ini_set('display_errors', 1);
        error_reporting(E_ALL);

        // 
        parent::__construct();

        // thư mục backup cho admin
        $this->admin_user_backups = 'admin';
        $this->admin_dir_backups = '/home/' . $this->admin_user_backups . '/admin_backups';

        // 
        $this->backups_error = LOCAL_BAK_PATH . '/backups_error-' . date('Y-m-d') . '.txt';
        if (SSH_BAK_PORT != '') {
            $this->ssh_port_bak = ' "ssh -p ' . trim(SSH_BAK_PORT) . '"';
        }
    }

    /**
     * Trả về mã file bash dùng để backup dữ liệu về localhost
     **/
    public function local_bak_bash()
    {
        header("Content-Type: text/plain");

        //
        $result = '';

        // nếu phương thức không phải là post
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            // xem có phải là admin không
            if ($this->current_user_id < 1 || $this->session_data['userLevel'] != UsersType::ADMIN_LEVEL) {
                $result = 'echo "' . $_SERVER['HTTP_HOST'] . '! Bad request with code ' . __LINE__ . ' "$(date) >> ' . $this->backups_error;
            }
        } else if (!isset($_POST['token']) || $_POST['token'] != CRONJOB_TOKEN) {
            $result = 'echo "' . $_SERVER['HTTP_HOST'] . '! Bad request with token code ' . __LINE__ . ' "$(date) >> ' . $this->backups_error;
        }

        //
        if ($result == '') {
            $result = $this->the_build_bash();
        }

        //
        ob_end_clean();

        //
        echo trim('#!/bin/bash' . PHP_EOL . $result) . PHP_EOL;
        exit();
    }

    /**
     * Trả về mã bash cho local_bak_bash
     **/
    protected function the_build_bash()
    {
        // thư mục backup
        $backup_local_path = LOCAL_BAK_PATH . '/' . $_SERVER['HTTP_HOST'];
        $test_rsync_macos = LOCAL_BAK_PATH . '/test_rsync_macos.txt';
        $backups_txt_log = $backup_local_path . '/backups_log-' . date('Y-m-d') . '.txt';
        $rm_txt_log = $backup_local_path . '/backups_log-' . date('Y-m-d', time() - WEEK) . '.txt';

        // lệnh rsync
        $bin_rsync = $this->cmd_rsync . $this->ssh_port_bak;
        if (date('d') * 1 < 2) {
            // với ngày 01 hàng tháng sẽ thực hiện dọn dẹp dữ liệu
            $bin_rsync .= ' --delete';
        }

        //
        $bash_localhost_bak = '

#
cd ~
echo "' . date('r') . '"

#
if [ -d ' . $backup_local_path . ' ]; then

if [ ! -f ' . LOCAL_BAK_PATH . '/' . __FUNCTION__ . '_running.txt ]; then

#
clear ; sleep 1

# khong cho tien trinh chay lien tuc
echo $(date) > ' . LOCAL_BAK_PATH . '/' . __FUNCTION__ . '_running.txt

#
' . $this->ssh_path_pass . ' -f "' . LOCAL_BAK_PATH . '/sshpass.txt" ' . $this->ssh_copy_id_full_path . ' -p 31141 root@' . $_SERVER['SERVER_ADDR'] . '

# log
echo "Test rsync: "$(date) > ' . $backups_txt_log . '
# create test file
echo "' . $_SERVER['SERVER_ADDR'] . ' "$(date) > ' . $test_rsync_macos . '
# rsync to server
' . $this->cmd_rsync . $this->ssh_port_bak . ' ' . $test_rsync_macos . ' root@' . $_SERVER['SERVER_ADDR'] . ':/root/
# remove file from localhost
' . $this->rm_full_path . ' -rf ' . $test_rsync_macos . '
sleep 1
# sync from srver to localhost
' . $this->cmd_rsync . $this->ssh_port_bak . ' root@' . $_SERVER['SERVER_ADDR'] . ':/root/' . basename($test_rsync_macos) . ' ' . rtrim(dirname($test_rsync_macos), '/') . '/
sleep 1
# check file in localhost
if [ -f ' . $test_rsync_macos . ' ]; then
echo "OK! ' . $_SERVER['SERVER_ADDR'] . ' rsync: "$(date) >> ' . $backups_txt_log . '
else
echo "ERROR! ' . $_SERVER['SERVER_ADDR'] . ' rsync: "$(date) >> ' . $backups_txt_log . '
fi

echo "Backup database"
' . $this->cmd_rsync . $this->ssh_port_bak . ' root@' . $_SERVER['SERVER_ADDR'] . ':/home/admin/admin_backups ' . $backup_local_path . '/

echo "Backup public_html ' . $_SERVER['HTTP_HOST'] . '"
' . $this->cmd_rsync . $this->ssh_port_bak . ' --exclude="cache/*" --exclude="ebcache/*" root@' . $_SERVER['SERVER_ADDR'] . ':' . rtrim(ROOTPATH, '/') . ' ' . $backup_local_path . '/

#
' . $this->curl_full_path . ' --data "source=localhost&token=' . CRONJOB_TOKEN . '" ' . base_url('backups/local_bak_done') . '

# backup xong thi xoa file nay di
' . $this->rm_full_path . ' -rf ' . LOCAL_BAK_PATH . '/' . __FUNCTION__ . '_running.txt

else
msg_error="backup is running ' . LOCAL_BAK_PATH . '/' . __FUNCTION__ . '_running.txt"
echo $msg_error
echo $msg_error" "$(date) > ' . $backups_txt_log . '
fi

# Xóa log 1 tuần trước
' . $this->rm_full_path . ' -rf ' . $rm_txt_log . '

else
msg_error="dir ' . $backup_local_path . ' not found!"
echo $msg_error
echo $msg_error" "$(date) > ' . LOCAL_BAK_PATH . '/' . __FUNCTION__ . '-not-found.txt
fi

#
cd ~
echo "Backup all DONE!"

';

        //
        $result = '';
        $bash_localhost_bak = explode("\n", $bash_localhost_bak);
        foreach ($bash_localhost_bak as $v) {
            $v = trim($v);
            if ($v == '' || substr($v, 0, 1) == '#') {
                continue;
            }
            $result .= $v . PHP_EOL;
        }

        //
        return $result;
    }

    /**
     * Chức năng xác thực quá trình backup dữ liệu về localhost thành công
     **/
    public function local_bak_done($fname = '')
    {
        header('Content-type: application/json; charset=utf-8');

        //
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            // xem có phải là admin không
            if ($this->current_user_id < 1 || $this->session_data['userLevel'] != UsersType::ADMIN_LEVEL) {
                $this->result_json_type([
                    'code' => __LINE__,
                    'error' => 'Bad request'
                ]);
            } else {
                $this->result_json_type([
                    'code' => __LINE__,
                    'msg' => 'OK! test request'
                ]);
            }
        }

        //
        if ($fname == '') {
            $fname = __FUNCTION__;
        }
        file_put_contents(ROOTPATH . '___' . $fname . '.txt', date('r'));

        //
        $this->result_json_type([
            'code' => __LINE__,
            'msg' => 'OK'
        ]);
    }

    /**
     * Trả về mã file bash dùng để backup database vào thư mục admin_backups
     **/
    public function db_bak_bash()
    {
        header("Content-Type: text/plain");

        //
        $result = '';

        // nếu phương thức không phải là post
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            // xem có phải là admin không
            if ($this->current_user_id < 1 || $this->session_data['userLevel'] != UsersType::ADMIN_LEVEL) {
                $result = 'echo "Bad request with code ' . __LINE__ . '"';
            }
        } else if (!isset($_POST['token']) || $_POST['token'] != CRONJOB_TOKEN) {
            $result = 'echo "Bad request with token code ' . __LINE__ . '"';
        }

        //
        if ($result == '') {
            /**
             * lệnh backup db định dạng sql
             */
            // tên db hiện tại
            $current_dbname = \Config\Database::connect()->database;
            // thư mục backup theo ngày trong tuần
            $admin_day_backups = $this->admin_dir_backups . '/' . date('l');

            // 
            $bash_bak_db = '

cd ~

#
if [ -f /usr/local/directadmin/conf/mysql.conf ]; then

# nano /usr/local/directadmin/conf/mysql.conf
. /usr/local/directadmin/conf/mysql.conf
# echo $user
# echo $passwd

if [ -d ' . $this->admin_dir_backups . ' ]; then

/usr/bin/mkdir -p ' . $admin_day_backups . '
/usr/bin/chmod 755 ' . $admin_day_backups . '
/usr/bin/chown -R ' . $this->admin_user_backups . ' ' . $admin_day_backups . '

# xóa cronjob trước khi backup
/usr/bin/crontab -r


#
if [ -f /usr/local/bin/mysqldump ]; then
/usr/local/bin/mysqldump --single-transaction --routines --triggers --add-drop-table --extended-insert -u$user -p$passwd ' . $current_dbname . ' > ' . $admin_day_backups . '/' . $current_dbname . '.sql
else
/usr/bin/mysqldump --single-transaction --routines --triggers --add-drop-table --extended-insert -u$user -p$passwd ' . $current_dbname . ' > ' . $admin_day_backups . '/' . $current_dbname . '.sql
fi
/usr/bin/gzip -f ' . $admin_day_backups . '/' . $current_dbname . '.sql

# xóa file backup hoome trước -> tiết kiệm dung lượng do dung lượng con web này khá lớn
# /usr/bin/rm -rf ' . $this->admin_dir_backups . '/' . date('l', time() - DAY) . '/' . $current_dbname . '.sql
# /usr/bin/rm -rf ' . $this->admin_dir_backups . '/' . date('l', time() - DAY) . '/' . $current_dbname . '.sql.gz
/usr/bin/rm -rf ' . $this->admin_dir_backups . '/' . date('l', time() - DAY) . '/*.sql
# /usr/bin/rm -rf ' . $this->admin_dir_backups . '/' . date('l', time() - DAY) . '/*


# xong việc thì add lại cronjob
if [ -f /home/bash_db_bak ]; then
/usr/bin/echo \'59 3 * * * /home/bash_db_bak\' | crontab -
fi

# code này chạy trên server nên lệnh tạo file báo DONE chạy trực tiếp được, không phải thông qua cURL nữa
/usr/bin/echo $(date) > ' . ROOTPATH . '___db_bak_done.txt
# thống kê hệ thống
/usr/bin/echo $(date) > ' . ROOTPATH . '___disk_usage.txt
/usr/bin/df -h >> ' . ROOTPATH . '___disk_usage.txt
/usr/bin/du -sh /home/ >> ' . ROOTPATH . '___disk_usage.txt
/usr/bin/du -sh ' . ROOTPATH . ' >> ' . ROOTPATH . '___disk_usage.txt
if [ -d ' . $this->admin_dir_backups . ' ]; then
/usr/bin/du -sh ' . $this->admin_dir_backups . '/ >> ' . ROOTPATH . '___disk_usage.txt
fi
if [ -d /home/admin/user_backups ]; then
/usr/bin/du -sh /home/admin/user_backups/ >> ' . ROOTPATH . '___disk_usage.txt
fi
/usr/bin/du -sh /var/lib/mysql >> ' . ROOTPATH . '___disk_usage.txt
/usr/bin/du -sh /var/lib/mysql/' . $current_dbname . ' >> ' . ROOTPATH . '___disk_usage.txt
/usr/bin/free -m >> ' . ROOTPATH . '___disk_usage.txt

fi

else

# /usr/bin/echo "mysql conf not found"
/usr/bin/echo "mysql conf not found "$(date) > ' . ROOTPATH . '___' . __FUNCTION__ . '.txt

fi

';

            // 
            $bash_bak_db = explode("\n", $bash_bak_db);
            foreach ($bash_bak_db as $v) {
                $v = trim($v);
                if ($v == '' || substr($v, 0, 1) == '#') {
                    continue;
                }

                // 
                $result .= $v . PHP_EOL;
            }
        }

        //
        ob_end_clean();

        //
        echo '#!/bin/bash' . PHP_EOL . $result;
        exit();
    }

    /**
     * Chức năng xác thực quá trình backup database vào thưc mục admin_backups thành công
     **/
    public function db_bak_done()
    {
        return $this->local_bak_done(__FUNCTION__);
    }
}
