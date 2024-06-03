<?php

require_once "vendor/autoload.php";

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader(["templates"]);
$twig = new Environment($loader);

if (str_starts_with($_SERVER['HTTP_HOST'], "localhost"))
{
    $name = "fractal";
}
else
{
    $name = strtolower(str_replace(' ', '', explode('.', $_SERVER['HTTP_HOST'])[0]));
}

$json = isset($_GET["json"]) && $_GET["json"] === "1";
if ($json) {
    header('Content-Type: application/json; charset=utf-8');
    echo "TODO";
} else {
    $members = json_decode(file_get_contents("data/members.json"), true);
    foreach ($members as $m) {
        if (strtolower($m["name"]) == $name)
        {
            echo $twig->render("index.html.twig", [
                "name" => $m["name"]
            ]);
            return;
        }
    }
    http_response_code(404);
}