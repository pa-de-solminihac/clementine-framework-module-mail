<?php

require_once(__FILES_ROOT_MAIL__ . '/lib/PHPMailer_5.2.21/PHPMailerAutoload.php');

class mailMailerHelper extends mailMailerHelper_Parent
{

    /**
     * send : envoie un mail en passant par PHPMailer (par fonction mail() ou par SMTP)
     *   remarque : en dupliquant ce module (par héritage dans le fichier config.ini),
     *   on peut utiliser plusieurs SMTP différents
     * 
     * @param mixed $params['to'] : email destinataire
     * @param mixed $params['message_html'] : message au format html
     * @param mixed $params['title'] : titre du message
     * @param mixed $params['from'] : email expéditeur (facultatif)
     * @param mixed $params['fromname'] : nom expéditeur (facultatif)
     * @param mixed $params['toname'] : nom destinataire (facultatif)
     * @param mixed $params['message_text'] : message au format texte (facultatif)
     * @param mixed $params['anonymize'] : anonymiser le message (facultatif)
     * @param mixed $params['receipt'] : adresse à laquelle envoyer un accusé de réception. si true mais pas une adresse email, l'accusé sera envoyé à $params['from'] (facultatif)
     * @access public
     * @return void
     */
    public function send($params)
    {
        // parametres
        if (empty($params['to'])) {
            return false;
        }
        if (!isset($params['message_html'])) {
            return false;
        }
        if (!isset($params['title'])) {
            return false;
        }
        if (empty($params['fromname'])) {
            $params['fromname'] = '';
        }
        if (empty($params['message_text'])) {
            $params['message_text'] = strip_tags($params['message_html']);
        }
        // configuration
        $conf = Clementine::$config['module_mailer'];
        $mail = new PHPMailer();
        if ($conf['host']) {
            $mail->IsSMTP();
        } else {
            $mail->IsMail();
        }
        $mail->CharSet = __PHP_ENCODING__;
        if ($conf['debug']) {
            $mail->SMTPDebug = 1; // 1 = errors and messages, 2 = messages only
        }
        // par defaut, on prend la config du php.ini
        $mail->Host = ini_get('SMTP');
        $mail->Port = ini_get('smtp_port');
        if (!empty($conf['host'])) {
            $mail->Host = $conf['host'];
            if ($conf['secure']) {
                $mail->SMTPSecure = $conf['secure'];
            }
            if ($conf['port']) {
                $mail->Port = $conf['port'];
            }
            if ($conf['user']) {
                $mail->SMTPAuth = true;
                $mail->Username = $conf['user'];
                $mail->Password = $conf['pass'];
            }
        }
        if (!empty($params['from'])) {
            $mail->SetFrom($params['from'], $params['fromname']);
            $mail->AddReplyTo($params['from'], $params['fromname']);
        }
        // ask for confirmation
        if (!empty($params['receipt'])) {
            if (strpos($params['receipt'], '@')) {
                $mail->ConfirmReadingTo = $params['receipt'];
            } elseif (!empty($params['from'])) {
                $mail->ConfirmReadingTo = $params['from'];
            }
        }
        // message parts anonymization
        if (!empty($params['anonymize'])) {
            $regexp_anonymize = '/__CLEMENTINE_MAIL_ANONYMIZE_START__(.*\r*\n*)+__CLEMENTINE_MAIL_ANONYMIZE_STOP__/mU';
            $params['message_text'] = preg_replace($regexp_anonymize, '######', $params['message_text']);
            $params['message_html'] = preg_replace($regexp_anonymize, '######', $params['message_html']);
        } else {
            $params['message_text'] = str_replace('__CLEMENTINE_MAIL_ANONYMIZE_START__', '', $params['message_text']);
            $params['message_html'] = str_replace('__CLEMENTINE_MAIL_ANONYMIZE_START__', '', $params['message_html']);
            $params['message_text'] = str_replace('__CLEMENTINE_MAIL_ANONYMIZE_STOP__', '', $params['message_text']);
            $params['message_html'] = str_replace('__CLEMENTINE_MAIL_ANONYMIZE_STOP__', '', $params['message_html']);
        }
        $mail->XMailer = 'Clementine Mail Helper';
        $mail->Subject = $params['title'];
        $mail->AltBody = $params['message_text'];
        $mail->MsgHTML($params['message_html']);
        $mail->AddAddress($params['to'], $params['toname']);
        return $mail->Send();
    }

    /**
     * Renvoie la liste des mailers de secours.
     * @return array
     */
    public function getFallback() {
        $conf = Clementine::$config['module_' . $this->getCurrentModule()];
        if (array_key_exists('fallback', $conf)) {
            return explode(',', $conf['fallback']);
        }
        else return array();
    }
}
?>
