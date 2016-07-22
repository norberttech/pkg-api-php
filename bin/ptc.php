#!/usr/bin/env php
<?php

include __DIR__ . '/../vendor/autoload.php';

$sdk = new \PkgClient\SDK(new \Psr\Log\NullLogger());

$session = $sdk->login($argv[1], $argv[2]);
$player = $sdk->player($session);

var_dump($player);