<?php

namespace App\Controllers\Sadmin;

// Libraries
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;
use App\Libraries\LanguageCost;
use App\Libraries\DeletedStatus;
use App\Libraries\CommentType;
use App\Helpers\HtmlTemplate;

use App\Controllers\Sadmin\Traits\PostsListTrait;
use App\Controllers\Sadmin\Traits\PostsDownloadTrait;
use App\Controllers\Sadmin\Traits\PostsFormTrait;
use App\Controllers\Sadmin\Traits\PostsWriteTrait;
use App\Controllers\Sadmin\Traits\PostsDeleteTrait;
use App\Controllers\Sadmin\Traits\PostsUtilityTrait;
use App\Controllers\Sadmin\Traits\PostsCommentTrait;
use App\Controllers\Sadmin\Traits\PostsThumbnailTrait;

//
class Posts extends Sadmin
{
    use PostsListTrait;
    use PostsDownloadTrait;
    use PostsFormTrait;
    use PostsWriteTrait;
    use PostsDeleteTrait;
    use PostsUtilityTrait;
    use PostsCommentTrait;
    use PostsThumbnailTrait;

    protected $post_type = '';
    protected $name_type = '';
    //private $detault_type = '';
    protected $post_arr_status = [];

    // các taxonomy được hỗ trợ -> cái nào trống nghĩa là không hỗ trợ theo post_type tương ứng
    protected $taxonomy = TaxonomyType::POSTS;
    protected $tags = TaxonomyType::TAGS;
    //protected $options = TaxonomyType::OPTIONS;
    protected $options = '';

    // tham số dùng để thay đổi bảng cần gọi dữ liệu
    public $table = 'posts';
    public $metaTable = 'postmeta';
    // tham số dùng để thay đổi URL cho controller nếu muốn
    protected $controller_slug = 'posts';
    // tham số dùng để đổi file view khi add hoặc edit bài viết nếu muốn
    protected $add_view_path = 'posts';
    // tham số dùng để đổi file view khi xem danh sách bài viết nếu muốn
    protected $list_view_path = 'posts';
    protected $list_table_path = '';
    // dùng để chọn xem hiển thị nhóm sản phẩm nào ra ở phần danh mục
    protected $main_category_key = 'post_category';
    protected $comment_type = CommentType::COMMENT;
    protected $post_per_page = 20;

    /**
     * khi update hoặc insert sẽ kiểm tra xem các dữ liệu trong này có không, nếu có không sẽ gán mặc định
     * vì các checkbox khi bỏ chọn tất cả sẽ không xuất hiện trong post -> không được update
     */
    protected $default_post_data = [];
    // các cột được liệt kê trong này sẽ được chuyển đổi từ datetime sang timestamp -> do plugin tạo thời gian nó lấy theo múi giờ hiện tại của người dùng -> lên server phải convert về múi giờ của server
    protected $timestamp_post_data = [];


    public function __construct($for_extends = false)
    {
        parent::__construct();

        // kiểm tra quyền truy cập của tài khoản hiện tại
        $this->check_permision(__CLASS__);

        // hỗ trợ lấy theo params truyền vào từ url
        if ($this->post_type == '') {
            $this->post_type = $this->MY_get('post_type', PostType::POST);
        }

        // chỉ kiểm tra các điều kiện này nếu không được chỉ định là extends
        if ($for_extends === false) {
            // lọc bài viết dựa theo post type
            //$this->detault_type = PostType::POST;
            $this->name_type = PostType::typeList($this->post_type);

            // báo lỗi nếu không xác định được post_type
            //if ( $this->post_type == '' || $this->name_type == '' ) {
            if ($this->name_type == '') {
                die('Post type not register in system: ' . $this->post_type);
            }
        }

        //
        $this->post_arr_status = PostType::arrStatus();
        //print_r( $this->post_arr_status );

        // chỉnh lại bảng select của model
        $this->post_model->table = $this->table;
        $this->post_model->metaTable = $this->metaTable;
    }

}
