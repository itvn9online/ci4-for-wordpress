<?php
namespace App\Models;

// Libraries
use App\Libraries\PostType;
use App\Libraries\TaxonomyType;
use App\Libraries\DeletedStatus;

//
class Post extends PostPosts
{
    public function __construct()
    {
        parent::__construct();
    }

    /*
     * cập nhật lượt xem cho post
     */
    public function update_views($id, $val = 1)
    {
        //echo __FUNCTION__ . '<br>' . "\n";
        //echo $id . '<br>' . "\n";

        //
        $this->base_model->update_count($this->table, 'post_viewed', array(
            // WHERE
            'ID' => $id,
        ), [
                'value' => $val,
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
            ]);
    }

    // vì permalink gán trực tiếp vào db nên thi thoảng sẽ check lại chút
    public function sync_post_term_permalink()
    {
        if ($this->base_model->scache(__FUNCTION__) !== NULL) {
            return false;
        }
        // luôn tạo giãn cách để tránh update liên tục -> chỉ 1 người update là đủ
        $this->base_model->scache(__FUNCTION__, time(), 120);

        // lấy các post chưa có permalink đẻ update
        $data = $this->base_model->select('ID, post_permalink, post_type, post_name', 'posts', array(
            // các kiểu điều kiện where
            'post_status' => PostType::PUBLICITY,
            'post_permalink' => '',
        ), array(
                'where_in' => array(
                    'post_type' => array(
                        PostType::POST,
                        PostType::BLOG,
                        PostType::PAGE,
                    )
                ),
                'order_by' => array(
                    'ID' => 'DESC'
                ),
                // hiển thị mã SQL để check
                //'show_query' => 1,
                // trả về câu query để sử dụng cho mục đích khác
                //'get_query' => 1,
                // trả về COUNT(column_name) AS column_name
                //'selectCount' => 'ID',
                // trả về tổng số bản ghi -> tương tự mysql num row
                //'getNumRows' => 1,
                //'offset' => 0,
                'limit' => 20
            ));
        //print_r( $data );

        // nếu không có thì chuyển sang update term
        if (empty($data)) {
            // lấy các term chưa có permalink đẻ update
            $data = $this->base_model->select('term_id, term_permalink, taxonomy, slug', WGR_TERM_VIEW, array(
                // các kiểu điều kiện where
                'is_deleted' => DeletedStatus::FOR_DEFAULT,
                'term_permalink' => '',
            ), array(
                    'where_in' => array(
                        'taxonomy' => array(
                            TaxonomyType::POSTS,
                            TaxonomyType::TAGS,
                            TaxonomyType::BLOGS,
                            TaxonomyType::BLOG_TAGS,
                        )
                    ),
                    'order_by' => array(
                        'term_id' => 'DESC'
                    ),
                    // hiển thị mã SQL để check
                    //'show_query' => 1,
                    // trả về câu query để sử dụng cho mục đích khác
                    //'get_query' => 1,
                    // trả về COUNT(column_name) AS column_name
                    //'selectCount' => 'ID',
                    // trả về tổng số bản ghi -> tương tự mysql num row
                    //'getNumRows' => 1,
                    //'offset' => 0,
                    'limit' => 20
                ));
            //print_r( $data );

            // nếu hết rồi thì lưu lại cache để sau đỡ dính
            if (empty($data)) {
                $this->base_model->scache(__FUNCTION__, time(), 3600);
            } else {
                foreach ($data as $v) {
                    $this->term_model->get_the_permalink($v);
                }
            }
        }
        // có thì xử lý cái phần có
        else {
            foreach ($data as $v) {
                $this->get_the_permalink($v);
            }
        }

        //
        return true;
    }
}