<?php

require_once(__FILES_ROOT_MAIL__ . '/lib/PHPMailer_5.2.1/class.phpmailer.php');

class mailMailerHelper extends mailMailerHelper_Parent
{

    /**
     * send : envoie un mail en passant par la fonction mail() de PHP par défaut, ou par SMTP avec PHPMailer si la config le permet du module mailer
     * 
     * @param mixed $params 
     * @access public
     * @return void
     */
    public function send($params)
    {
        $conf = Clementine::$config['module_mailer'];
        if (empty($conf['host'])) {
            // par défaut on passe par la fonction mail() de PHP
            // MIME BOUNDARY
            $mime_boundary = "---- " . preg_replace("/[^a-zA-Z0-9@\._+-]/", "", $params['fromname']) . " ----" . md5(time());
            // MAIL HEADERS
            $headers  = 'From: "' . addslashes($params['fromname']) . '" <' . $params['from'] . ">\n";
            $headers .= 'Reply-To: "' . addslashes($params['fromname']) . '" <' . $params['from'] . ">\n";
            $headers .= 'Return-Path: "' . addslashes($params['fromname']) . '" <' . $params['from'] . ">\n";
            if ($params['receipt']) {
                $headers .= 'Disposition-Notification-To: "' . addslashes($params['fromname']) . '" <' . $params['from'] . ">\n";
                $headers .= 'Return-Receipt-To: "' . addslashes($params['fromname']) . '" <' . $params['from'] . ">\n";
            }
            $headers .= "MIME-Version: 1.0\n";
            if (strlen($params['message_html'])) {
                $headers .= "Content-Type: multipart/alternative; boundary=\"$mime_boundary\"\n";
            }
            // TEXT EMAIL PART
            $message = "";
            if (strlen($params['message_html'])) {
                $message .= "\n--$mime_boundary\n";
                $message .= "Content-Type: text/plain; charset=" . __PHP_ENCODING__ . "\n";
                $message .= "Content-Transfer-Encoding: 8bit\n\n";
            }
            $message .= $this->getModel('fonctions')->html_entity_decode(stripslashes($params['message_text']));
            // HTML EMAIL PART
            if (strlen($params['message_html'])) {
                $message .= "\n--$mime_boundary\n";
                $message .= "Content-Type: text/html; charset=" . __PHP_ENCODING__ . "\n";
                $message .= "Content-Transfer-Encoding: 8bit\n\n";
                $message .= "<html>\n";
                $message .= "<body>\n";
                $message .= stripslashes($params['message_html']);
                $message .= "</body>\n";
                $message .= "</html>\n";
                // FINAL BOUNDARY
                $message .= "\n--$mime_boundary--\n\n";
            }
            if ($params['anonymize']) {
                $regexp_anonymize = '/__CLEMENTINE_MAIL_ANONYMIZE_START__(.*\r*\n*)+__CLEMENTINE_MAIL_ANONYMIZE_STOP__/mU';
                $message = preg_replace($regexp_anonymize, '######', $message);
            } else {
                $message = str_replace('__CLEMENTINE_MAIL_ANONYMIZE_START__', '', $message);
                $message = str_replace('__CLEMENTINE_MAIL_ANONYMIZE_STOP__', '', $message);
            }
            return mail($params['to'], $params['title'], $message, $headers);
        } else {
            // si SMTP on passe par PHPMailer
            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->CharSet = __PHP_ENCODING__;
            if ($conf['debug']) {
                $mail->SMTPDebug = 1; // 1 = errors and messages, 2 = messages only
            }
            $mail->Host       = $conf['host'];
            if ($conf['secure']) {
                $mail->SMTPSecure = $conf['secure'];
            }
            $mail->Port = 25; // port par défaut
            if ($conf['port']) {
                $mail->Port = $conf['port'];
            }
            if ($conf['user']) {
                $mail->SMTPAuth = true;
                $mail->Username = $conf['user'];
                $mail->Password = $conf['pass'];
            }
        }
        $mail->SetFrom($params['from'], $params['fromname']);
        $mail->AddReplyTo($params['from'], $params['fromname']);
        $mail->Subject = $params['title'];
        $mail->AltBody = $params['message_text'];
        $mail->MsgHTML($params['message_html']);
        $mail->AddAddress($params['to'], $params['toname']);
        return $mail->Send();
    }

}
?>
