<?php
namespace App\ Controllers\ Admin;

//
class Dev extends Admin {
    public function __construct() {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision( __CLASS__ );
    }

    public function index() {
        return $this->server_info();
    }

    public function server_info() {
        /*
         * db không cần update liên tục, nếu cần thì clear cache để tái sử dụng
         */
        $has_update = $this->cache->get( __FUNCTION__ );
        if ( $has_update === NULL ) {
            /*
             * một số lệnh thay đổi dữ liệu thủ công
             */
            $arr_update_db = [
                'UPDATE `' . WGR_TABLE_PREFIX . 'term_taxonomy` SET `term_level` = 0 WHERE `parent` = 0',
                'UPDATE `' . WGR_TABLE_PREFIX . 'term_taxonomy` SET `term_level` = 1 WHERE `parent` > 0 AND `parent` IN (SELECT `term_id` FROM `' . WGR_TABLE_PREFIX . 'term_taxonomy` WHERE `parent` = 0)',
            ];
            // lấy term level cao nhất để for
            $high_level = $this->base_model->select( 'term_level', 'term_taxonomy', [], [
                'order_by' => array(
                    'term_level' => 'DESC'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                //'offset' => 2,
                'limit' => 1
            ] );
            //print_r( $high_level );
            if ( !empty( $high_level ) ) {
                for ( $i = 1; $i < $high_level[ 'term_level' ] + 1; $i++ ) {
                    $arr_update_db[] = 'UPDATE `' . WGR_TABLE_PREFIX . 'term_taxonomy` SET `term_level` = ' . ( $i + 1 ) . ' WHERE `parent` > 0 AND `parent` IN (SELECT `term_id` FROM `' . WGR_TABLE_PREFIX . 'term_taxonomy` WHERE term_level = ' . $i . ')';
                }
            }
            // daidq (2022-03-04): chức năng này đang hoạt động không đúng -> vòng lặp nó sẽ chạy mãi do i++ hoài
            foreach ( $arr_update_db as $v ) {
                echo $v . '<br>' . "\n";

                //
                if ( $this->base_model->MY_query( $v ) ) {
                    echo 'OK! RUN query... <br>' . "\n";
                } else {
                    echo 'Query failed! Please re-check query <br>' . "\n";
                }
            }

            //
            $this->cache->save( __FUNCTION__, time(), MEDIUM_CACHE_TIMEOUT );
        }


        $this->teamplate_admin[ 'content' ] = view( 'admin/dev/server_info', array(
            'all_cookie' => $_COOKIE,
            'all_session' => $_SESSION,
            'data' => $_SERVER,
        ) );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }
}