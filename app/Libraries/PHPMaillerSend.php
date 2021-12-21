<?php

namespace App\ Libraries;

//use PHPMailer\ PHPMailer\ PHPMailer;
//use PHPMailer\ PHPMailer\ Exception;

class PHPMaillerSend {
    public static function get_the_send( $data, $cog = [], $debug = 0 ) {
        //echo APPPATH . '<br>' . "\n";
        require_once APPPATH . 'ThirdParty/PHPMailer/src/Exception.php';
        require_once APPPATH . 'ThirdParty/PHPMailer/src/PHPMailer.php';
        require_once APPPATH . 'ThirdParty/PHPMailer/src/SMTP.php';

        //print_r( $data );
        //print_r( $cog );
        //die( __FILE__ . ':' . __LINE__ );

        // -> data
        $to = $data[ 'to' ];
        $to_name = isset( $data[ 'to_name' ] ) ? $data[ 'to_name' ] : $data[ 'to' ];
        $bcc_email = isset( $data[ 'bcc_email' ] ) ? $data[ 'bcc_email' ] : [];
        $cc_email = isset( $data[ 'cc_email' ] ) ? $data[ 'cc_email' ] : [];
        $subject = $data[ 'subject' ];
        $message = $data[ 'message' ];

        // -> config
        $host_name = $cog[ 'smtp_host_name' ];
        //echo $host_name . '<br>' . "\n";
        $host_port = $cog[ 'smtp_host_port' ];
        //echo $host_port . '<br>' . "\n";
        $host_user = $cog[ 'smtp_host_user' ];
        $host_pass = $cog[ 'smtp_host_pass' ];
        if ( !isset( $cog[ 'smtp_from' ] ) || $cog[ 'smtp_from' ] == '' ) {
            $cog[ 'smtp_from' ] = $host_user;
        }
        $from = $cog[ 'smtp_from' ];
        if ( !isset( $cog[ 'smtp_from_name' ] ) || $cog[ 'smtp_from_name' ] == '' ) {
            $cog[ 'smtp_from_name' ] = $_SERVER[ 'HTTP_HOST' ];
        }
        $from_name = $cog[ 'smtp_from_name' ];

        //
        $mail = new\ PHPMailer\ PHPMailer\ PHPMailer();
        $mail->IsSMTP();
        try {
            $mail->CharSet = 'utf-8';
            $mail->SMTPDebug = $debug;
            $mail->SMTPAuth = true;

            //
            if ( $host_name == 'smtp.gmail.com' ) {
                if ( $host_port == 465 ) {
                    $mail->SMTPSecure = 'ssl';
                } else if ( $host_port == 587 ) {
                    $mail->SMTPSecure = 'tls';
                } else {
                    return 'ERROR! gmail port';
                }
                /*
            } else if ( $host_name == 'smtp.pepipost.com' ) {
                $mail->SMTPSecure = 'tls';
                */
            } else if ( isset( $cog[ 'smtp_secure' ] ) && !empty( $cog[ 'smtp_secure' ] ) ) {
                $mail->SMTPSecure = $cog[ 'smtp_secure' ];
            } else if ( !empty( $host_port ) ) {
                if ( $host_port == 465 ) {
                    $mail->SMTPSecure = 'ssl';
                } else if ( $host_port == 587 ) {
                    $mail->SMTPSecure = 'tls';
                }
            }

            //
            $mail->Host = $host_name;
            $mail->Port = $host_port;
            $mail->Username = $host_user;
            $mail->Password = $host_pass;

            //
            $reply_to = $from;
            if ( isset( $cog[ 'smtp_no_reply' ] ) && $cog[ 'smtp_no_reply' ] == 'on' ) {
                $reply_to = 'no-reply@' . $_SERVER[ 'HTTP_HOST' ];
            }
            $mail->addReplyTo( $reply_to, $from_name );
            $mail->SetFrom( $from, $from_name );

            //
            foreach ( $bcc_email as $v ) {
                if ( $v != '' && $v != $to ) {
                    $mail->AddBCC( $v, $v );
                }
            }
            foreach ( $cc_email as $v ) {
                if ( $v != '' && $v != $to ) {
                    $mail->AddCC( $v, $v );
                }
            }

            //
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            $mail->WordWrap = 50;
            $mail->IsHTML( true );
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AddAddress( $to, $to_name );

            //
            if ( !$mail->Send() ) {
                $m = $mail->ErrorInfo;
            } else {
                return true;
            }
        } catch ( Exception $e ) {
            $m = $e;
        }

        //
        return $m;
    }

    public static function the_send( $data, $cog = [] ) {
        $result = self::get_the_send( $data, ( array )$cog );
        if ( $result === true ) {
            return true;
        }
        return $result;
    }
}