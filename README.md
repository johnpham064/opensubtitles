# PHP client for OpenSubtitles API

## Usage HELLO

```php
$client = KickAssSubtitles\OpenSubtitles\Client::create([
    'username'  => 'USERNAME',
    'password'  => 'PASSWORD',
    'useragent' => 'USERAGENT',
]);

$response = $client->searchSubtitles([
    [
        'sublanguageid' => 'pol',
        'moviehash' => '163ce22b6261f50a',
        'moviebytesize' => '2094235131',
    ]
]);

var_dump($response->toArray());
```
