<?php

namespace App\ Models;

//use CodeIgniter\ Model;

class Base extends Session {
    public function __construct() {
        $this->db = \Config\ Database::connect();
    }

    public $default_post_type = 'post';
    public $default_taxonomy = 'category';

    function insert( $table, $data, $remove_col = false, $queryType = '' ) {
        if ( $remove_col === true ) {
            $data = $this->removeInvalidField( $data, $table );

            //
            if ( empty( $data ) ) {
                die( 'data insert empty ' . $table . ':' . basename( __FILE__ ) . ':' . __FUNCTION__ . ':' . __LINE__ );
            }
        }

        //
        $builder = $this->db->table( $table );
        if ( $queryType == 'ignore' ) {
            $builder->ignore( true )->insert( $data );
        } else if ( $queryType == 'getQuery' ) {
            echo $builder->set( $data )->getCompiledInsert();
            return false;
        } else if ( $queryType == 'replace' ) {
            $builder->replace( $data );
        } else {
            $builder->insert( $data );
        }
        //die(' bb'); // lỗi sẽ hiển thị ở đây khi không insert đc
        if ( $this->db->affectedRows() ) {
            //var_dump( $this->db->affectedRows() );
            //echo $this->db->insertID() . '<br>' . "\n";
            return $this->db->insertID();
            /*
        } else {
            print_r( $this->db->error() );
            print_r( $this->db->_error_message() );
            */
        }
        return false;
    }

    // update giá trị cho 1 cột nào đó lên 1 đơn vị. Ví dụ: update lượt xem
    function update_count( $table, $col, $where_array, $ops = [] ) {
        $builder = $this->db->table( $table );
        $builder->set( $col, $col . '+1', false );
        foreach ( $where_array as $key => $value ) {
            $builder->where( $key, $value );
        }
        $builder->update();
    }

    function update_multiple( $table, $data, $where_array, $ops = [] ) {
        if ( empty( $where_array ) ) {
            if ( isset( $ops[ 'debug_backtrace' ] ) ) {
                echo $ops[ 'debug_backtrace' ] . '<br>' . PHP_EOL;
            }
            echo debug_backtrace()[ 1 ][ 'class' ] . '\\ ' . debug_backtrace()[ 1 ][ 'function' ] . '<br>' . PHP_EOL;

            //
            die( 'data update empty ' . $table . ':' . __FUNCTION__ . ':line:' . __LINE__ );
        }
        //print_r( $where_array );

        //
        $builder = $this->db->table( $table );

        //
        foreach ( $where_array as $key => $value ) {
            $builder->where( $key, $value );
        }

        //print_r( $data );
        $data = $this->removeInvalidField( $data, $table );
        //print_r( $data );
        if ( empty( $data ) ) {
            if ( isset( $ops[ 'debug_backtrace' ] ) ) {
                echo $ops[ 'debug_backtrace' ] . '<br>' . PHP_EOL;
            }
            echo debug_backtrace()[ 1 ][ 'class' ] . '\\ ' . debug_backtrace()[ 1 ][ 'function' ] . '<br>' . PHP_EOL;

            //
            die( __FUNCTION__ . ' data update empty ' . $table . ':line:' . __LINE__ );
        }
        $builder->update( $data );

        // in luôn ra query để test
        /*
        if ( isset( $op[ 'show_query' ] ) ) {
            print_r( $this->db->getLastQuery()->getQuery() );
            echo '<br>' . PHP_EOL;
        }

        // trả về query để sử dụng cho mục đích khác
        if ( isset( $op[ 'get_query' ] ) ) {
            return $this->db->getLastQuery()->getQuery();
        }
        */

        if ( $this->db->affectedRows() > 0 ) {
            return true;
        }
        if ( !$this->query_error( $this->db->error() ) ) {
            print_r( $this->db->error() );
        }
        return false;
    }

    function update( $table, $data, $where, $id ) {
        // ép buộc sử dụng update_multiple
        return $this->update_multiple( $table, $data, [
            $where => $id
        ], [
            'debug_backtrace' => debug_backtrace()[ 1 ][ 'function' ]
        ] );
    }

    function removeInvalidField( $items, $tbl_name, $disableFields = array() ) {
        if ( !is_array( $items ) ) {
            return [];
        }
        $column_name = $this->db->getFieldNames( $tbl_name ); // list ra những cột có trong table
        //print_r( $column_name );
        //die( __FILE__ . ':' . __LINE__ );

        foreach ( $items as $key => $value ) { //lặp lại cột lấy giá trị mà người dùng đẩy lên table: $item = array('newstitle'=>'tin tuc hom nay', 'slug'=>'tin-tuc-hom-nay'...)
            if ( !in_array( $key, $column_name ) ) { // kiểm tra xem những giá trị trường đẩy lên có trùng với cột trong table ở csdl k
                unset( $items[ $key ] ); // nếu không trùng thì loại bỏ những cột mà người dùng đẩy lên
            }
        }
        // remove disallow fields
        foreach ( $disableFields as $key => $value ) { //kiểm tra những field nào không đc phép cập nhật thì loại bỏ
            if ( in_array( $value, $column_name ) ) { // kiểm tra nếu tồn tại những cột được disable
                unset( $items[ $value ] ); // nếu tồn tại những cột đã disable thì sẽ remove.
            }
        }

        return $items;
    }

    public function delete_multiple( $table, $where, $ops = [] ) {
        if ( empty( $where ) ) {
            if ( isset( $ops[ 'debug_backtrace' ] ) ) {
                echo $ops[ 'debug_backtrace' ] . '<br>' . PHP_EOL;
            }
            echo debug_backtrace()[ 1 ][ 'class' ] . '\\ ' . debug_backtrace()[ 1 ][ 'function' ] . '<br>' . PHP_EOL;

            //
            die( __FUNCTION__ . ' where update empty ' . $table . ':line:' . __LINE__ );
            return false;
        }

        //
        $builder = $this->db->table( $table );
        foreach ( $where as $k => $v ) {
            $builder->where( $k, $v );
        }
        $builder->delete();
        if ( $this->db->affectedRows() ) {
            return true;
        }
        if ( !$this->query_error( $this->db->error() ) ) {
            print_r( $this->db->error() );
        }
        return false;
    }

    public function delete( $table, $where, $id ) {
        return $this->delete_multiple( $table, [
            $where => $id
        ], [
            'debug_backtrace' => debug_backtrace()[ 1 ][ 'function' ]
        ] );

        //
        $builder = $this->db->table( $table );
        $builder->where( $where, $id );
        $builder->delete();
        if ( $this->db->affectedRows() ) {
            return true;
        }
        return false;
    }

    // trả về các cột dữ liệu mặc định trong 1 bảng
    function default_data( $table, $other_table = [] ) {
        $column_name = $this->db->getFieldNames( $table ); // list ra những cột có trong table

        $result = [];
        foreach ( $column_name as $v ) {
            $result[ $v ] = '';
        }

        //
        foreach ( $other_table as $table2 ) {
            if ( $table2 != '' ) {
                $column_name = $this->db->getFieldNames( $table2 );
                foreach ( $column_name as $v ) {
                    $result[ $v ] = '';
                }
            }
        }

        return $result;
    }

    public function table_exists( $tbl ) {
        if ( $this->db->tableExists( $tbl ) ) {
            return true;
        }
        return false;
    }

    // tự tạo 1 hàm select riêng, viết kiểu code cũ, mỗi lần select lại phải viết function khác -> mệt
    function select( $select, $from, $where = array(), $op = array() ) {
        //print_r($op);

        //
        $builder = $this->db->table( $from );
        $builder->select( $select );

        if ( isset( $op[ 'join' ] ) ) {
            foreach ( $op[ 'join' ] as $k => $v ) {
                $builder->join( $k, $v, 'inner' );
            }
        }
        if ( isset( $op[ 'left_join' ] ) ) {
            foreach ( $op[ 'left_join' ] as $k => $v ) {
                $builder->join( $k, $v, 'left' );
            }
        }
        if ( isset( $op[ 'right_join' ] ) ) {
            foreach ( $op[ 'right_join' ] as $k => $v ) {
                $builder->join( $k, $v, 'right' );
            }
        }
        /*
        if ( isset( $op[ 'full_join' ] ) ) {
            foreach ( $op[ 'full_join' ] as $k => $v ) {
                $builder->join( $k, $v, 'full' );
            }
        }
        */
        /*
        if ( isset( $op[ 'self_join' ] ) ) {
            foreach ( $op[ 'self_join' ] as $k => $v ) {
                $builder->join( $k, $v, 'self' );
            }
        }
        */
        // điều kiện lấy dữ liệu
        foreach ( $where as $k => $v ) {
            if ( $v === NULL ) {
                $builder->where( $k, NULL, FALSE );
            } else {
                $builder->where( $k, $v );
            }
        }


        // các thông số tùy chỉnh khác
        // or where
        if ( isset( $op[ 'or_where' ] ) && !empty( $op[ 'or_where' ] ) ) {
            //$and_or = array();
            $builder->groupStart();
            foreach ( $op[ 'or_where' ] as $k => $v ) {
                //$and_or[] = $k . ' = ' . $v;
                /*
                if ( $k == 0 ) {
                	$this->db->where( $k, $v );
                }
                else {
                */
                $builder->orWhere( $k, $v );
                //}
            }
            $builder->groupEnd();
            //print_r($and_or);
        }

        // where in
        if ( isset( $op[ 'where_in' ] ) ) {
            foreach ( $op[ 'where_in' ] as $k => $v ) {
                if ( !empty( $v ) ) {
                    $builder->whereIn( $k, $v );
                }
            }
        }

        // where not in
        if ( isset( $op[ 'where_not_in' ] ) ) {
            foreach ( $op[ 'where_not_in' ] as $k => $v ) {
                if ( !empty( $v ) ) {
                    $builder->whereNotIn( $k, $v );
                }
            }
        }

        // like
        if ( isset( $op[ 'like' ] ) ) {
            foreach ( $op[ 'like' ] as $k => $v ) {
                $len = strlen( $v );
                // từ 3 ký tự trở lên sẽ tìm theo dạng '%tu-khoa%'
                if ( $len > 2 ) {
                    $builder->like( $k, $v );
                }
                // từ 3 ký tự trở xuống sẽ tìm theo dạng 'tu-khoa%' -> bắt đầu bằng
                else if ( $len > 0 ) {
                    $builder->like( $k, $v, 'after' );
                }
            }
        }
        // like before
        if ( isset( $op[ 'like_before' ] ) ) {
            foreach ( $op[ 'like_before' ] as $k => $v ) {
                if ( $v != '' ) {
                    // Produces: WHERE `title` LIKE '%match' ESCAPE '!'
                    $builder->like( $k, $v, 'before' );
                }
            }
        }
        // like after
        if ( isset( $op[ 'like_after' ] ) ) {
            foreach ( $op[ 'like_after' ] as $k => $v ) {
                if ( $v != '' ) {
                    // Produces: WHERE `title` LIKE 'match%' ESCAPE '!'
                    $builder->like( $k, $v, 'after' );
                }
            }
        }
        // not like
        if ( isset( $op[ 'not_like' ] ) ) {
            foreach ( $op[ 'not_like' ] as $k => $v ) {
                $len = strlen( $v );
                // từ 3 ký tự trở lên sẽ tìm theo dạng '%tu-khoa%'
                if ( $len > 2 ) {
                    $builder->notLike( $k, $v );
                }
                // từ 3 ký tự trở xuống sẽ tìm theo dạng 'tu-khoa%' -> bắt đầu bằng
                else if ( $len > 0 ) {
                    $builder->notLike( $k, $v, 'after' );
                }
            }
        }
        // or like
        if ( isset( $op[ 'or_like' ] ) && !empty( $op[ 'or_like' ] ) ) {
            $builder->groupStart();
            foreach ( $op[ 'or_like' ] as $k => $v ) {
                $len = strlen( $v );
                // từ 3 ký tự trở lên sẽ tìm theo dạng '%tu-khoa%'
                if ( $len > 2 ) {
                    $builder->orLike( $k, $v );
                }
                // từ 3 ký tự trở xuống sẽ tìm theo dạng 'tu-khoa%' -> bắt đầu bằng
                else if ( $len > 0 ) {
                    $builder->orLike( $k, $v, 'after' );
                }
            }
            $builder->groupEnd();
        }
        // or not like
        if ( isset( $op[ 'or_not_like' ] ) && !empty( $op[ 'or_not_like' ] ) ) {
            $builder->groupStart();
            foreach ( $op[ 'or_not_like' ] as $k => $v ) {
                $len = strlen( $v );
                // từ 3 ký tự trở lên sẽ tìm theo dạng '%tu-khoa%'
                if ( $len > 2 ) {
                    $builder->orNotLike( $k, $v );
                }
                // từ 3 ký tự trở xuống sẽ tìm theo dạng 'tu-khoa%' -> bắt đầu bằng
                else if ( $len > 0 ) {
                    $builder->orNotLike( $k, $v, 'after' );
                }
            }
            $builder->groupEnd();
        }

        // group by
        if ( isset( $op[ 'group_by' ] ) ) {
            foreach ( $op[ 'group_by' ] as $k => $v ) {
                $builder->groupBy( $v );
            }
        }

        // order by
        if ( isset( $op[ 'order_by' ] ) ) {
            foreach ( $op[ 'order_by' ] as $k => $v ) {
                $builder->orderBy( $k, $v );
            }
        }

        // offset -> limit
        if ( !isset( $op[ 'offset' ] ) || $op[ 'offset' ] < 0 ) {
            $op[ 'offset' ] = 0;
        }
        //print_r($op);
        /*
        if ( isset( $op[ 'limit' ] ) && $op[ 'limit' ] > 0 ) {
            $builder->limit( $op[ 'limit' ], $op[ 'offset' ] );
        }
        */
        // daidq (2021-12-25): để tránh trường hợp select unlimit cho dữ liệu lớn -> đặt mặc định lệnh LIMIT nếu không được chỉ định
        if ( !isset( $op[ 'limit' ] ) || $op[ 'limit' ] === 0 ) {
            //echo 'auto limit <br>' . "\n";
            $op[ 'limit' ] = 500;
        }
        if ( $op[ 'limit' ] > 0 ) {
            $builder->limit( $op[ 'limit' ], $op[ 'offset' ] );
        }

        //
        /*
        if ( isset( $op[ 'get_query' ] ) ) {
            // trên CI3 có thể sử dụng hàm này
            echo $this->db->get_compiled_select() . '<br>' . PHP_EOL;
        }
        */

        // trả về kết quả
        $a = array();
        $query = $builder->get();

        //
        if ( !$this->query_error( $this->db->error() ) ) {
            print_r( $this->db->error() );
        }

        // in luôn ra query để test
        if ( isset( $op[ 'show_query' ] ) ) {
            print_r( $this->db->getLastQuery()->getQuery() );
            echo '<br>' . PHP_EOL;
        }

        // trả về query để sử dụng cho mục đích khác
        if ( isset( $op[ 'get_query' ] ) ) {
            return $this->db->getLastQuery()->getQuery();
        }


        //print_r( $query );
        //print_r( $this->db->_error_message() );
        //if ( $builder->countAllResults() > 0 ) {
        $a = $query->getResultArray();
        //print_r( $a );

        // nếu chỉ lấy 1 kết quả -> trả về luôn mảng số 0
        if ( isset( $op[ 'limit' ] ) && $op[ 'limit' ] === 1 && !empty( $a ) ) {
            //echo $op[ 'limit' ] . '<br>' . "\n";
            //echo $builder->countAllResults() . '<br>' . "\n";
            //print_r( $a );
            //print_r( $a[ 0 ] );
            //die( 'fgjhkgsd gsdfgsgs' );
            $a = $a[ 0 ];
        }
        //}
        //print_r( $a );
        return $a;
    }

    function query_error( $arr ) {
        if ( $arr[ 'code' ] > 0 ) {
            return false;
        }
        return true;
    }

    function MY_query( $sql ) {
        return $this->db->query( $sql );
    }

    // nạp CSS, JS để tránh phải bấm Ctrl + F5
    function get_add_css( $f, $get_content = false, $attr = [], $preload = false ) {
        $f = str_replace( PUBLIC_PUBLIC_PATH, '', $f );
        $f = ltrim( $f, '/' );
        //echo $f . '<br>' . "\n";
        if ( !file_exists( PUBLIC_PUBLIC_PATH . $f ) ) {
            return '<!-- ' . $f . ' not exist! -->';
        }
        if ( $get_content === true ) {
            return '<style>' . file_get_contents( $f, 1 ) . '</style>' . PHP_EOL;
        }
        if ( $preload == true ) {
            $rel = 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"';
        } else {
            $rel = 'rel="stylesheet" type="text/css" media="all"';
        }
        return '<link ' . $rel . ' href="' . $f . '?v=' . filemtime( PUBLIC_PUBLIC_PATH . $f ) . '"' . implode( ' ', $attr ) . ' />';
    }
    // chế độ nạp css thông thường
    function add_css( $f, $get_content = false, $attr = [], $preload = false ) {
        echo $this->get_add_css( $f, $get_content, $attr, $preload ) . PHP_EOL;
    }
    // chế độ nạp trước css
    function preload_css( $f ) {
        echo $this->get_add_css( $f, false, [], true ) . PHP_EOL;
    }

    function get_add_js( $f, $get_content = false, $attr = [] ) {
        $f = str_replace( PUBLIC_PUBLIC_PATH, '', $f );
        $f = ltrim( $f, '/' );
        //echo $f . '<br>' . "\n";
        if ( !file_exists( PUBLIC_PUBLIC_PATH . $f ) ) {
            return '<!-- ' . $f . ' not exist! -->';
        }
        if ( $get_content === true ) {
            return '<script type="text/javascript">' . file_get_contents( $f, 1 ) . '</script>';
        }
        //print_r( $attr );
        return '<script type="text/javascript" src="' . $f . '?v=' . filemtime( PUBLIC_PUBLIC_PATH . $f ) . '"' . implode( ' ', $attr ) . '></script>';
    }
    // thêm 1 file
    function add_js( $f, $get_content = false, $attr = [] ) {
        echo $this->get_add_js( $f, $get_content, $attr ) . PHP_EOL;
    }
    // thêm nhiều file cùng 1 thuộc tính
    function adds_js( $fs, $get_content = false, $attr = [] ) {
        foreach ( $fs as $f ) {
            echo $this->get_add_js( $f, $get_content, $attr ) . PHP_EOL;
        }
    }


    function alert( $m, $lnk = '' ) {
        $arr_debug = debug_backtrace();
        //print_r($arr_debug);

        die( '<script>
        console.log("' . basename( $arr_debug[ 1 ][ 'file' ] ) . ':' . $arr_debug[ 1 ][ 'line' ] . '");
        console.log("function: ' . $arr_debug[ 1 ][ 'function' ] . '");
        console.log("class: ' . basename( str_replace( '\\', '/', $arr_debug[ 1 ][ 'class' ] ) ) . '");
        
        //
		var m = "' . $m . '";
		var lnk = "' . $lnk . '";
		try {
			if (top != self) {
				top.WGR_alert(m, lnk);
			} else {
				WGR_alert(m, lnk);
			}
		} catch (e) {
			console.log(\'name: \' + e.name + \'; line: \' + (e.lineNumber || e.line) + \'; script: \' + (e.fileName || e.sourceURL || e.script) + \'; stack: \' + (e.stackTrace || e.stack) + \'; message: \' + e.message);
			if ( m != "" ) {
				alert(m);
			}
			
			//
			if (typeof lnk != \'undefined\' && lnk != \'\') {
				if (top != self) {
					top.window.location = lnk;
				} else {
					window.location = lnk;
				}
			}
		}
		</script>' );
    }

    //
    function _eb_non_mark_seo_v2( $str ) {
        // Chuyển đổi toàn bộ chuỗi sang chữ thường
        if ( function_exists( 'mb_convert_case' ) ) {
            $str = mb_convert_case( trim( $str ), MB_CASE_LOWER, "UTF-8" );
        }

        //Tạo mảng chứa key và chuỗi regex cần so sánh
        $unicode = array(
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            '-' => '\+|\*|\/|\&|\!| |\^|\%|\$|\#|\@'
        );

        foreach ( $unicode as $key => $value ) {
            //So sánh và thay thế bằng hàm preg_replace
            $str = preg_replace( "/($value)/", $key, $str );
        }
        $str = ltrim( $str, '-' );
        $str = rtrim( $str, '-' );
        $str = ltrim( $str, '.' );
        $str = rtrim( $str, '.' );
        $str = trim( $str );

        //Trả về kết quả
        return $str;
    }

    function _eb_non_mark_seo_v1( $str ) {
        $str = $this->_eb_non_mark( trim( $str ) );

        //
        $unicode = array(
            /*
            'a' => array('á','à','ả','ã','ạ','ă','ắ','ặ','ằ','ẳ','ẵ','â','ấ','ầ','ẩ','ẫ','ậ','Á','À','Ả','Ã','Ạ','Ă','Ắ','Ặ','Ằ','Ẳ','Ẵ','Â','Ấ','Ầ','Ẩ','Ẫ','Ậ'),
            'd' => array('đ','Đ'),
            'e' => array('é','è','ẻ','ẽ','ẹ','ê','ế','ề','ể','ễ','ệ','É','È','Ẻ','Ẽ','Ẹ','Ê','Ế','Ề','Ể','Ễ','Ệ'),
            'i' => array('í','ì','ỉ','ĩ','ị', 'Í','Ì','Ỉ','Ĩ','Ị'),
            'o' => array('ó','ò','ỏ','õ','ọ','ô','ố','ồ','ổ','ỗ','ộ','ơ','ớ','ờ','ở','ỡ','ợ','Ó','Ò','Ỏ','Õ','Ọ','Ô','Ố','Ồ','Ổ','Ỗ','Ộ','Ơ','Ớ','Ờ','Ở','Ỡ','Ợ'),
            'u' => array('ú','ù','ủ','ũ','ụ','ư','ứ','ừ','ử','ữ','ự','Ú','Ù','Ủ','Ũ','Ụ','Ư','Ứ','Ừ','Ử','Ữ','Ự'),
            'y' => array('ý','ỳ','ỷ','ỹ','ỵ','Ý','Ỳ','Ỷ','Ỹ','Ỵ'),
            */
            '-' => array( ' ', '~', '`', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '=', '[', ']', '{', '}', '\\', '|', ';', ':', '\'', '"', ',', '<', '>', '/', '?' )
        );
        foreach ( $unicode as $nonUnicode => $uni ) {
            foreach ( $uni as $v ) {
                $str = str_replace( $v, $nonUnicode, $str );
            }
        }

        //
        return $str;
    }

    function _eb_non_mark_seo( $str ) {
        //$str = _eb_non_mark_seo_v1( $str );
        $str = $this->_eb_non_mark_seo_v2( $str );


        //	$str = urlencode($str);
        // thay thế 2- thành 1-  
        $str = preg_replace( '/-+-/', "-", $str );

        // cắt bỏ ký tự - ở đầu và cuối chuỗi
        $str = preg_replace( '/^\-+|\-+$/', "", $str );

        //
        $str = $this->_eb_text_only( $str );

        //
        return $str;
        //	return strtolower($str);
    }

    function _eb_non_mark( $str ) {
        $unicode = array(
            'a' => array( 'á', 'à', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ặ', 'ằ', 'ẳ', 'ẵ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ' ),
            'A' => array( 'Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ặ', 'Ằ', 'Ẳ', 'Ẵ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ' ),
            'd' => array( 'đ' ),
            'D' => array( 'Đ' ),
            'e' => array( 'é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ' ),
            'E' => array( 'É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ' ),
            'i' => array( 'í', 'ì', 'ỉ', 'ĩ', 'ị' ),
            'I' => array( 'Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị' ),
            'o' => array( 'ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ' ),
            'O' => array( 'Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ' ),
            'u' => array( 'ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự' ),
            'U' => array( 'Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự' ),
            'y' => array( 'ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ' ),
            'Y' => array( 'Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ' )
        );
        foreach ( $unicode as $nonUnicode => $uni ) {
            foreach ( $uni as $v ) {
                $str = str_replace( $v, $nonUnicode, $str );
            }
        }
        return $str;
    }

    function _eb_text_only( $str = '' ) {
        if ( $str == '' ) {
            return '';
        }
        return preg_replace( '/[^a-zA-Z0-9\-\.]+/', '', $str );
    }

    // trả về nội dung HTML mẫu
    function get_html_tmp( $file_name, $path = '', $sub_path = 'Views/html/', $file_type = '.html' ) {
        //echo PUBLIC_HTML_PATH . '<br>' . "\n";
        //echo APPPATH . '<br>' . "\n";
        //echo PUBLIC_HTML_PATH . APPPATH . '<br>' . "\n";

        // nếu path được chỉ định -> dùng path
        if ( $path != '' ) {
            $f = $path . $sub_path . $file_name . $file_type;
        }
        // nếu không
        else {
            // ưu tiên file trong child-theme
            $f = THEMEPATH . $sub_path . $file_name . $file_type;
            // nếu không có -> dùng trong theme mặc định
            if ( !file_exists( $f ) ) {
                $f = APPPATH . $sub_path . $file_name . $file_type;
            }
        }
        if ( !file_exists( $f ) ) {
            return 'File HTML tmp not exist #' . $file_name . $file_type;
        }
        return file_get_contents( $f, 1 );
    }
    // trả về mẫu HTML ở theme cha
    function parent_html_tmp( $file_name ) {
        return $this->get_html_tmp( $file_name, APPPATH );
    }
    // trả về mẫu HTML ở theme con
    function child_html_tmp( $file_name ) {
        return $this->get_html_tmp( $file_name, THEMEPATH );
    }

    // chuyển đổi từ mảng php sang html tương ứng
    function tmp_to_html( $tmp_html, $arr, $default_arr = [] ) {
        // meta
        $arr_meta = NULL;
        if ( isset( $arr[ 'post_meta' ] ) && $arr[ 'post_meta' ] !== '' ) {
            $arr_meta = $arr[ 'post_meta' ];
            $arr[ 'post_meta' ] = '';
        } else if ( isset( $arr[ 'term_meta' ] ) && $arr[ 'term_meta' ] !== '' ) {
            $arr_meta = $arr[ 'term_meta' ];
            $arr[ 'term_meta' ] = '';
        }

        //print_r( $arr );
        foreach ( $arr as $k => $v ) {
            // sử dụng chung mẫu template với angular js
            $tmp_html = str_replace( '{{' . $k . '}}', $v, $tmp_html );
            //$tmp_html = str_replace( '{tmp.' . $k . '}', $v, $tmp_html );
            //$tmp_html = str_replace( '%' . $k . '%', $v, $tmp_html );
        }

        // meta
        if ( $arr_meta !== NULL ) {
            $tmp_html = $this->tmp_to_html( $tmp_html, $arr_meta );
        }

        // thay các dữ liệu không có key bằng dữ liệu mặc định
        foreach ( $default_arr as $k => $v ) {
            $tmp_html = str_replace( '{{' . $k . '}}', $v, $tmp_html );
            //$tmp_html = str_replace( '{tmp.' . $k . '}', $v, $tmp_html );
            //$tmp_html = str_replace( '%' . $k . '%', $v, $tmp_html );
        }

        //
        return $tmp_html;
    }

    // daidq -> dữ liệu của bảng options -> đã chuyển sang options model -> ở base model sau này sẽ xóa đi
    function get_the_logo( $cog, $key = 'logo' ) {
        //if ( !isset( $cog->$key ) || $cog->$key == '' ) {
        if ( $cog->$key == '' ) {
            $cog->$key = $cog->logo;
        }
        return $cog->$key;
    }

    function the_logo( $cog, $key = 'logo', $logo_height = 'logo_main_height' ) {
        //if ( !isset( $cog->$logo_height ) || $cog->$logo_height == '' ) {
        if ( $cog->$logo_height == '' ) {
            $logo_height = 'logo_main_height';
        }
        if ( isset( $cog->$logo_height ) ) {
            $height = $cog->$logo_height;
        } else {
            $height = 90;
        }

        //
        echo '<a href="./" class="web-logo" style="background-image: url(\'' . $this->get_the_logo( $cog, $key ) . '\'); height: ' . $height . 'px;">&nbsp;</a>';
    }

    function EBE_get_file_in_folder( $dir, $file_type = '', $type = '', $get_basename = false ) {
        /*
         * chuẩn hóa đầu vào
         */
        // bỏ dấu * nếu có
        $dir = rtrim( $dir, '*' );
        $file_type = ltrim( $file_type, '*' );
        // thêm dấu / nếu chưa có
        $dir = rtrim( $dir, '/' ) . '/';
        //echo $dir . '*' . $file_type . '<br>' . "\n";

        // lấy danh sách file
        if ( $file_type != '' ) {
            $arr = glob( $dir . '*' . $file_type, GLOB_BRACE );
        } else {
            $arr = glob( $dir . '*' );
        }
        //print_r( $arr );

        // chỉ lấy file
        if ( $type == 'file' ) {
            $arr = array_filter( $arr, 'is_file' );
        }
        // chỉ lấy thư mục
        else if ( $type == 'dir' ) {
            $arr = array_filter( $arr, 'is_dir' );
        }

        //	print_r($arr);
        //	exit();

        // chỉ lấy mỗi tên file hoặc thư mục
        if ( $get_basename == true ) {
            foreach ( $arr as $k => $v ) {
                $arr[ $k ] = basename( $v );
            }
        }

        return $arr;
    }

    function seo( $data, $url ) {
        //print_r( $data );
        if ( isset( $data[ 'term_id' ] ) ) {
            $seo = array(
                'title' => $data[ 'name' ],
                'description' => $data[ 'description' ] != '' ? $data[ 'description' ] : $data[ 'name' ],
                //'keyword' => $pageDetail[ 0 ][ 'keyword' ],
                //'name' => $pageDetail[ 0 ][ 'name' ],
                'term_id' => $data[ 'term_id' ],
                'body_class' => 'taxonomy ' . $data[ 'taxonomy' ] . '-taxonomy',
            );
        } else {
            $seo = array(
                'title' => $data[ 'post_title' ],
                'description' => $data[ 'post_excerpt' ] != '' ? $data[ 'post_excerpt' ] : $data[ 'post_title' ],
                //'keyword' => $pageDetail[ 0 ][ 'keyword' ],
                //'name' => $pageDetail[ 0 ][ 'name' ],
                'post_id' => $data[ 'ID' ],
                'body_class' => 'post ' . $data[ 'post_type' ] . '-post',
            );
        }
        //$seo[ 'google_analytics' ] = $getconfig->google_analytics;
        $seo[ 'keyword' ] = $seo[ 'description' ];
        $seo[ 'url' ] = $url;
        $seo[ 'canonical' ] = $url;
        //$seo[ 'index' ] = 1;
        $seo[ 'description' ] = trim( strip_tags( $seo[ 'description' ] ) );
        //print_r( $seo );

        return $seo;
    }

    // cắt chuỗi ngắn lại (phải làm phức tạp chút vì cắt tiếng Việt có dấu sẽ bị lỗi nếu cắt đúng chỗ có dấu)
    function short_string( $str, $len, $add_more = true ) {
        // sử dụng function mặc định xem như nào, nếu sau lỗi thì bỏ
        return mb_strimwidth( $str, 0, $len, $add_more == true ? '...' : '', 'utf-8' );
    }

    function short_string_v1( $str, $len, $add_more = true ) {
        if ( strlen( $str ) < $len ) {
            return $str;
        }

        // cắt chuỗi
        $str = substr( $str, 0, $len );
        // bỏ mảng cuối cùng
        $str = explode( ' ', $str );
        $count_str = count( $str );
        //echo $count_str . "\n";
        //print_r($str);
        unset( $str[ $count_str - 1 ] );
        if ( $add_more == true ) {
            unset( $str[ $count_str - 2 ] );
        }
        //print_r($str);

        // nối lại
        $str = implode( ' ', $str );

        // trả về
        if ( $add_more == true ) {
            return trim( $str ) . '...';
        }
        return trim( $str );
    }

    function get_config( $config, $key, $default_value = '' ) {
        //print_r( $config );
        //if ( isset( $config->$key ) ) {
        if ( $config->$key != '' ) {
            return $config->$key;
        }
        return $default_value;
    }

    function the_config( $config, $key, $default_value = '' ) {
        echo $this->get_config( $config, $key, $default_value );
    }

    function EBE_pagination( $Page, $TotalPage, $strLinkPager, $sub_part = '/page/' ) {
        return $this->EBE_part_page( $Page, $TotalPage, $strLinkPager, $sub_part );
    }

    function EBE_part_page( $Page, $TotalPage, $strLinkPager, $sub_part = '/page/' ) {
        if ( $TotalPage <= 1 ) {
            return '';
        }

        $strLinkPager = rtrim( $strLinkPager, '/' ) . $sub_part;
        //echo $strLinkPager . '<br>' . "\n";
        $show_page = 8;
        $str_page = '';
        if ( $Page <= $show_page ) {
            if ( $TotalPage <= $show_page ) {
                for ( $i = 1; $i <= $TotalPage; $i++ ) {
                    if ( $i == $Page ) {
                        $str_page .= '<span data-page="' . $i . '" class="current">' . $i . '</span>';
                    } else {
                        $str_page .= '<a rel="nofollow" href="' . $strLinkPager . $i . '">' . $i . '</a>';
                    }
                }
            } else {
                for ( $i = 1; $i <= $show_page; $i++ ) {
                    if ( $i == $Page ) {
                        $str_page .= '<span data-page="' . $i . '" class="current">' . $i . '</span>';
                    } else {
                        $str_page .= '<a rel="nofollow" href="' . $strLinkPager . $i . '">' . $i . '</a>';
                    }
                }
                $str_page .= ' ... <a rel="nofollow" href="' . $strLinkPager . $i . '">&gt;</a>';
            }
        } else {
            $chiadoi = $show_page / 2;
            $i = $Page - ( $chiadoi + 1 );
            $str_page = '<a rel="nofollow" href="' . $strLinkPager . $i . '">&lt;&lt;</a> <a rel="nofollow" href="' . $strLinkPager . '1">1</a> ... ';
            $i++;
            for ( $i; $i < $Page; $i++ ) {
                $str_page .= '<a rel="nofollow" href="' . $strLinkPager . $i . '">' . $i . '</a>';
            }
            $str_page .= '<span data-page="' . $i . '" class="current">' . $i . '</span>';
            $i++;
            $_Page = $Page + $chiadoi;
            if ( $_Page > $TotalPage ) {
                $_Page = $TotalPage;
            }
            for ( $i; $i < $_Page; $i++ ) {
                $str_page .= '<a rel="nofollow" href="' . $strLinkPager . $i . '">' . $i . '</a>';
            }
            $str_page .= ' ... <a rel="nofollow" href="' . $strLinkPager . $TotalPage . '">' . $TotalPage . '</a> <a href="' . $strLinkPager . $i . '" rel="nofollow">&gt;&gt;</a>';
        }

        return $str_page;
    }

    public function default_seo( $name, $canonical ) {
        return array(
            'index' => '0',
            'title' => $name,
            'description' => $name,
            'keyword' => $name,
            'name' => $name,
            'body_class' => $canonical,
            'canonical' => base_url( '/' . $canonical ),
            //'google_analytics' => $getconfig->google_analytics,
        );
    }

    // tạo file
    function _eb_create_file( $file_, $content_, $ops = [] ) {
        if ( $content_ == '' ) {
            echo 'ERROR put file: content is NULL<br>' . PHP_EOL;
            return false;
        }

        //
        if ( !isset( $ops[ 'add_line' ] ) ) {
            $ops[ 'add_line' ] = '';
        }

        //
        if ( !isset( $ops[ 'set_permission' ] ) ) {
            $ops[ 'set_permission' ] = 0777;
        }

        //
        if ( !isset( $ops[ 'ftp' ] ) ) {
            $ops[ 'ftp' ] = 0;
        }

        //
        if ( !file_exists( $file_ ) ) {
            $filew = fopen( $file_, 'x+' );
            // nhớ set 777 cho file
            chmod( $file_, $ops[ 'set_permission' ] );
            fclose( $filew );
        }

        //
        if ( $ops[ 'add_line' ] != '' ) {
            file_put_contents( $file_, $content_, FILE_APPEND );
        }
        //
        else {
            file_put_contents( $file_, $content_ );
        }

        //
        return true;
    }

    public function _eb_number_only( $str = '', $re = '/[^0-9]+/' ) {
        $str = trim( $str );
        if ( $str == '' ) {
            return 0;
        }
        //	echo $str . ' str number<br>';
        $a = preg_replace( $re, '', $str );
        //	echo $a . ' a number<br>';
        if ( $a == '' ) {
            $a = 0;
        } else if ( substr( $str, 0, 1 ) == '-' ) {
            $a = 0 - $a;
        } else {
            $a *= 1;
        }
        return $a;
    }
    public function _eb_float_only( $str = '', $lam_tron = 0 ) {
        $str = trim( $str );
        //	echo $str . ' str float<br>';
        $a = $this->_eb_number_only( $str, '/[^0-9|\.]+/' );
        //	echo $a . ' a float<br>';

        // làm tròn hết sang số nguyên
        if ( $lam_tron == 1 ) {
            $a = ceil( $a );
        }
        // làm tròn phần số nguyên, số thập phân giữ nguyên
        else if ( $lam_tron == 2 ) {
            $a = explode( '.', $a );
            if ( isset( $a[ 1 ] ) ) {
                $a = ( int )$a[ 0 ] . '.' . $a[ 1 ];
            } else {
                $a = ( int )$a[ 0 ];
            }
        }

        return $a;
    }
    public function un_money_format( $str ) {
        return $this->_eb_number_only( $str );
    }
    public function unmoney_format( $str ) {
        return $this->_eb_number_only( $str );
    }
    public function number_only( $str ) {
        return $this->_eb_number_only( $str );
    }
    public function text_only( $str = '' ) {
        return $this->_eb_text_only( $str );
    }

}