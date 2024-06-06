<?php

require_once "vendor/autoload.php";

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader(["templates"]);
$twig = new Environment($loader);

function toLower($name) {
    return strtolower($name);
}

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
    $data = json_decode(file_get_contents("data/config.json"), true);
    $members = json_decode(file_get_contents($data["members"]), true);
    $images = json_decode(file_get_contents($data["gallery"]), true);

    $endImages = [];
    $endComics = [];

    foreach (array_reverse($images) as $img) {
        if ($img["author"] === $name) {
            array_push($endImages, [
                "id" => $img["id"],
                "format" => $img["format"]
            ]);

            if (count($endImages) === 5)
            {
                break;
            }
        }
    }

    $comics = glob($data["comic"] . "*", GLOB_ONLYDIR);
    foreach ($comics as $path) {
        $metadata = json_decode(file_get_contents("$path/info.json"), true);
        if (in_array($name, array_map("toLower", $metadata["members"])))
        {
            array_push($endComics, [
                "id" => basename($path)
            ]);

            if (count($endComics) === 5)
            {
                break;
            }
        }
    }

    foreach ($members as $m) {
        if (strtolower($m["name"]) == $name)
        {
            echo $twig->render("index.html.twig", [
                "name" => $m["name"],
                "images" => $endImages,
                "comics" => $endComics
            ]);
            return;
        }
    }
    http_response_code(404);
}