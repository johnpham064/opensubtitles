
<!DOCTYPE html>
<html>
<head>
	
</head>
<body>



<div class="container" id="main-content">
	<h2>Welcome to my website!</h2>
	<p>Some content goes here! Let's go with the classic "lorem ipsum."</p>

	
</div>

<?php 


include("Client.php");
$client = KickAssSubtitles\OpenSubtitles\Client::create([
    'username'  => 'patuan03',
    'password'  => 'P@ssword1234',
    'useragent' => 'TemporaryUserAgent',
]);

$response = $client->searchSubtitles([
    [
        'query' => 'Insurgent',
        'tag' => 'Insurgent.2015.READNFO.CAM.AAC.x264-LEGi0N',
    ]
]);

var_dump($response->toArray());

?>

</body>
</html>