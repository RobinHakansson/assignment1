<?php
require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use GuzzleHttp\Client;

$log = new Logger('unicorns');
$log->pushHandler(new StreamHandler('visits.log', Logger::INFO));

// Skapa en HTTP-client
$client = new Client(['http_errors' => false]);

// Variabeln som innehåller texten som ska loggas
$logText = "";

//Om parametern id finns och inte är tom
if (!empty($_GET['id'])) {
    $res = $client->request(
      'GET',
      'http://unicorns.idioti.se/'.$_GET['id'],
      ['headers' => ['accept' => 'application/json']]
    );

    // Om HTTP-statuskoden är 200 (OK)
    if ($res->getStatusCode() == 200) {
        $logText = 'Requested info about: '.json_decode($res->getBody())->name;
    } else { // Om HTTP-statuskoden INTE är 200 (OK)
        $logText = 'Error: '.$res->getStatusCode().' - No unicorn with id '.$_GET['id'].' was found.';
    }
} else { //Om parametern id INTE finns eller är tom
    $res = $client->request(
      'GET',
      'http://unicorns.idioti.se/',
      ['headers' => ['accept' => 'application/json']]
    );
    $logText = 'Requested info about: all unicorns';
}

// Logga info om 'request'
$log->info($logText);

// Omvandla JSON-svar till datatyper
$data = json_decode($res->getBody());

?>
<!DOCTYPE html>
<html>
<head>
    <title>Example form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/css/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <a href="/" class="logo">
            <h1>
                Enhörningar
            </h1>
        </a>
        <p>Från <a href="http://unicorns.idioti.se/">http://unicorns.idioti.se/</a></p>

        <form action="/" method="get" class="form-inline">
            <div class="form-group">
                <label for="age">ID för enhörning: </label>
                <input type="number" id="id" name="id" class="form-control" required value="<?php echo !empty($_GET['id']) ? $_GET['id'] : ''; ?>">
                <button type="submit" class="btn btn-success">
                    <span class="glyphicon glyphicon-knight"></span> Visa enhörning
                </button>

            </div>
            <div class="form-group">
                <a href="/" class="btn btn-primary">
                    <span class="glyphicon glyphicon-list"></span> Lista alla enhörningar
                </a>
            </div>
        </form>
    <div id="result">
        <?php
        if ($data == null) { // Om data från API är NULL ska felmeddelande skrivas ut
            echo "<p>Ingen enhörning med det Id gick att hitta. Försök med ett annat Id.</p>";
        } elseif (is_array($data)) { // Om data är av datatypen array ska listan med enhörningar skrivas ut
            echo "
            <h2>Alla enhörningar</h2>
            <ul>";
            foreach ($data as $unicorn) {
                echo "
                <li>
                    <p>".
                        $unicorn->id.": ".$unicorn->name."
                    </p>
                    <a class='btn btn-success' href='/?id=".$unicorn->id."'>Läs mer</a>
                </li>";
            }
            echo "
            </ul>";
        } elseif (is_object($data)) { // Om data är av datatypen object ska enhörningen skrivas ut

            echo "<div class='row no-gutters'>
                <div class='col-md-5'>
                    <div class='row  no-gutters'>
                        <div class='col'>
                            <h2>
                                ".$data->name."
                            </h2>
                        </div>
                    </div>
                    <div class='row no-gutters'>
                        <div class='col'>
                            <p>
                                <em>".$data->spottedWhen."</em>
                            </p>
                        </div>
                    </div>
                    <div class='row no-gutters'>
                        <div class='col'>
                            <p>
                                ".$data->description."
                            </p>
                        </div>
                    </div>
                    <div class='row no-gutters'>
                        <div class='col'>
                            <p>
                                <strong>Plats: </strong>".$data->spottedWhere->name."
                            </p>
                        </div>
                    </div>
                    <div class='row no-gutters'>
                        <div class='col'>
                            <p>
                                <strong>Rapporterad av: </strong>".$data->reportedBy."
                            </p>
                        </div>
                    </div>
                </div>
                <div class='col-md-7'>
                    <img src='".$data->image."' alt='Bild på en ".$data->name."' class='img-responsive'>
                </div>
            </div>";
        }

        ?>
    </div>
</div>
<footer>
    <p>
        Skapad av Robin Håkansson
    </p>
    <p>
        2018-04-18
    </p>
</footer>
</body>
</html>
