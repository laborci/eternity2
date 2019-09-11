<?php

use Eternity2\System\Env\Env;

function env($key = null){ return Env::Service()->get($key); }
function setenv($key, $value){ Env::Service()->set($key, $value); }
function unsetenv($key){ Env::Service()->unset($key); }