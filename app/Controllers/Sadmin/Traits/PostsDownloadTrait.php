<?php

namespace App\Controllers\Sadmin\Traits;

//
trait PostsDownloadTrait
{
    public function download()
    {
        $this->post_per_page = 100;
        $data = $this->lists([
            'get_data' => 1,
        ]);
        // print_r($data);
        // die(__FILE__ . ':' . __LINE__);

        // 
        $get_type = $this->MY_get('type', 'xml');
        if ($get_type == 'xml') {
            $xml_template = file_get_contents(__DIR__ . '/templates/' . $this->post_type . 's-export-item.xml');
            $xml_template = str_replace('{{base_url}}', $_SERVER['HTTP_HOST'], $xml_template);

            // 
            $xml_item = '';
            foreach ($data as $k => $v) {
                $v['pubDate'] = date('D, d M Y H:i:s O', strtotime($v['post_date']));

                // 
                $v['product_cat'] = '';
                $v['product_cat_nicename'] = '';
                $post_category = $v['post_meta']['post_category'] ?? '';
                if (!empty($post_category)) {
                    $post_category = explode(',', $post_category)[0];
                    $data_cats = $this->base_model->select('name, slug', 'terms', [
                        'term_id' => $post_category,
                        'is_deleted' => DeletedStatus::FOR_DEFAULT,
                    ], [
                        // 'show_query' => 1,
                        'order_by' => [
                            'term_id' => 'DESC',
                        ],
                        'limit' => 1,
                    ]);
                    // print_r($data_cats);

                    if (!empty($data_cats)) {
                        $v['product_cat'] = $data_cats['name'];
                        $v['product_cat_nicename'] = $data_cats['slug'];
                    }
                }

                // Fix relative URLs in post content
                $v['post_content'] = str_replace(' src="../../', ' src="' . DYNAMIC_BASE_URL, $v['post_content']);
                $v['post_content'] = str_replace(' href="../../', ' href="' . DYNAMIC_BASE_URL, $v['post_content']);

                // Debugging output
                // print_r($v);
                // break;

                // Process thumbnail URL and auto-insert image into content
                $this->processThumbnailAndContent($v);

                // Render XML item
                $xml_item .= HtmlTemplate::render(
                    $xml_template,
                    $v
                );
            }
            // die($xml_item);

            // 
            // print_r($this->session_data);
            $xml_content = HtmlTemplate::render(
                file_get_contents(__DIR__ . '/templates/' . $this->post_type . 's-export.xml'),
                [
                    'base_url' => $_SERVER['HTTP_HOST'],
                    'wordpress_version' => FAKE_WORDPRESS_VERSION,
                    'pubDate' => date('D, d M Y H:i:s O'),
                    'post_type' => $this->post_type,
                    'lang_key' => $this->lang_key,
                    'author_id' => $this->session_data['ID'],
                    'user_login' => $this->session_data['user_login'],
                    'user_email' => $this->session_data['user_email'],
                    'display_name' => $this->session_data['display_name'],
                    'user_nicename' => $this->session_data['user_nicename'],
                    // 
                    'items' => $xml_item,
                ]
            );

            // lúc xuất file excel thì chạy hàm clean để nó xóa mọi nội dung khác nếu có
            ob_end_clean();

            // header để trình duyệt nhận dạng đây là file XML
            header('Content-Type: application/xml; charset=utf-8');
            // thiết lập tên file khi người dùng save về
            // header('Content-Disposition: attachment; filename="' . $_SERVER['HTTP_HOST'] . '-posts-export-' . $this->post_type . '-' . date('Ymd-His') . '.xml"');
            // 
            header('Cache-Control: no-cache, no-store, must-revalidate');

            // 
            die($xml_content);
        } else if ($get_type == 'csv' && $this->post_type == PostType::PROD) {
            // xử lý xuất dữ liệu ra file CSV để import vào các website wordpress + woocommerce khác

            // nạp thư viện xử lý file excel
            require_once APPPATH . 'ThirdParty/phpspreadsheet/vendor/autoload.php';

            // tạo file excel theo cấu trúc mẫu
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('List ' . $this->post_type);
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'Type');
            $sheet->setCellValue('C1', 'Name');
            $sheet->setCellValue('D1', 'Published');
            $sheet->setCellValue('E1', 'Visibility in catalog');
            $sheet->setCellValue('F1', 'Short description');
            $sheet->setCellValue('G1', 'Description');
            $sheet->setCellValue('H1', 'In stock?');
            $sheet->setCellValue('I1', 'Sale price');
            $sheet->setCellValue('J1', 'Regular price');
            $sheet->setCellValue('K1', 'Categories');
            $sheet->setCellValue('L1', 'Images');

            $row = 2;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $item['ID']);
                $sheet->setCellValue('B' . $row, 'simple');
                $sheet->setCellValue('C' . $row, $item['post_title']);
                $sheet->setCellValue('D' . $row, $item['post_status'] == PostType::PUBLICITY ? '1' : '-1');
                $sheet->setCellValue('E' . $row, 'visible');
                $sheet->setCellValue('F' . $row, $item['post_excerpt']);
                $sheet->setCellValue('G' . $row, $item['post_content']);
                $sheet->setCellValue('H' . $row, '1'); // In stock

                if (isset($item['post_meta']['_sale_price']) && !empty($item['post_meta']['_sale_price'])) {
                    $sheet->setCellValue('I' . $row, $item['post_meta']['_sale_price']);
                }

                if (isset($item['post_meta']['_regular_price']) && !empty($item['post_meta']['_regular_price'])) {
                    $sheet->setCellValue('J' . $row, $item['post_meta']['_regular_price']);
                }

                // lấy danh mục sản phẩm
                $post_category = $item['post_meta']['post_category'] ?? '';
                if (!empty($post_category)) {
                    $post_category = explode(',', $post_category)[0];
                    $data_cats = $this->base_model->select('name, slug', 'terms', [
                        'term_id' => $post_category,
                        'is_deleted' => DeletedStatus::FOR_DEFAULT,
                    ], [
                        // 'show_query' => 1,
                        'order_by' => [
                            'term_id' => 'DESC',
                        ],
                        'limit' => 1,
                    ]);
                    // print_r($data_cats);

                    if (!empty($data_cats)) {
                        $sheet->setCellValue('K' . $row, $data_cats['name']);
                    }
                }

                // nếu có ảnh thì chuyển sang đường dẫn đầy đủ
                if (isset($item['post_meta']['image']) && !empty($item['post_meta']['image'])) {
                    $item['post_meta']['image'] = DYNAMIC_BASE_URL . $item['post_meta']['image'];
                    $sheet->setCellValue('L' . $row, isset($item['post_meta']['image']) ? $item['post_meta']['image'] : '');
                }
                $row++;
            }

            // bôi đậm hàng đầu tiên
            $styleArrayFirstRow = [
                'font' => [
                    'bold' => true,
                ]
            ];

            // Retrieve Highest Column (e.g AE)
            $highestColumn = $sheet->getHighestColumn();
            // die($highestColumn);

            //set first row bold
            $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray($styleArrayFirstRow);

            // chỉnh chiều rộng cho các cột
            // $sheet->getColumnDimension('B')->setWidth(12);
            // $sheet->getColumnDimension('C')->setWidth(20);
            // $sheet->getColumnDimension('D')->setWidth(12);
            foreach (
                [
                    'A',
                    'B',
                    'C',
                    'D',
                    'E',
                    'F',
                    'G',
                    'H',
                    'I',
                    'J',
                    'K',
                    'L',
                    'M',
                    'N',
                    'O',
                    'P',
                    'Q',
                    'R',
                    'S',
                    'T',
                    'U',
                    'V',
                    'W',
                    'X',
                    'Y',
                    'Z',
                ] as $v
            ) {
                $sheet->getColumnDimension($v)->setAutoSize(true);
            }

            // định dạng file csv
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);

            // 
            // lúc xuất file excel thì chạy hàm clean để nó xóa mọi nội dung khác nếu có
            ob_end_clean();

            // đặt tên file
            $filename = $_SERVER['HTTP_HOST'] . '_' . $this->post_type . '_list_' . date('Ymd_His') . '.csv';
            // gửi file về trình duyệt
            header('Content-Type: text/csv; charset=utf-8');
            // thiết lập tên file khi người dùng save về
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            // ghi file ra output
            $writer->save('php://output');
        } else {
            die('Unsupported file type: ' . $get_type . ' for post type: ' . $this->post_type);
        }
    }
}
