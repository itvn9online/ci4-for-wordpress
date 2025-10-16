<?php

namespace App\Libraries;

// nếu có thư viện PHPMailer mới thì dùng
if (is_dir(APPPATH . 'ThirdParty/PHPMailer-6.10.0')) {
    if (is_file(APPPATH . 'ThirdParty/PHPMailer-6.10.0/autoload.php')) {
        include_once APPPATH . 'ThirdParty/PHPMailer-6.10.0/autoload.php';
    } else {
        include_once APPPATH . 'ThirdParty/PHPMailer-6.10.0/src/Exception.php';
        include_once APPPATH . 'ThirdParty/PHPMailer-6.10.0/src/PHPMailer.php';
        include_once APPPATH . 'ThirdParty/PHPMailer-6.10.0/src/SMTP.php';
    }
} else {
    // nếu không có thì dùng thư viện cũ
    if (is_file(APPPATH . 'ThirdParty/PHPMailer/autoload.php')) {
        include_once APPPATH . 'ThirdParty/PHPMailer/autoload.php';
    } else {
        include_once APPPATH . 'ThirdParty/PHPMailer/src/Exception.php';
        include_once APPPATH . 'ThirdParty/PHPMailer/src/PHPMailer.php';
        include_once APPPATH . 'ThirdParty/PHPMailer/src/SMTP.php';
    }
}

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

class PHPMaillerSend
{
    const DEBUG_0 = 0;
    const DEBUG_1 = 1;
    const DEBUG_2 = 2;

    public static function get_the_send($data, $cog = [], $debug = 0, $resend = true)
    {
        //echo APPPATH . '<br>' . "\n";

        //print_r( $data );
        //print_r( $cog );
        //die( __CLASS__ . ':' . __LINE__ );

        // -> data
        $to = $data['to'];
        $to_name = isset($data['to_name']) ? $data['to_name'] : $data['to'];
        $bcc_email = isset($data['bcc_email']) ? $data['bcc_email'] : [];
        $cc_email = isset($data['cc_email']) ? $data['cc_email'] : [];
        // lấy host hiện tại
        $the_host = str_replace('www', '', explode(':', $_SERVER['HTTP_HOST'])[0]);
        $subject = '[' . $the_host . '] ' . $data['subject'];
        $message = $data['message'];

        // -> config
        $host_name = $cog['smtp_host_name'];
        if ($host_name == '') {
            return 'Host?';
        }
        //echo $host_name . '<br>' . "\n";
        $host_port = $cog['smtp_host_port'];
        $host_port *= 1;
        //echo $host_port . '<br>' . "\n";
        $host_user = $cog['smtp_host_user'];
        if ($host_user == '') {
            return 'User?';
        }
        $host_pass = $cog['smtp_host_pass'];
        //if ( $debug > 0 )echo $host_pass . '<br>' . "\n";
        if ($host_pass == '') {
            return 'Pass?';
        }

        // 
        if (isset($cog['smtp_from']) && strpos($cog['smtp_from'], '@') !== false) {
            $from = $cog['smtp_from'];
        } else if (strpos($host_user, '@') !== false) {
            $from = $host_user;
        } else {
            $from = 'admin@' . $the_host;
        }

        // 
        if (!isset($cog['smtp_from_name']) || empty($cog['smtp_from_name'])) {
            $cog['smtp_from_name'] = $the_host;
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
            if (isset($cog['smtp_secure']) && !empty($cog['smtp_secure'])) {
                $mail->SMTPSecure = $cog['smtp_secure'];
            } else if ($host_name == 'smtp.gmail.com') {
                if ($host_port == 465) {
                    $mail->SMTPSecure = 'ssl';
                } else if ($host_port == 587) {
                    $mail->SMTPSecure = 'tls';
                } else {
                    return 'ERROR! gmail port';
                }
                /*
            } else if ($host_name == 'smtp.pepipost.com') {
                $mail->SMTPSecure = 'tls';
                */
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
                $reply_to = 'no-reply@' . $the_host;
            }
            $mail->addReplyTo($reply_to, $from_name);
            $mail->SetFrom($from, $from_name);

            //
            foreach ($bcc_email as $v) {
                if ($v != '' && strpos($v, '@') !== false && $v != $to) {
                    $mail->AddBCC($v, $v);
                }
            }
            foreach ($cc_email as $v) {
                if ($v != '' && strpos($v, '@') !== false && $v != $to) {
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
                if (
                    $resend === true &&
                    isset($cog['smtp2_host_user']) &&
                    isset($cog['smtp2_host_pass']) &&
                    $cog['smtp2_host_user'] != '' &&
                    $cog['smtp2_host_pass'] != ''
                ) {
                    $cog['smtp_host_user'] = $cog['smtp2_host_user'];
                    $cog['smtp_host_pass'] = $cog['smtp2_host_pass'];
                    $cog['smtp_host_name'] = $cog['smtp2_host_name'];
                    $cog['smtp_secure'] = $cog['smtp2_secure'];
                    $cog['smtp_host_port'] = $cog['smtp2_host_port'];

                    //
                    if ($debug > 0) {
                        print_r($mail->ErrorInfo);
                        echo '<hr>' . "\n";
                        echo 'Username/ Email: ' . $cog['smtp_host_user'] . '<br>' . "\n";
                        echo 'Password: ' . substr($cog['smtp_host_pass'], 0, 6) . '******<br>' . "\n";
                        echo 'Hostname: ' . $cog['smtp_host_name'] . '<br>' . "\n";
                        echo 'Secure: ' . $cog['smtp_secure'] . '<br>' . "\n";
                        echo 'Port: ' . $cog['smtp_host_port'] . '<br>' . "\n";
                        echo '<hr>' . "\n";
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
