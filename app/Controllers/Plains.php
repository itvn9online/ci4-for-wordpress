<?php

namespace App\Controllers;

/**
 * 
 * Chi tiết các cập nhật thư viện `Reader` và các file `.mmdb` xem trong file `Geolite2db.php` của `echbayv3`
 * 
 */
include APPPATH . 'ThirdParty/geolite2/Reader.php';
include APPPATH . 'ThirdParty/geolite2/Reader/Decoder.php';
include APPPATH . 'ThirdParty/geolite2/Reader/InvalidDatabaseException.php';
include APPPATH . 'ThirdParty/geolite2/Reader/Metadata.php';
include APPPATH . 'ThirdParty/geolite2/Reader/Util.php';

//
use MaxMind\Db\Reader;

//
class Plains extends Layout
{
    // với 1 số controller, sẽ không nạp cái HTML header vào, nên có thêm tham số này để không nạp header nữa
    public $preload_header = false;
    protected $dir_geolite2 = '';

    public function __construct()
    {
        parent::__construct();

        // 
        $this->dir_geolite2 = APPPATH . 'ThirdParty/geolite2/db';
    }

    /**
     * Download GeoLite2-db nếu chưa có
     **/
    protected function downloadGeoLite2Db()
    {
        // không download với server nguồn
        if (strpos($_SERVER['HTTP_HOST'], 'cloud.echbay.com') === true) {
            return true;
        }

        // 
        $dir_download = APPPATH . 'ThirdParty';
        foreach (
            [
                'geolite2',
                'db',
            ] as $v
        ) {
            $dir_download .= '/' . $v;
            if (!is_dir($dir_download)) {
                mkdir($dir_download, DEFAULT_DIR_PERMISSION) or die('ERROR create cache dir');
                chmod($dir_download, DEFAULT_DIR_PERMISSION);
            }
        }

        // file chứa thời hạn reset lại db định kỳ
        $cache_download = $dir_download . '/last-download.txt';
        $next_download = 0;
        // nếu có file này
        if (is_file($cache_download)) {
            // kiểm tra đã đến hạn cập nhật chưa
            $next_download = file_get_contents($cache_download);
            if (time() < $next_download * 1) {
                // die(__CLASS__ . ':' . __LINE__);
                // chưa thì bỏ qua
                return true;
            }
        }

        // danh sách các file .mmdb sẽ cho tải về
        $arr_files = [
            $dir_download . '/GeoLite2-Country.mmdb',
            $dir_download . '/GeoLite2-ASN.mmdb',
            $dir_download . '/GeoLite2-City.mmdb'
        ];
        // print_r($arr_files);

        // chạy vòng lặp kiểm tra có thiếu file nào ko
        $update_mmdb = false;
        foreach ($arr_files as $v) {
            // thiếu là hủy luôn
            if (!is_file($v)) {
                $update_mmdb = true;
                break;
            }
        }
        // var_dump($update_mmdb);

        // nếu lệnh download được thiết lập
        if ($update_mmdb === true) {
            // -> tiến hành download db từ link:
            $url = 'https://cloud.echbay.com/Geolite2db/mmdb';

            // Use basename() function to return the base name of file 
            $dir_path = dirname($arr_files[0]);
            $file_path = $dir_path . '/db.zip';
            // die($file_path);
            // die(__CLASS__ . ':' . __LINE__);

            // tạo cache -> tránh download liên tục
            file_put_contents($cache_download, time() + (86400 * 7), LOCK_EX);
            chmod($cache_download, 0777);

            // Use file_get_contents() function to get the file 
            // from url and use file_put_contents() function to 
            // save the file by using base name 
            set_time_limit(0);
            if (file_put_contents($file_path, file_get_contents($url), LOCK_EX)) {
                // echo "File downloaded successfully";
                chmod($file_path, 0777);

                // download xong thì giải nén thôi
                $zip = new \ZipArchive();
                if ($zip->open($file_path) === TRUE) {
                    $zip->extractTo($dir_path);
                    $zip->close();
                }

                // 
                foreach ($arr_files as $v) {
                    // thiếu là hủy luôn
                    if (is_file($v)) {
                        chmod($v, 0777);
                    }
                }

                // 
                unlink($file_path);
            }
        }
        // die(__CLASS__ . ':' . __LINE__);

        // 
        return true;
    }

    /**
     * Public url cho các tên miền khác truy cập
     **/
    protected function setOrigin()
    {
        header('Content-type: text/plain');
        header('Access-Control-Allow-Origin: *');
    }

    /**
     * Trả về ip của người dùng -> dùng cho ECHBAY-VPSSIM
     **/
    public function ip()
    {
        $this->setOrigin();

        //
        die($this->base_model->getIPAddress());
    }

    /**
     * Trả về ip cần kiểm tra tọa độ
     **/
    protected function get_ip()
    {
        // chỉ chấp nhận phương thức POST để lấy IP tùy chỉnh
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $ip = $this->MY_post('ip');
            if ($ip == '') {
                $ip = $this->base_model->getIPAddress();
            }
        } else {
            // $ip = $this->MY_get('ip');
            // với GET -> lấy ip hiện tại của người dùng
            $ip = $this->base_model->getIPAddress();
        }
        // if ($ip == '') {
        //     $ip = $this->base_model->getIPAddress();
        // }

        //
        return $ip;
    }

    /**
     * Chức năng này trả về thông tin city theo ip nhưng kèm thêm chức năng kiểm tra và cập nhật db định kỳ nữa
     **/
    public function city_db_ip($ip = '')
    {
        $this->downloadGeoLite2Db();

        // 
        return $this->city_ip($ip);
    }

    /**
     * Trả về city dựa theo IP
     **/
    public function city_ip($ip = '')
    {
        $this->setOrigin();
        if ($ip == '') {
            $ip = $this->get_ip();
        }
        if (strpos($ip, '192.168.') !== false) {
            return $this->result_json_type([
                'city_name' => 'Localhost city',
                'country_name' => 'Localhost country',
                'ip' => $ip,
                'data' => [
                    'city' => [
                        'names' => [
                            'en' => 'Localhost city',
                        ]
                    ]
                ],
            ]);
        }
        // return $this->result_json_type([$ip]);

        //
        $a = $this->getDB($ip, $this->dir_geolite2 . '/GeoLite2-City.mmdb', 'city');
        // var_dump($a);
        // print_r($a);
        // die(__CLASS__ . ':' . __LINE__);

        //
        return $this->result_json_type($a);
    }

    /**
     * Trả về country dựa theo IP
     **/
    public function country_ip($ip = '')
    {
        $this->setOrigin();
        if ($ip == '') {
            $ip = $this->get_ip();
        }

        //
        $a = $this->getDB($ip, $this->dir_geolite2 . '/GeoLite2-Country.mmdb', 'country');
        // var_dump($a);
        // print_r($a);
        // die(__CLASS__ . ':' . __LINE__);

        //
        return $this->result_json_type($a);
    }

    /**
     * Trả về asn dựa theo IP
     **/
    public function asn_ip($ip = '')
    {
        $this->setOrigin();
        if ($ip == '') {
            $ip = $this->get_ip();
        }

        //
        $a = $this->getDB($ip, $this->dir_geolite2 . '/GeoLite2-ASN.mmdb', 'asn');
        // var_dump($a);
        // print_r($a);
        // die(__CLASS__ . ':' . __LINE__);

        //
        return $this->result_json_type($a);
    }

    protected function getDB($ip, $path, $level = '')
    {
        // var_dump($ip);
        // die(__CLASS__ . ':' . __LINE__);

        //
        $reader = new Reader($path);

        //
        $r = $reader->get($ip);
        // var_dump($r);
        // print_r($r);

        //
        $reader->close();

        // 
        return [
            'ip' => $ip,
            'level' => $level,
            // 'last_updated' => date('Y-m-d', filemtime($path)),
            'last_updated' => filemtime($path),
            'data' => $r,
        ];
    }
}
