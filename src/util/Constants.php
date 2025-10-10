<?php
namespace GithubProjectViewer\Util;

$env = parse_ini_file(__DIR__ . '/../../.env');
$apiKey = $env['APIKEY'];
$baseURL = $env['baseURL'];
$veryLongTimeout = (int)$env['veryLongTimeout'];
$dayTimeout = (int)$env['dayTimeout'];
$githubAuthKey = $env['githubAuthKey'] ?? null;