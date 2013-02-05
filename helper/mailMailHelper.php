<?php
class mailMailHelper extends mailMailHelper_Parent
{

    /**
     * send : envoie un mail en donnant le bon mailer en fonction du type de mail
     * 
     * @param mixed $params 
     * @access public
     * @return void
     */
    public function send($params)
    {
        $conf = Clementine::$config['module_mail'];
        // check champs
        if (!empty($params['to'])) {
            // Securite antispam : les emails ne doivent contenir que des caracteres mail et il ne doit pas y avoir de retours a la ligne avant le titre
            $params['to'] = preg_replace("/[^a-zA-Z0-9@\._+-]/", "", $params['to']);
        }
        if (empty($params['to'])) {
            // missing to
            return false;
        }
        if (!empty($params['from'])) {
            // Securite antispam : les emails ne doivent contenir que des caracteres mail et il ne doit pas y avoir de retours a la ligne avant le titre
            $params['from']  = preg_replace("/[^a-zA-Z0-9@\._+-]/", "", $params['from']);
        }
        if (empty($params['from'])) {
            // missing from
            return false;
        }
        if (!empty($params['fromname'])) {
            $params['fromname'] = preg_replace("/;*/",  "", $params['fromname']);
            $params['fromname'] = preg_replace("/\r*/", "", $params['fromname']);
            $params['fromname'] = preg_replace("/\n*/", "", $params['fromname']);
        }
        if (empty($params['fromname'])) {
            // default
            $params['fromname'] = $params['from'];
        }
        if (!empty($params['toname'])) {
            $params['toname'] = preg_replace("/;*/",  "", $params['toname']);
            $params['toname'] = preg_replace("/\r*/", "", $params['toname']);
            $params['toname'] = preg_replace("/\n*/", "", $params['toname']);
        }
        if (empty($params['toname'])) {
            // default
            $params['toname'] = $params['to'];
        }
        if (!empty($params['title'])) {
            $params['title'] = preg_replace("/\r*/", "", $params['title']);
            $params['title'] = preg_replace("/\n*/", "", $params['title']);
            $params['title'] = mb_encode_mimeheader($params['title']); // pour passer au travers des antispam (cf. SUBJECT_NEEDS_ENCODING et SUBJ_ILLEGAL_CHARS de SpamAssassin)
        }
        if (empty($params['title'])) {
            // missing title
            return false;
        }
        if (empty($params['message_text']) && empty($params['message_html'])) {
            // missing message (either text or html)
            return false;
        } elseif (empty($params['message_text'])) {
            // seul le html est fourni, on génère la version texte
            $params['message_text'] = $this->getModel('fonctions')->strip_tags(str_replace('<hr />', '------------------------------',
                                                                               str_replace('<br />', "\n", $params['message_html']))) . "\n";
        }
        // envoie le mail au mailer
        if (empty($params['mailer'])) {
            $this->getHelper('mailer')->send($params);
        } else {
            // mailer par défaut
            $mailer = $conf['default'];
            if (!empty($params['type'])) {
                if (!empty($conf[$params['type']])) {
                    $mailer = $conf[$params['type']];
                }
            }
            $this->getHelper($mailer)->send($params);
        }
    }

    public function sendwrap()
    {
    }

}
?>
