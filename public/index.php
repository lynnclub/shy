<?php

require __DIR__ . '/../bootstrap/http.php';

shy('request')->initialize($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, file_get_contents('php://input'));
shy('session')->sessionStart();
shy(Shy\Http::class)->run();
