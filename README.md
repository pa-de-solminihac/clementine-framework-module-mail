#Clementine Framework : module Mail

Le module __mail__ permet d'envoyer des emails en passant par un __mailer__.

##Configuration

Le __mailer__ utilisé par défaut est configurable dans `config.ini`. 
```ini
[module_mailer]
default=mailer
```

Cette configuration indique que le module __mail__ appellera le __mailer__ ainsi : 
```php 
$mailer = $this->getHelper('mailer');
$mailer->send($params);
```

##Définir plusieurs mailers

Pour définir de nouveaux mailers, il suffit d'utiliser la fonctionnalité d'adoption proposée par Clémentine :
- déclarer l'adoption dans le fichier `config.ini` :
```ini
[clementine_inherit_helper]
mailchimp=mailer
mailjet=mailer
```

- définir ensuite les classes helper correspondantes : 
  - fichier `app/local/site/helper/siteMailchimpHelper.php` : 
```php
class siteMailchimpHelper extends siteMailchimpHelper_Parent
{
}
```
  - fichier `app/local/site/helper/siteMailjetHelper.php` : 
```php
class siteMailjetHelper extends siteMailjetHelper_Parent
{
}
```

On configurera alors les modules mailer ainsi, dans le fichier `config.ini` :
```ini
[module_mailjet]
debug=1
secure=
port=587
host=in.mailjet.com
user=
pass=
fallback=mailchimp

[module_mailchimp]
debug=0
secure=
port=
host=
user=
pass=
```

Voilà, on peut maintenant dire par quel __mailer__ doivent passer les emails en utilisant la variable `$params['mailer']` lors de l'envoi d'un mail :
```php
$params['mailer'] = 'mailjet';
$this->getHelper('mail')->send($params);
```
