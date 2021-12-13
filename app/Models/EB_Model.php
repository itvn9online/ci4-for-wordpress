<?php

/*
 * Nạp model dùng chung cho các model khác
 */
namespace App\ Models;

//use CodeIgniter\ Model;

//class EB_Model extends Model {
class EB_Model {
    protected $table = '';
    protected $primaryKey = 'ID';

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
    }
}