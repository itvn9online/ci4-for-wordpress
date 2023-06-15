<?php

namespace App\ThirdParty;

/*
 * Chức năng kết nối và gửi thông báo thông qua telegram
 * https://kb.hostvn.net/huong-dan-tao-bot-va-gui-thong-bao-telegram_633.html
 * https://kb.hostvn.net/hung-dn-tao-bot-canh-bao-dang-nhap-ssh-qua-telegram_671.html
 */

class TelegramBot
{
    protected static function resultErrorMsg()
    {
        return self::resultMsg($msg);
    }
    protected static function resultOkMsg($msg)
    {
        return self::resultMsg($msg, 1);
    }
    protected static function resultMsg($msg, $ok = 0)
    {
        return [
            'msg' => $msg,
            'ok' => $ok,
        ];
    }

    protected static function getCog()
    {
        $option_model = new \App\Models\Option();
        return $option_model->get_smtp();
    }

    // GET chat ID
    public static function getUpdates($cog = NULL)
    {
        if ($cog === NULL) {
            $cog = self::getCog();
            //print_r( $cog );
        }
        if (!isset($cog->telegram_bot_token) || empty($cog->telegram_bot_token)) {
            return self::resultErrorMsg('ERROR bot token?');
        }

        //
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.telegram.org/bot' . $cog->telegram_bot_token . '/getUpdates',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => array(
                'cmd' => '_notify-synch',
                'tx' => '54E3398386635404W',
                'at' => 'vGyTxoxDseiSWeryncdVbF4uIEcguRK9Ga2DizaKtz964Iyw4Z68SwyLLTu'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;
        $response = json_decode($response);
        //print_r( $response );

        //
        return self::resultOkMsg($response);
    }

    public static function sendMessage($text, $cog = NULL)
    {
        if ($cog === NULL) {
            $cog = self::getCog();
            //print_r( $cog );
        }
        if (!isset($cog->telegram_bot_token) || empty($cog->telegram_bot_token)) {
            return self::resultErrorMsg('ERROR bot token?');
        }
        if (!isset($cog->telegram_chat_id) || empty($cog->telegram_chat_id)) {
            return self::resultErrorMsg('ERROR chat id?');
        }

        //
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.telegram.org/bot' . $cog->telegram_bot_token . '/sendMessage?chat_id=' . $cog->telegram_chat_id . '&text=' . urlencode($text),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_POSTFIELDS => array(
                'cmd' => '_notify-synch',
                'tx' => '54E3398386635404W',
                'at' => 'vGyTxoxDseiSWeryncdVbF4uIEcguRK9Ga2DizaKtz964Iyw4Z68SwyLLTu'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        //echo $response;
        $response = json_decode($response);
        //print_r( $response );

        //
        return self::resultOkMsg($response);
    }
}
