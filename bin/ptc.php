#!/usr/bin/env php
<?php

include __DIR__ . '/../vendor/autoload.php';

$sdk = new \PkgClient\SDK(new \Psr\Log\NullLogger());

$location = new \PkgClient\SDK\Location('50.0466813', '19.8647908');
$session = $sdk->login($argv[1], $argv[2], $location);
$player = $sdk->player($session);

var_dump($player);