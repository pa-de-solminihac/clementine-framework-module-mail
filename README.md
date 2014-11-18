#clementine-framework-module-mail

__mail__ permet d'envoyer des emails en passant par un __mailer__.

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

##Utiliser plusieurs mailers

On peut spécifier le __mailer__ à utiliser pour un email donné par le biais de la variable `$params['mailer']`

Pour définir de nouveaux mailers, il suffit d'utiliser la fonctionnalité d'adoption proposée par Clémentine, dans un ficiher `config.ini` :
```ini
[clementine_inherit_helper]
mailchimp=mailer
mailjet=mailer
```
