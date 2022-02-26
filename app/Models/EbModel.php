<?php

/*
 * Nạp model dùng chung cho các model khác
 */
namespace App\ Models;

//use CodeIgniter\ Model;

//class EbModel extends Model {
class EbModel {
    protected $table = '';
    public $primaryKey = 'ID';

    public $metaKey = 'meta_id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = [];

    protected $useTimestamps = false;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;

    public function __construct() {
        $this->base_model = new\ App\ Models\ Base();
        //$this->db = \Config\ Database::connect();
        $this->cache = \Config\ Services::cache();
    }

    // cache bên model là cache select database -> chỉ kiểm tra theo key truyền vào -> không kiểm tra theo session login
    public function MY_cache( $key, $value = '', $time = MINI_CACHE_TIMEOUT ) {
        // lưu cache nếu có nội dung
        if ( $value != '' ) {
            return $this->cache->save( $key, $value, $time );
        }

        // trả về cache nếu có
        return $this->cache->get( $key );
    }
}