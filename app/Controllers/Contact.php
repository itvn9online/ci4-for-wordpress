<?php
require_once __DIR__ . '/Home.php';

class Contact extends Home {
    public function __construct() {
        parent::__construct();
        //$this->load->helper( 'translate' );
        //$this->load->helper( 'form' );
    }

    public function put() {
        if ( empty( $this->MY_post( 'data' ) ) ) {
            $this->session->setFlashdata( 'msg_error', 'Phương thức đầu vào không chính xác' );
            die( redirect()->to( DYNAMIC_BASE_URL ) );
        }

        // thực hiện validation
        $this->form_validation->set_rules( 'data[fullname]', 'fullname', 'required|xss_clean|min_length[5]|max_length[255]' );
        $this->form_validation->set_rules( 'data[email]', 'email', 'required|xss_clean|min_length[5]|max_length[255]|valid_email' );
        $this->form_validation->set_rules( 'data[title]', 'title', 'required|xss_clean|min_length[5]|max_length[255]' );
        $this->form_validation->set_rules( 'data[content]', 'content', 'required|xss_clean|min_length[5]' );

        //
        $redirect_to = DYNAMIC_BASE_URL . ltrim( $this->MY_post( 'redirect' ), '/' );
        if ( empty( $redirect_to ) ) {
            $redirect_to = DYNAMIC_BASE_URL;
        }
        if ( $this->form_validation->run() == FALSE ) {
            $this->session->setFlashdata( 'msg_error', 'Vui lòng kiểm tra lại! Dữ liệu đầu vào không chính xác' );
        } else {
            $submit = $this->MY_comment( [
                'redirect_to' => $redirect_to,
                'comment_type' => __CLASS__
            ] );

            //
            $data = $this->MY_post( 'data' );

            // thiết lập thông tin người nhận
            $data_send = [
                'to' => $data[ 'email' ],
                'to_name' => $data[ 'fullname' ],
                'subject' => '(' . $_SERVER[ 'HTTP_HOST' ] . ') ' . $data[ 'title' ],
                'message' => $submit[ 'message' ],
            ];

            //
            PHPMaillerSend::the_send( $data_send, $this->getconfig );
        }

        //
        die( redirect( $redirect_to ) );
    }
}