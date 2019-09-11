<?php
require "../JsonFactory.php";
require "../Dto_JsonFactory.php";

// this could be url
$json = file_get_contents("books.json");

// lets say I only want isbn, author and title, from all the books
$books = new Kudlas\JsonFactory();

// for create method, you have to supply json itself, and key where are the books stored
$books->create($json, "books");

// now I not only filter, but I am also able to rename isbn to id (or any other columns)
$books->filterCols(["isbn", "author", "title"])
      ->rename("id", "isbn"); // chaining methods is possible

// array output
var_dump($books->toArray());

