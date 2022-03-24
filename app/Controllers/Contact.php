<?php
namespace App\ Controllers;

//
use App\ Libraries\ PHPMaillerSend;

class Contact extends Home {
    public function __construct() {
        parent::__construct();
        //$this->load->helper( 'translate' );
        //$this->load->helper( 'form' );
    }

    public function put() {
        $this->wgr_target();

        //
        $data = $this->MY_post( 'data' );
        if ( empty( $data ) ) {
            $this->base_model->msg_error_session( 'Phương thức đầu vào không chính xác', $this->form_target );
            //return redirect()->to( DYNAMIC_BASE_URL );
            return $this->done_action_login();
        }

        // thực hiện validation
        /*
        $this->form_validation->set_rules( 'data[fullname]', 'fullname', 'required|xss_clean|min_length[5]|max_length[255]' );
        $this->form_validation->set_rules( 'data[email]', 'email', 'required|xss_clean|min_length[5]|max_length[255]|valid_email' );
        $this->form_validation->set_rules( 'data[title]', 'title', 'required|xss_clean|min_length[5]|max_length[255]' );
        $this->form_validation->set_rules( 'data[content]', 'content', 'required|xss_clean|min_length[5]' );
        */
        $this->validation->reset();
        $this->validation->setRules( [
            'fullname' => 'required|min_length[5]|max_length[255]',
            'email' => 'required|min_length[5]|max_length[255]|valid_email',
            'title' => 'required|min_length[5]|max_length[255]',
            'content' => 'required|min_length[5]',
        ] );

        //
        $redirect_to = DYNAMIC_BASE_URL . ltrim( $this->MY_post( 'redirect' ), '/' );
        if ( empty( $redirect_to ) ) {
            $redirect_to = DYNAMIC_BASE_URL;
        }

        //
        /*
        if ( $this->form_validation->run() == FALSE ) {
            $this->base_model->msg_error_session( 'Vui lòng kiểm tra lại! Dữ liệu đầu vào không chính xác', $this->form_target );
            */
        if ( !$this->validation->run( $data ) ) {
            $this->set_validation_error( $this->validation->getErrors(), $this->form_target );
        } else {
            $smtp_config = $this->option_model->get_smtp();

            $submit = $this->MY_comment( [
                'redirect_to' => $redirect_to,
                'comment_type' => $this->getClassName( __CLASS__ )
            ] );

            // thiết lập thông tin người nhận
            $data_send = [
                //'to' => $data[ 'email' ],
                'to' => $smtp_config->emailcontact,
                //'to_name' => $data[ 'fullname' ],
                'subject' => $data[ 'title' ],
                'message' => $submit[ 'message' ],
            ];

            //
            $bcc_email = [];

            //
            $send_my_email = $this->MY_post( 'send_my_email' );
            if ( $send_my_email === 'on' ) {
                //$data_send[ 'to' ] = $data[ 'email' ];
                //$data_send[ 'to_name' ] = $data[ 'fullname' ];

                //
                $bcc_email[] = $data[ 'email' ];
            }

            //
            $data_send[ 'bcc_email' ] = $bcc_email;

            //
            PHPMaillerSend::the_send( $data_send, $smtp_config );
        }

        //
        //die( redirect( $redirect_to ) );
        return $this->done_action_login( $redirect_to );
    }
}