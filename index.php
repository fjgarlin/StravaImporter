<?php

require 'vendor/autoload.php';

//********** somewhere in your routes or controllers...
function upload($post) {
    //process post
    $data = null;

    $config = [
        'id' => 'CLIENT-ID',
        'secret' => 'CLIENT-SECRET',
        'redirect_url' => 'http://localhost:8888/'    //URL where you want to be redirected after authorizing this app
    ];
    $importer = new fjgarlin\StravaCsvImporter($config, $data);
    return $importer->upload();
}

$res = null;
if (!empty($_POST)) {
    //validate...

    //and upload
    $res = upload($_POST);
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
            <h1>Strava CSV Uploader</h1>
            <hr>
            <div class="row">
                <div class="col-sm-6">
                    <p class="lead">Upload your CSV with activities to Strava.</p>
                    <p>
                        Please follow the following column headers.
                    <ul>
                        <li><b>name</b> : Name of activity</li>
                        <li><b>date</b> : Date (dd/mm/yyyy hh:mm)</li>
                        <li><b>distance</b> : Distance (miles)</li>
                        <li><b>time</b> : Time (minutes)</li>
                    </ul>

                    Then add your credentials (nothing gets stored anywhere) and attach the file and click on Submit.
                    </p>
                </div>
                <div class="col-sm-6">
                    <form method="post" action="" class="well">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" required="required" name="username" placeholder="Username">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" required="required" name="password" placeholder="Password">
                        </div>
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

        </div>
    </body>
</html>