#clementine-framework-module-mail

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

On peut spécifier le __mailer__ à utiliser pour un email donné par le biais de la variable `$params['mailer']`

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

On configurera alors les modules mailer ainsi :
```ini
[module_mailjet]
debug=1
secure=
port=587
host=in.mailjet.com
user=
pass=

[module_mailchimp]
debug=0
secure=
port=
host=
user=
pass=
```
