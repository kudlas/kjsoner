<?php
require "../JsonFactory.php";
require "../Dto_JsonFactory.php";

// "books.json" could be url
$json = file_get_contents("facets.json");

// Now we have this structure of facets with many levels
// lets say I want to save every country to my db with id of entity, it was extracted from
$facets = new Kudlas\JsonFactory();

// for create method, you have to supply json itself, and key where are the books stored
$facets->create($json, "Facets");

var_dump($facets->toArray());



