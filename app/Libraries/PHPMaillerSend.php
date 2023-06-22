<?php

namespace App\Libraries;

//
require_once APPPATH . 'ThirdParty/PHPMailer/autoload.php';

//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;

class PHPMaillerSend
{
    const DEBUG_0 = 0;
    const DEBUG_1 = 1;
    const DEBUG_2 = 2;

    public static function get_the_send($data, $cog = [], $debug = 0, $resend = true)
    {
        //echo APPPATH . '<br>' . PHP_EOL;
        //require_once APPPATH . 'ThirdParty/PHPMailer/src/Exception.php';
        //require_once APPPATH . 'ThirdParty/PHPMailer/src/PHPMailer.php';
        //require_once APPPATH . 'ThirdParty/PHPMailer/src/SMTP.php';

        //print_r( $data );
        //print_r( $cog );
        //die( __CLASS__ . ':' . __LINE__ );

        // -> data
        $to = $data['to'];
        $to_name = isset($data['to_name']) ? $data['to_name'] : $data['to'];
        $bcc_email = isset($data['bcc_email']) ? $data['bcc_email'] : [];
        $cc_email = isset($data['cc_email']) ? $data['cc_email'] : [];
        $subject = '[' . $_SERVER['HTTP_HOST'] . '] ' . $data['subject'];
        $message = $data['message'];

        // -> config
        $host_name = $cog['smtp_host_name'];
        if ($host_name == '') {
            return 'Host?';
        }
        //echo $host_name . '<br>' . PHP_EOL;
        $host_port = $cog['smtp_host_port'];
        //echo $host_port . '<br>' . PHP_EOL;
        $host_user = $cog['smtp_host_user'];
        if ($host_user == '') {
            return 'User?';
        }
        $host_pass = $cog['smtp_host_pass'];
        //if ( $debug > 0 )echo $host_pass . '<br>' . PHP_EOL;
        if ($host_pass == '') {
            return 'Pass?';
        }
        $from = $host_user;
        /* daidq (2022-04-18): bỏ qua tham số smtp_from -> do from phải cùng domain với host_user
         if ( !isset( $cog[ 'smtp_from' ] ) || $cog[ 'smtp_from' ] == '' ) {
         $cog[ 'smtp_from' ] = $host_user;
         }
         $from = $cog[ 'smtp_from' ];
         */
        if (!isset($cog['smtp_from_name']) || $cog['smtp_from_name'] == '') {
            $cog['smtp_from_name'] = $_SERVER['HTTP_HOST'];
        }
        $from_name = $cog['smtp_from_name'];

        //
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        $mail->IsSMTP();
        try {
            $mail->CharSet = 'utf-8';
            $mail->SMTPDebug = $debug;
            $mail->SMTPAuth = true;

            //
            if ($host_name == 'smtp.gmail.com') {
                if ($host_port == 465) {
                    $mail->SMTPSecure = 'ssl';
                } else if ($host_port == 587) {
                    $mail->SMTPSecure = 'tls';
                } else {
                    return 'ERROR! gmail port';
                }
                /*
                 } else if ( $host_name == 'smtp.pepipost.com' ) {
                 $mail->SMTPSecure = 'tls';
                 */
            } else if (isset($cog['smtp_secure']) && !empty($cog['smtp_secure'])) {
                $mail->SMTPSecure = $cog['smtp_secure'];
            } else if (!empty($host_port)) {
                if ($host_port == 465) {
                    $mail->SMTPSecure = 'ssl';
                } else if ($host_port == 587) {
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
            if (isset($cog['smtp_no_reply']) && $cog['smtp_no_reply'] == 'on') {
                $reply_to = 'no-reply@' . $_SERVER['HTTP_HOST'];
            }
            $mail->addReplyTo($reply_to, $from_name);
            $mail->SetFrom($from, $from_name);

            //
            foreach ($bcc_email as $v) {
                if ($v != '' && $v != $to) {
                    $mail->AddBCC($v, $v);
                }
            }
            foreach ($cc_email as $v) {
                if ($v != '' && $v != $to) {
                    $mail->AddCC($v, $v);
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
            $mail->IsHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AddAddress($to, $to_name);

            //
            if (!$mail->Send()) {
                // gửi bằng email dự phòng nếu có
                if ($resend === true && $cog['smtp2_host_user'] != '' && $cog['smtp2_host_pass'] != '') {
                    $cog['smtp_host_user'] = $cog['smtp2_host_user'];
                    $cog['smtp_host_pass'] = $cog['smtp2_host_pass'];
                    $cog['smtp_host_name'] = $cog['smtp2_host_name'];
                    $cog['smtp_secure'] = $cog['smtp2_secure'];
                    $cog['smtp_host_port'] = $cog['smtp2_host_port'];

                    //
                    if ($debug > 0) {
                        print_r($mail->ErrorInfo);
                        echo '<hr>' . PHP_EOL;
                        echo 'Username/ Email: ' . $cog['smtp_host_user'] . '<br>' . PHP_EOL;
                        echo 'Password: ' . substr($cog['smtp_host_pass'], 0, 6) . '******<br>' . PHP_EOL;
                        echo 'Hostname: ' . $cog['smtp_host_name'] . '<br>' . PHP_EOL;
                        echo 'Secure: ' . $cog['smtp_secure'] . '<br>' . PHP_EOL;
                        echo 'Port: ' . $cog['smtp_host_port'] . '<br>' . PHP_EOL;
                        echo '<hr>' . PHP_EOL;
                    }

                    //
                    return self::get_the_send($data, $cog, $debug, false);
                }

                //
                $m = $mail->ErrorInfo;
            } else {
                return true;
            }
        } catch (Exception $e) {
            $m = $e;
        }

        //
        return $m;
    }

    public static function the_send($data, $cog = [], $debug = 0)
    {
        $result = self::get_the_send($data, (array) $cog, $debug);
        if ($result === true) {
            return true;
        }
        return $result;
    }
}
