<?php
require "../JsonFactory.php";
require "../Dto_JsonFactory.php";

// this could be url
$json = file_get_contents("books.json");

// lets say I only want isbn, author and title, from all the books
$books = new Kudlas\JsonFactory();

// for create method, you have to supply json itself, and key where are the books stored
$books->create($json, "books");

// this method gets rid of every column, that is not stated as item of array param.
$books->filterCols(["isbn", "author", "title"]);

// array output
var_dump($books->toArray());

