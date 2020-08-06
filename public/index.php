<?php

require __DIR__ . '/../bootstrap/http.php';

shy('request')->initialize($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);
shy('session')->sessionStart();
shy(Shy\Http::class)->run();
