# microsoft-teams-component
yii2 component for Microsoft Teams Message Card and Adaptive Cart

Based on this repositories:
- https://github.com/laravel-notification-channels/microsoft-teams
- https://github.com/sebbmeyer/php-microsoft-teams-connector

This is not a complete package, just an implementation example. And also, there may be references to classes that are not provided here.

```php
$message = AdaptiveCard::create()    
    ->title('Title #1')
    ->text($content)
    ->fact('Single fact#1', 'Some text **here**')
    ->fact('Single fact#2', 'More test')
    ->factSet([
        Fact::create('Fact Set', 'Few grouped values'),
        Fact::create('Assigned to', 'Some Person'),
        Fact::create('Status', 'Not started'),
    ])
    ->title('Title #2')
    ->text('Hi <at>Roman Bat\'kovich</at>! This is mention in text')
    ->mention($email, 'Roman Bat\'kovich')
    ->textMention($email, 'Roman Bat\'kovich')
    ->image('https://adaptivecards.io/content/cats/1.png')
    ->textMention($email, 'Any Name For Mention')
    ->button('Button #1', $url1)
    ->button('Button #2', $url2);
Yii::$app->microsoftTeams->send($message);
```

![result](https://github.com/Tahiaji/microsoft-teams-component/blob/main/example.png?raw=true)
