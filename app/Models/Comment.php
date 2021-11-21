<?php

namespace App\ Models;

class Comment extends EB_Model {
    public $table = 'wp_comments';
    protected $primaryKey = 'comment_ID';

    public $metaTable = 'wp_commentmeta';
    protected $metaKey = 'meta_id';

	public function __construct() {
		parent::__construct();

		$this->request = \Config\ Services::request();
	}

	public function insert_comments( $data ) {
		$data_default = [
			//'comment_author_url' => $redirect_to,
			'comment_author_IP' => $this->request->getIPAddress(),
			'comment_date' => date( 'Y-m-d H:i:s' ),
			'comment_content' => '',
			'comment_agent' => $_SERVER[ 'HTTP_USER_AGENT' ],
			//'comment_type' => $ops[ 'comment_type' ],
			'user_id' => 0,
		];
		$data_default[ 'comment_date_gmt' ] = $data_default[ 'comment_date' ];
		foreach ( $data_default as $k => $v ) {
			if ( !isset( $data[ $k ] ) ) {
				$data[ $k ] = $v;
			}
		}

		//
		return $this->base_model->insert( $this->table, $data, true );
	}

	public function insert_meta_comments( $data ) {
		return $this->base_model->insert( $this->metaTable, $data, true );
	}
}