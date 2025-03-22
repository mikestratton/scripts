<?php

$envFile = __DIR__ . '/.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        $_ENV[$name] = $value;
    }
    $api_key = $_ENV['GIPHY_API_KEY'];

} else {
    echo ".env file not found.";
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if($_POST['name'] == '') unset($_POST['name']);

    $var = $_POST['name'] ?? 'programming';
    $stringWithUnderscores = str_replace(" ", "_", $var);
    $url = "http://api.giphy.com/v1/gifs/search?q=" . $stringWithUnderscores . "&api_key=" . $api_key . "&limit=5";
    $giphy = json_decode(file_get_contents($url));
    $results = 0;
    foreach($giphy->data as $key=>$image){
        if(!empty($image)){
            $img = 'https://i.giphy.com/'. $image->id . '.webp';
            echo "<img height='250px' src='$img' alt='giphy'>";
            $results++;
        }
    }

    echo "<h1>You searched for: " . $var . " which returned " . $results . " results.</h1>";
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Form Example</title>
</head>
<body>

<form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    Search: <input type="text" name="name" autofocus><br><br>
    <input type="submit" name="submit" value="Submit">
</form>

</body>
</html>
