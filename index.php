<?php

require 'vendor/autoload.php';

use fjgarlin\StravaImporter;

// http://php.net/manual/en/function.str-getcsv.php
function csvToArray($file_path) {
    $csv = array_map('str_getcsv', file($file_path));
    array_walk($csv, function(&$a) use ($csv) {
        $a = array_combine($csv[0], $a);
    });
    array_shift($csv); # remove column header

    return $csv;
}

//********** somewhere in your routes or controllers...
//functionality here
$credentials = json_decode(file_get_contents('.cred'));
$config = [
    'id' => $credentials->id,
    'secret' => $credentials->secret,
    'redirect_url' => 'http://' . $_SERVER['HTTP_HOST']
];
$importer = new StravaImporter($config);

$code = isset($_GET['code']) ? $_GET['code'] : false;
if ($code) {
    $importer->authorize($code);
}

$authorized = $importer->authorized();
$authorize_url = ($authorized) ? false : $importer->getAuthorizeUrl();
$athlete = $importer->getAthlete();

$res = null;
if ($authorized and !empty($_POST) and !empty($_FILES)) {
    //get parsed data
    $csv = csvToArray($_FILES['activities']['tmp_name']);

    //and upload
    $res = $importer->upload($csv);
}
//********** somewhere in your routes or controllers...

?>
<!doctype html>
<html>
    <head>
        <title>Strava Uploader</title>
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    </head>
    <body>
        <div class="container">
            <h1>
                Strava CSV Uploader <br>
                <small>Upload your CSV with activities to Strava.</small>
            </h1>
            <hr>

            <?php if (!$authorized): ?>
                <p>
                    <a href="<?php echo $authorize_url; ?>" title="Authorize">
                        <img src="img/btn_strava_connectwith_orange.png" alt="Connect with Strava">
                    </a>
                </p>
            <?php else: ?>
                <div class="row">
                    <div class="col-sm-6">
                        <p>
                            Please follow the following column headers.
                            <ul>
                                <li><b>name</b> : Name of activity</li>
                                <li><b>date</b> : Date in ISO 8601 (ie: 2016-11-11T11:07:59Z)</li>
                                <li><b>distance</b> : Distance (miles)</li>
                                <li><b>time</b> : Time (minutes)</li>
                            </ul>
                            Attach the file and click on Submit.
                        </p>
                    </div>
                    <div class="col-sm-6">
                        <form method="post" action="" class="well" enctype="multipart/form-data">
                            <input type="hidden" name="_submitted">
                            <?php if ($athlete): ?>
                                <div class="thumbnail text-center">
                                    <div class="caption">
                                        <h3><?php echo htmlspecialchars($athlete->firstname . " " . $athlete->lastname); ?></h3>
                                        <p><?php echo htmlspecialchars($athlete->email); ?></p>
                                        <p><a target="_blank" href="https://www.strava.com/athletes/<?php echo (int)$athlete->id; ?>" class="btn btn-primary" role="button">View profile</a></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="form-group">
                                <label for="activities">File input</label>
                                <input type="file" id="activities" required="required" name="activities">
                                <p class="help-block">CSV containing the activities.</p>
                            </div>
                            <button type="submit" class="btn btn-default">Submit</button>
                        </form>
                    </div>
                </div>

                <?php if (!is_null($res)): ?>
                    <hr>
                    <?php if ($res->status): ?>
                        <div class="alert alert-success">
                            Activities uploaded.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            Couldn't upload activities. <?php echo htmlspecialchars($res->message); ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div><!-- .container -->
        <footer class="text-right container">
            <hr>
            <img height="40" src="img/api_logo_pwrdBy_strava_horiz_light.png" alt="Powered by Strava">
        </footer>
    </body>
</html>