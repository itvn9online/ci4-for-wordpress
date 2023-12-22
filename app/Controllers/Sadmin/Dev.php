<?php

namespace App\Controllers\Sadmin;

//
class Dev extends Sadmin
{
    public function __construct()
    {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision(__CLASS__);
    }

    public function index()
    {
        return $this->server_info();
    }

    public function server_info()
    {
        $file_log = WRITEPATH . 'logs/' . __FUNCTION__ . '_term_level.txt';
        // echo $file_log . '<br>' . PHP_EOL;

        /**
         * db không cần update liên tục, nếu cần thì clear cache để tái sử dụng
         */
        $has_update = $this->base_model->scache(__FUNCTION__);
        // $has_update = NULL;
        if ($has_update === NULL) {
            $this->base_model->scache(__FUNCTION__, time(), MEDIUM_CACHE_TIMEOUT);

            //
            file_put_contents($file_log, date('r') . PHP_EOL, LOCK_EX);

            /**
             * xóa log quá 1 tháng trước
             */
            $current_time = time() - (24 * 3600 * 30);
            $max_i = 30;
            for ($i = 0; $i < 500; $i++) {
                if ($max_i < 0) {
                    echo 'max_i: ' . $max_i . '<br>' . PHP_EOL;
                    break;
                }

                //
                $old_log = WRITEPATH . 'logs/log-' . date('Y-m-d', $current_time - ($i * DAY)) . '.log';
                // echo $old_log . '<br>' . PHP_EOL;

                //
                if (!is_file($old_log)) {
                    $max_i--;
                    continue;
                }
                echo $old_log . '<br>' . PHP_EOL;
                unlink($old_log);
            }

            //
            // $prefix = WGR_TABLE_PREFIX;

            /**
             * một số lệnh thay đổi dữ liệu thủ công
             */

            //
            $arr_update_db = [
                // 'UPDATE `' . $prefix . 'term_taxonomy` SET `term_level` = 0 WHERE `parent` = 0',
                // 'UPDATE `' . $prefix . 'term_taxonomy` SET `term_level` = 1 WHERE `parent` > 0 AND `parent` IN (SELECT `term_id` FROM `' . $prefix . 'term_taxonomy` WHERE `parent` = 0)',
            ];

            //
            ob_start();

            // chuyển các nhóm ko có cha về level 0
            $result_id = $this->base_model->update_multiple('term_taxonomy', [
                // SET
                'term_level' => 0,
            ], [
                // WHERE
                'parent' => 0,
            ], [
                'debug_backtrace' => debug_backtrace()[1]['function'],
                // hiển thị mã SQL để check
                'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                // 'get_query' => 1,
                // mặc định sẽ remove các field không có trong bảng, nếu muốn bỏ qua chức năng này thì kích hoạt no_remove_field
                //'no_remove_field' => 1
            ]);
            var_dump($result_id);

            //
            // lấy các nhóm con của nhóm này
            $ids = $this->base_model->select(
                'GROUP_CONCAT(DISTINCT term_id SEPARATOR \',\') AS ids',
                'term_taxonomy',
                array(
                    // các kiểu điều kiện where
                    'term_level' => 0,
                    'parent' => 0,
                ),
                array(
                    // trả về COUNT(column_name) AS column_name
                    //'selectCount' => 'ID',
                    // hiển thị mã SQL để check
                    'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    // trả về tổng số bản ghi -> tương tự mysql num row
                    //'getNumRows' => 1,
                    //'offset' => 0,
                    'limit' => -1
                )
            );
            // print_r($ids);
            $ids = explode(',', str_replace(', ', ',', $ids[0]['ids']));
            // print_r($ids);

            //
            if (!empty($ids)) {
                $result_id = $this->base_model->update_multiple('term_taxonomy', [
                    // SET
                    'term_level' => 1,
                ], [
                    // WHERE
                    // 'parent > ' => 0,
                ], [
                    'debug_backtrace' => debug_backtrace()[1]['function'],
                    'where_in' => array(
                        'parent' => $ids
                    ),
                    // hiển thị mã SQL để check
                    'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    // 'get_query' => 1,
                    // mặc định sẽ remove các field không có trong bảng, nếu muốn bỏ qua chức năng này thì kích hoạt no_remove_field
                    //'no_remove_field' => 1
                ]);

                // lấy term level cao nhất để for
                $high_level = $this->base_model->select('term_level', 'term_taxonomy', [
                    // 'parent' => 999999999999
                ], [
                    'order_by' => array(
                        'term_level' => 'DESC'
                    ),
                    // hiển thị mã SQL để check
                    'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    //'offset' => 2,
                    'limit' => 1
                ]);
                // print_r($high_level);
                if (empty($high_level)) {
                    $high_level['term_level'] = 0;
                }
                $high_level['term_level'] += 1;
                print_r($high_level);

                //
                for ($i = 1; $i < $high_level['term_level']; $i++) {
                    // $arr_update_db[] = 'UPDATE `' . $prefix . 'term_taxonomy` SET `term_level` = ' . ($i + 1) . ' WHERE `parent` > 0 AND `parent` IN (SELECT `term_id` FROM `' . $prefix . 'term_taxonomy` WHERE term_level = ' . $i . ')';

                    //
                    $ids = $this->base_model->select(
                        'GROUP_CONCAT(DISTINCT term_id SEPARATOR \',\') AS ids',
                        'term_taxonomy',
                        array(
                            // các kiểu điều kiện where
                            'term_level' => $i,
                        ),
                        array(
                            // trả về COUNT(column_name) AS column_name
                            //'selectCount' => 'ID',
                            // hiển thị mã SQL để check
                            'show_query' => 1,
                            // trả về câu query để sử dụng cho mục đích khác
                            //'get_query' => 1,
                            // trả về tổng số bản ghi -> tương tự mysql num row
                            //'getNumRows' => 1,
                            //'offset' => 0,
                            'limit' => -1
                        )
                    );
                    // print_r($ids);
                    $ids = explode(',', str_replace(', ', ',', $ids[0]['ids']));
                    // print_r($ids);

                    //
                    if (!empty($ids)) {
                        $result_id = $this->base_model->update_multiple('term_taxonomy', [
                            // SET
                            'term_level' => ($i + 1),
                        ], [
                            // WHERE
                            // 'parent > ' => 0,
                        ], [
                            'debug_backtrace' => debug_backtrace()[1]['function'],
                            'where_in' => array(
                                'parent' => $ids
                            ),
                            // hiển thị mã SQL để check
                            'show_query' => 1,
                            // trả về câu query để sử dụng cho mục đích khác
                            // 'get_query' => 1,
                            // mặc định sẽ remove các field không có trong bảng, nếu muốn bỏ qua chức năng này thì kích hoạt no_remove_field
                            //'no_remove_field' => 1
                        ]);
                    }
                }
            }
            // $result = ob_get_contents();
            file_put_contents($file_log, ob_get_contents(), FILE_APPEND);
            ob_end_clean();

            // daidq (2022-03-04): chức năng này đang hoạt động không đúng -> vòng lặp nó sẽ chạy mãi do i++ hoài
            if (1 > 2) {
                foreach ($arr_update_db as $v) {
                    echo $v . '<br>' . PHP_EOL;
                    file_put_contents($file_log, $v . PHP_EOL, FILE_APPEND);
                    continue;

                    //
                    if ($this->base_model->MY_query($v)) {
                        echo 'OK! RUN query... <br>' . PHP_EOL;
                    } else {
                        echo 'Query failed! Please re-check query <br>' . PHP_EOL;
                    }
                }
            }
        }

        //
        $this->teamplate_admin['content'] = view('vadmin/dev/server_info', array(
            'all_cookie' => $_COOKIE,
            'all_session' => $_SESSION,
            'data' => $_SERVER,
            'content_log' => is_file($file_log) ? file_get_contents($file_log) : '',
        ));
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
    }

    // hiển thị thông tin php để check
    public function php_info()
    {
        $this->teamplate_admin['content'] = view('vadmin/dev/php_info', array());
        return view('vadmin/admin_teamplate', $this->teamplate_admin);
    }
    public function php2_info()
    {
        die(phpinfo());
    }
}
