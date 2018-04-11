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
            $params['to'] = preg_replace("/[^a-zA-Z0-9@\._\/='\"+-]/", "", $params['to']);
        }
        if (empty($params['to'])) {
            // missing to
            return false;
        }
        if (!empty($params['from'])) {
            // Securite antispam : les emails ne doivent contenir que des caracteres mail et il ne doit pas y avoir de retours a la ligne avant le titre
            $params['from']  = preg_replace("/[^a-zA-Z0-9@\._\/='\"+-]/", "", $params['from']);
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

        // envoie le mail au(x) mailer(s)
        $mailer = $conf['default'];
        if (!empty($params['mailer'])) {
            $mailer = $params['mailer'];
        }

        return $this->sendWithFallback($mailer, $params);
    }

    public function sendwrap($params)
    {
    }

    protected function sendWithFallback($mailer, $params)
    {
        return $this->recursiveSend($mailer, $params)['result'];
    }

    /**
     * Tente d'envoyer un mail en essayant tous les fallbacks définis
     * @param   string  $mailer nom du helper mailer
     * @param   mixed   $params
     * @param   array   $ignoreFallbacks    liste de helpers à ignorer
     * @return  array   resultat et helpers déjà utilisés (à ignorer pour éviter les boucles)
     */
    protected function recursiveSend($mailer, $params, $ignoreFallbacks = array())
    {
        array_push($ignoreFallbacks, $mailer);
        $mailerHelper = $this->getHelper($mailer);
        $mailerResult = $mailerHelper->send($params);

        if (! $mailerResult) {
            $fallbacks = $mailerHelper->getFallback();
            foreach ($fallbacks as $fallbackMailer) {
                if (! in_array($fallbackMailer, $ignoreFallbacks)) {
                    $swfResult = $this->recursiveSend($fallbackMailer, $params, $ignoreFallbacks);
                    $mailerResult = $swfResult['result'];
                    $ignoreFallbacks = $swfResult['ignore'];
                    if ($mailerResult) break;
                }
            }
        }

        return array(
            'result' => $mailerResult,
            'ignore' => $ignoreFallbacks,
        );
    }

}
?>
