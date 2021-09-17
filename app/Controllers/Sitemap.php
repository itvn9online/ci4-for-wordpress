<?php
//if ( !defined( 'BASEPATH' ) )exit( 'No direct script access allowed' );
//require_once __DIR__ . '/Layout.php';
namespace App\ Controllers;

// Libraries
use App\ Libraries\ PostType;
use App\ Libraries\ TaxonomyType;

//
class Sitemap extends Layout {
	public function __construct() {
		parent::__construct();


		// định dạng ngày tháng
		$this->sitemap_date_format = 'c';
		$this->sitemap_current_time = date( $this->sitemap_date_format, time() );

		// giới hạn số bài viết cho mỗi sitemap map
		$this->limit_post_get = 100;
		//$this->limit_post_get = 2;

		// giới hạn tạo sitemap cho hình ảnh -> google nó limit 1000 ảnh nên chỉ lấy thế thôi
		$this->limit_image_get = $this->limit_post_get;

		// thời gian nạp lại cache cho file, để = 0 -> disable
		//$time_for_relload_sitemap = 0;
		$time_for_relload_sitemap = 3600;
		//$time_for_relload_sitemap = 3 * 3600;

		$this->web_link = DYNAMIC_BASE_URL;
	}

	public function index( $post_type = '', $page_page = '', $page_num = 1 ) {
		//echo __FILE__ . ':' . __LINE__ . '<br>' . "\n";

		//
		$this->WGR_echo_sitemap_css();

		//
		//echo $post_type . '<br>' . "\n";
		//echo $page_page . '<br>' . "\n";
		//echo $page_num . '<br>' . "\n";
		if ( !empty( $post_type ) ) {
			if ( $post_type == 'tags' ) {
				return $this->sitemap_tags();
			} else {
				return $this->by_post_type( $post_type, $page_num );
			}
		}

		//
		$get_list_sitemap = '';

		// manual -> chuẩn hơn trong trường hợp không có bài viết tương ứng thì sitemap không được kích hoạt
		$get_list_sitemap .= $this->WGR_echo_sitemap_node( $this->web_link . 'sitemap/tags', $this->sitemap_current_time );

		//
		$arr_post_type = [
			PostType::POST,
			PostType::BLOG,
			PostType::PAGE,
		];
		foreach ( $arr_post_type as $post_type ) {
			$totalThread = $this->get_post_type( $post_type, 0, true );
			if ( $totalThread > 0 ) {
				$get_list_sitemap .= $this->WGR_echo_sitemap_node( $this->web_link . 'sitemap/' . $post_type, $this->sitemap_current_time );

				// phân trang cho sitemap (lấy từ trang 2 trở đi)
				$get_list_sitemap .= $this->WGR_sitemap_part_page( $totalThread, 'sitemap/' . $post_type );
			}
		}

		//
		echo $this->tmp( file_get_contents( __DIR__ . '/sitemap/sitemapindex.xml', 1 ), [
			'get_list_sitemap' => $get_list_sitemap,
		] );
		exit();
	}

	private function sitemap_tags() {
		$get_list_sitemap = '';

		// home
		$get_list_sitemap .= $this->WGR_echo_sitemap_url_node(
			$this->web_link,
			1.0,
			$this->sitemap_current_time
		);

		//
		$arr_taxonomy_type = [
			TaxonomyType::POSTS,
			TaxonomyType::BLOGS,
		];
		foreach ( $arr_taxonomy_type as $taxonomy_type ) {
			$data = $this->term_model->get_all_taxonomy( $taxonomy_type, 0, [
				//'or_like' => $where_or_like,
				//'lang_key' => LanguageCost::lang_key(),
				//'get_meta' => true,
				//'get_child' => true
			] );
			//print_r( $data );
			foreach ( $data as $v ) {
				$get_list_sitemap .= $this->WGR_echo_sitemap_url_node(
					$this->term_model->get_the_permalink( $v ),
					1.0,
					date( $this->sitemap_date_format, strtotime( $v[ 'last_updated' ] ) ),
				);
			}
		}

		//
		return $this->WGR_echo_sitemap_urlset( $get_list_sitemap );
	}

	private function get_post_type( $post_type, $page_num = 1, $get_count = false ) {
		// các kiểu điều kiện where
		$where = [
			'wp_posts.post_status !=' => PostType::DELETED,
			'wp_posts.post_type' => $post_type,
			'wp_posts.post_status' => PostType::PUBLIC,
			//'wp_posts.lang_key' => LanguageCost::lang_key()
		];

		// tổng kết filter
		$filter = [
			/*
			'where_in' => array(
			    'wp_posts.post_status' => array(
			        PostType::DRAFT,
			        PostType::PUBLIC,
			        PostType::PENDING,
			    )
			),
			*/
			//'or_like' => $where_or_like,
			'order_by' => array(
				'wp_posts.menu_order' => 'DESC',
				'wp_posts.post_date' => 'DESC',
				//'post_modified' => 'DESC',
			),
			// hiển thị mã SQL để check
			//'show_query' => 1,
			// trả về câu query để sử dụng cho mục đích khác
			//'get_query' => 1,
			//'offset' => 0,
			//'limit' => $post_per_page;
		];

		//
		$totalThread = $this->base_model->select( 'COUNT(ID) AS c', 'wp_posts', $where, $filter );
		//print_r( $totalThread );
		$totalThread = $totalThread[ 0 ][ 'c' ];

		//
		if ( $get_count === true ) {
			return $totalThread;
		}

		//print_r( $totalThread );
		$totalPage = ceil( $totalThread / $this->limit_post_get );
		if ( $totalPage < 1 ) {
			$totalPage = 1;
		}
		//echo $totalPage . '<br>' . "\n";
		if ( $page_num > $totalPage ) {
			$page_num = $totalPage;
		} else if ( $page_num < 1 ) {
			$page_num = 1;
		}
		//echo $totalThread . '<br>' . "\n";
		//echo $totalPage . '<br>' . "\n";
		$offset = ( $page_num - 1 ) * $this->limit_post_get;

		//
		$filter[ 'offset' ] = $offset;
		$filter[ 'limit' ] = $this->limit_post_get;
		$data = $this->base_model->select( '*', 'wp_posts', $where, $filter );
		//print_r( $data );

		//
		return $data;
	}

	private function by_post_type( $post_type, $page_num = 1 ) {
		$data = $this->get_post_type( $post_type, $page_num );

		//
		$get_list_sitemap = '';
		foreach ( $data as $v ) {
			$get_list_sitemap .= $this->WGR_echo_sitemap_url_node(
				$this->post_model->get_the_permalink( $v ),
				0.5,
				date( $this->sitemap_date_format, strtotime( $v[ 'post_modified' ] ) ),
				array(
					'get_images' => $v[ 'ID' ]
				)
			);
		}

		//
		return $this->WGR_echo_sitemap_urlset( $get_list_sitemap );
	}

	private function WGR_echo_sitemap_urlset( $get_list_sitemap ) {
		echo $this->tmp( file_get_contents( __DIR__ . '/sitemap/urlset.xml', 1 ), [
			'get_list_sitemap' => $get_list_sitemap,
		] );
		exit();
	}

	private function WGR_echo_sitemap_url_node( $loc, $priority, $lastmod, $op = array() ) {
		return $this->tmp( file_get_contents( __DIR__ . '/sitemap/url.xml', 1 ), [
			'loc' => $loc,
			'lastmod' => $lastmod,
		] );
	}

	private function WGR_sitemap_part_page( $count_post, $file_name = 'sitemap/post' ) {
		$str = '';

		$count_post_post = $count_post;
		//echo $type . ' --> ' . $count_post . '<br>' . "\n";

		if ( $count_post_post > $this->limit_post_get ) {
			$j = 0;
			for ( $i = 2; $i < 100; $i++ ) {
				$j += $this->limit_post_get;

				if ( $j < $count_post_post ) {
					// cho phần bài viết
					$str .= $this->WGR_echo_sitemap_node( $this->web_link . $file_name . '/page/' . $i, $this->sitemap_current_time );
				}
			}
		}

		// tạm thời ko lấy phần sitemap ảnh ở đây
		return $str;
	}

	//
	private function WGR_echo_sitemap_css() {
		header( "Content-type: text/xml" );
		//die( __FILE__ . ':' . __LINE__ );

		echo $this->tmp( file_get_contents( __DIR__ . '/sitemap/css.xml', 1 ), [
			'base_url' => DYNAMIC_BASE_URL,
			'filemtime_main_sitemap' => filemtime( PUBLIC_HTML_PATH . 'public/css/main-sitemap.xsl' ),
		] );
	}


	private function WGR_echo_sitemap_node( $loc, $lastmod ) {
		return $this->tmp( file_get_contents( __DIR__ . '/sitemap/sitemap_node.xml', 1 ), [
			'loc' => $loc,
			'lastmod' => $lastmod,
		] );
	}

	private function tmp( $html, $arr ) {
		foreach ( $arr as $k => $v ) {
			$html = str_replace( '%' . $k . '%', $v, $html );
		}
		return $html;
	}
}