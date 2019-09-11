<?php
require "../JsonFactory.php";
require "../Dto_JsonFactory.php";

// this could be url
$json = file_get_contents("books.json");

// lets say I want array of titles from books
$books = new Kudlas\JsonFactory();

// for create method, you have to supply json itself, and key where are the books stored
$books->create($json, "books");

// now I not only filter, but I am also able to rename isbn to id (or any other columns)
var_dump($books->colToArray('title')    );

