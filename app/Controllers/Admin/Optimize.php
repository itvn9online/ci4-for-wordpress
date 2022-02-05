<?php
namespace App\ Controllers\ Admin;

class Optimize extends Admin {
    public function __construct() {
        parent::__construct();
    }

    // sử dụng khi cần nén lại các file tĩnh bằng cách thủ công
    public function index() {
        // tính năng này không hoạt động trên localhost
        if ( strpos( $_SERVER[ 'HTTP_HOST' ], 'localhost' ) === false ) {
            // tạo các file txt xác nhận quá trình optimize
            $c = 'Nếu tồn tại file này -> sẽ kích hoạt lệnh optimize file CSS hoặc JS trong thư mục tương ứng';
            $f = 'active-optimize.txt';

            //
            $this->base_model->_eb_create_file( PUBLIC_PUBLIC_PATH . 'css/' . $f, $c, [
                'set_permission' => DEFAULT_FILE_PERMISSION,
            ] );
            $this->base_model->_eb_create_file( PUBLIC_PUBLIC_PATH . 'javascript/' . $f, $c, [
                'set_permission' => DEFAULT_FILE_PERMISSION,
            ] );
            $this->base_model->_eb_create_file( THEMEPATH . 'css/' . $f, $c, [
                'set_permission' => DEFAULT_FILE_PERMISSION,
            ] );
            $this->base_model->_eb_create_file( THEMEPATH . 'js/' . $f, $c, [
                'set_permission' => DEFAULT_FILE_PERMISSION,
            ] );

            // bắt đầu optimize
            $this->optimize_css_js();
        }

        //
        $this->teamplate_admin[ 'content' ] = view( 'admin/optimize_view', [] );
        return view( 'admin/admin_teamplate', $this->teamplate_admin );
    }

    protected function optimize_css_js() {
        // tính năng này không hoạt động trên localhost
        if ( strpos( $_SERVER[ 'HTTP_HOST' ], 'localhost' ) !== false ) {
            return false;
        }

        // css, js chung
        $this->optimize_action_css( PUBLIC_PUBLIC_PATH );
        $this->optimize_action_js( PUBLIC_PUBLIC_PATH, 'javascript' );

        // css, js của từng theme
        if ( $this->optimize_action_css( THEMEPATH ) === true ) {
            // riêng với CSS thì còn thừa file style.css của theme -> sinh ra đoạn này để xử lý nó
            $filename = THEMEPATH . 'style.css';
            if ( file_exists( $filename ) ) {
                $c = $this->WGR_remove_css_multi_comment( file_get_contents( $filename, 1 ) );
                if ( $c !== false ) {
                    echo $filename . ':' . __FUNCTION__ . ':' . __LINE__ . '<br>' . PHP_EOL;
                    $c = trim( $c );
                    if ( !empty( $c ) ) {
                        $this->base_model->_eb_create_file( $filename, $c, [ 'ftp' => 1 ] );
                    }
                }
            }
        }
        $this->optimize_action_js( THEMEPATH );
    }

    private function optimize_action_css( $path, $dir = 'css', $type = 'css' ) {
        if ( $this->check_active_optimize( $path . $dir . '/' ) !== true ) {
            return false;
        }
        echo $path . '<br>' . PHP_EOL;

        //
        foreach ( glob( $path . $dir . '/*.' . $type ) as $filename ) {
            $c = $this->WGR_remove_css_multi_comment( file_get_contents( $filename, 1 ) );
            //var_dump( $c );
            if ( $c === false ) {
                echo 'continue (' . basename( $filename ) . ') <br>' . PHP_EOL;
                continue;
            }
            echo $filename . ':' . __FUNCTION__ . ':' . __LINE__ . '<br>' . PHP_EOL;

            //
            $c = trim( $c );
            if ( !empty( $c ) ) {
                $this->base_model->_eb_create_file( $filename, $c, [ 'ftp' => 1 ] );
            }
        }

        //
        return true;
    }

    private function optimize_action_js( $path, $dir = 'js', $type = 'js' ) {
        if ( $this->check_active_optimize( $path . $dir . '/' ) !== true ) {
            return false;
        }
        echo $path . '<br>' . PHP_EOL;

        //
        foreach ( glob( $path . $dir . '/*.' . $type ) as $filename ) {
            $c = $this->WGR_update_core_remove_js_comment( file_get_contents( $filename, 1 ) );
            if ( $c === false ) {
                echo 'continue (' . basename( $filename ) . ') <br>' . PHP_EOL;
                continue;
            }
            echo $filename . ':' . __FUNCTION__ . ':' . __LINE__ . '<br>' . PHP_EOL;

            //
            if ( !empty( $c ) ) {
                $this->base_model->_eb_create_file( $filename, $c, [ 'ftp' => 1 ] );
            }
        }
    }

    // kiểm tra xem có sự tồn tại của file kích hoạt chế độ optimize không
    private function check_active_optimize( $path ) {
        //echo $path . 'active-optimize.txt <br>' . "\n";
        $full_path = $path . 'active-optimize.txt';
        if ( file_exists( $full_path ) ) {
            // thử xóa file optimize -> XÓA được thì mới trả về true -> đảm bảo có quyền chỉnh sửa các file trong này
            if ( $this->MY_unlink( $full_path ) ) {
                return true;
            }
        }
        return false;
    }

    // optimize cho file css
    private function WGR_remove_css_multi_comment( $a ) {
        $a = explode( '*/', $a );
        $str = '';
        foreach ( $a as $v ) {
            $v = explode( '/*', $v );
            $str .= $v[ 0 ];
        }

        //
        $a = explode( "\n", $str );
        if ( count( $a ) < 10 ) {
            return false;
        }
        //echo 'count a: ' . count( $a ) . '<br>' . "\n";
        $str = '';
        foreach ( $a as $v ) {
            $v = trim( $v );
            if ( $v != '' ) {
                $str .= $v;
            }
        }

        // bỏ các ký tự thừa nhiều nhất có thể
        $str = str_replace( '; }', '}', $str );
        $str = str_replace( ';}', '}', $str );
        $str = str_replace( ' { ', '{', $str );
        $str = str_replace( ' {', '{', $str );
        $str = str_replace( ', .', ',.', $str );
        $str = str_replace( ', #', ',#', $str );
        $str = str_replace( ': ', ':', $str );
        $str = str_replace( '} .', '}.', $str );

        //
        return $str;
    }

    private function WGR_update_core_remove_js_comment( $a ) {
        $a = $this->WGR_remove_js_comment( $a );
        if ( $a === false ) {
            return false;
        }
        $a = $this->_eb_str_text_fix_js_content( $a );
        //$a = $this->WGR_remove_js_multi_comment( $a );

        return trim( $a );
    }

    private function WGR_remove_js_comment( $a, $chim = false ) {
        $a = explode( "\n", $a );
        if ( count( $a ) < 10 ) {
            return false;
        }

        $str = '';
        foreach ( $a as $v ) {
            $v = trim( $v );

            if ( $v == '' || substr( $v, 0, 2 ) == '//' ) {} else {
                // thêm dấu xuống dòng với 1 số trường hợp
                if ( $chim == true || strpos( $v, '//' ) !== false || substr( $v, -1 ) == '\\' ) {
                    $v .= "\n";
                }
                $str .= $v;
            }
        }

        // loại bỏ khoảng trắng
        $arr = array(
            ' ( ' => '(',
            ' ) ' => ')',
            '( \'' => '(\'',
            '\' )' => '\')',

            '\' + ' => '\'+',
            ' + \'' => '+\'',

            ' == ' => '==',
            ' != ' => '!=',
            ' || ' => '||',
            ' === ' => '===',

            ' () ' => '()',
            ' && ' => '&&',
            '\' +\'' => '\'+\'',
            ' += ' => '+=',
            '+ \'' => '+\'',
            '; i < ' => ';i<',
            'var i = 0;' => 'var i=0;',
            '; i' => ';i',
            ' = \'' => '=\''
        );

        foreach ( $arr as $k => $v ) {
            $str = str_replace( $k, $v, $str );
        }

        //
        return $str;
    }

    private function _eb_str_text_fix_js_content( $str ) {
        if ( $str == '' ) {
            return '';
        }

        //	$str = iconv('UTF-16', 'UTF-8', $str);
        //	$str = mb_convert_encoding($str, 'UTF-8', 'UTF-16');
        //	$str = mysqli_escape_string($str);
        //	$str = htmlentities($str, ENT_COMPAT, 'UTF-16');
        $arr = $this->_eb_arr_block_fix_content();

        //
        foreach ( $arr as $k => $v ) {
            if ( $v != '' ) {
                $str = str_replace( $k, $v, $str );
            }
        }
        return $str;
    }

    private function WGR_remove_js_multi_comment( $a, $begin = '/*', $end = '*/' ) {

        $str = $a;

        $b = explode( $begin, $a );
        $a = explode( $end, $a );

        // nếu số thẻ đóng với thẻ mở khác nhau -> hủy luôn
        if ( count( $a ) != count( $b ) ) {
            return $str;
            //		return _eb_str_block_fix_content( $str );
        }

        //
        $str = '';

        //
        foreach ( $a as $v ) {
            $v = explode( $begin, $v );
            $str .= $v[ 0 ];
        }

        return $str;
        //	return _eb_str_block_fix_content( $str );
    }

    private function _eb_arr_block_fix_content() {
        // https://www.google.com/search?q=site:charbase.com+%E1%BB%9D#q=site:charbase.com+%E1%BA%A3
        return array(
            'á' => '\u00e1',
            'à' => '\u00e0',
            'ả' => '\u1ea3',
            'ã' => '\u00e3',
            'ạ' => '\u1ea1',
            'ă' => '\u0103',
            'ắ' => '\u1eaf',
            'ặ' => '\u1eb7',
            'ằ' => '\u1eb1',
            'ẳ' => '\u1eb3',
            'ẵ' => '\u1eb5',
            'â' => '\u00e2',
            'ấ' => '\u1ea5',
            'ầ' => '\u1ea7',
            'ẩ' => '\u1ea9',
            'ẫ' => '\u1eab',
            'ậ' => '\u1ead',
            'Á' => '\u00c1',
            'À' => '\u00c0',
            'Ả' => '\u1ea2',
            'Ã' => '\u00c3',
            'Ạ' => '\u1ea0',
            'Ă' => '\u0102',
            'Ắ' => '\u1eae',
            'Ặ' => '\u1eb6',
            'Ằ' => '\u1eb0',
            'Ẳ' => '\u1eb2',
            'Ẵ' => '\u1eb4',
            'Â' => '\u00c2',
            'Ấ' => '\u1ea4',
            'Ầ' => '\u1ea6',
            'Ẩ' => '\u1ea8',
            'Ẫ' => '\u1eaa',
            'Ậ' => '\u1eac',
            'đ' => '\u0111',
            'Đ' => '\u0110',
            'é' => '\u00e9',
            'è' => '\u00e8',
            'ẻ' => '\u1ebb',
            'ẽ' => '\u1ebd',
            'ẹ' => '\u1eb9',
            'ê' => '\u00ea',
            'ế' => '\u1ebf',
            'ề' => '\u1ec1',
            'ể' => '\u1ec3',
            'ễ' => '\u1ec5',
            'ệ' => '\u1ec7',
            'É' => '\u00c9',
            'È' => '\u00c8',
            'Ẻ' => '\u1eba',
            'Ẽ' => '\u1ebc',
            'Ẹ' => '\u1eb8',
            'Ê' => '\u00ca',
            'Ế' => '\u1ebe',
            'Ề' => '\u1ec0',
            'Ể' => '\u1ec2',
            'Ễ' => '\u1ec4',
            'Ệ' => '\u1ec6',
            'í' => '\u00ed',
            'ì' => '\u00ec',
            'ỉ' => '\u1ec9',
            'ĩ' => '\u0129',
            'ị' => '\u1ecb',
            'Í' => '\u00cd',
            'Ì' => '\u00cc',
            'Ỉ' => '\u1ec8',
            'Ĩ' => '\u0128',
            'Ị' => '\u1eca',
            'ó' => '\u00f3',
            'ò' => '\u00f2',
            'ỏ' => '\u1ecf',
            'õ' => '\u00f5',
            'ọ' => '\u1ecd',
            'ô' => '\u00f4',
            'ố' => '\u1ed1',
            'ồ' => '\u1ed3',
            'ổ' => '\u1ed5',
            'ỗ' => '\u1ed7',
            'ộ' => '\u1ed9',
            'ơ' => '\u01a1',
            'ớ' => '\u1edb',
            'ờ' => '\u1edd',
            'ở' => '\u1edf',
            'ỡ' => '\u1ee1',
            'ợ' => '\u1ee3',
            'Ó' => '\u00d3',
            'Ò' => '\u00d2',
            'Ỏ' => '\u1ece',
            'Õ' => '\u00d5',
            'Ọ' => '\u1ecc',
            'Ô' => '\u00d4',
            'Ố' => '\u1ed0',
            'Ồ' => '\u1ed2',
            'Ổ' => '\u1ed4',
            'Ỗ' => '\u1ed6',
            'Ộ' => '\u1ed8',
            'Ơ' => '\u01a0',
            'Ớ' => '\u1eda',
            'Ờ' => '\u1edc',
            'Ở' => '\u1ede',
            'Ỡ' => '\u1ee0',
            'Ợ' => '\u1ee2',
            'ú' => '\u00fa',
            'ù' => '\u00f9',
            'ủ' => '\u1ee7',
            'ũ' => '\u0169',
            'ụ' => '\u1ee5',
            'ư' => '\u01b0',
            'ứ' => '\u1ee9',
            'ừ' => '\u1eeb',
            'ử' => '\u1eed',
            'ữ' => '\u1eef',
            'ự' => '\u1ef1',
            'Ú' => '\u00da',
            'Ù' => '\u00d9',
            'Ủ' => '\u1ee6',
            'Ũ' => '\u0168',
            'Ụ' => '\u1ee4',
            'Ư' => '\u01af',
            'Ứ' => '\u1ee8',
            'Ừ' => '\u1eea',
            'Ử' => '\u1eec',
            'Ữ' => '\u1eee',
            'Ự' => '\u1ef0',
            'ý' => '\u00fd',
            'ỳ' => '\u1ef3',
            'ỷ' => '\u1ef7',
            'ỹ' => '\u1ef9',
            'ỵ' => '\u1ef5',
            'Ý' => '\u00dd',
            'Ỳ' => '\u1ef2',
            'Ỷ' => '\u1ef6',
            'Ỹ' => '\u1ef8',
            'Ỵ' => '\u1ef4'
        );
    }
}