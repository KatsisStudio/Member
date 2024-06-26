<?php

require_once "vendor/autoload.php";

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader(["templates"]);
$twig = new Environment($loader);

function clean($name) {
    return strtolower(str_replace(' ', '', $name));
}

if (str_starts_with($_SERVER['HTTP_HOST'], "localhost"))
{
    $name = "fractal";
}
else
{
    $name = explode('.', clean($_SERVER['HTTP_HOST']))[0];
}

$json = isset($_GET["json"]) && $_GET["json"] === "1";
if ($json) {
    header('Content-Type: application/json; charset=utf-8');
    echo "TODO";
} else {
    $data = json_decode(file_get_contents("data/config.json"), true);
    $members = json_decode(file_get_contents($data["members"]), true);
    $images = json_decode(file_get_contents($data["gallery"]), true);
    $projects = json_decode(file_get_contents($data["project"]), true);

    $endImages = [];
    $endComics = [];
    $endProjects = [];

    foreach (array_reverse($images) as $img) {
        if (clean($img["author"]) === $name) {
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
        if (in_array($name, array_map("clean", $metadata["members"])))
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

    foreach (array_reverse($projects) as $p) {
        if (($p["type"] === "game" || $p["type"] === "gamejam") && in_array($name, array_map("clean", $p["members"]))) {
            array_push($endProjects, [
                "baseFolder" => $p["baseFolder"],
                "preview" => $p["preview"],
                "link" => $p["links"][0]["content"]
            ]);

            if (count($endProjects) === 5)
            {
                break;
            }
        }
    }

    foreach ($members as $m) {
        if (clean($m["name"]) == $name)
        {
            echo $twig->render("index.html.twig", [
                "member" => $m,
                "images" => $endImages,
                "comics" => $endComics,
                "projects" => $endProjects
            ]);
            return;
        }
    }
    http_response_code(404);
}