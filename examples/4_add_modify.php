<?php
require "../JsonFactory.php";
require "../Dto_JsonFactory.php";

// "books.json" could be url
$json = file_get_contents("books.json");

// You can also add or calculate new columns from existing ones
$books = new Kudlas\JsonFactory();

// for create method, you have to supply json itself, and key where are the books stored
$books->create($json, "books")
    // now we can create new column by stating name, and a function which calculates value for each dataset.
      ->addColFunction("author_publisher", function ($val) { return $val["author"] . " / " . $val["publisher"]; })
    // apply modifies current column with stated function, i am using it here, to format date.
      ->apply("published", function ($date) { return date("Y/m/d H:i", strtotime($date) ); })
    // now I filter it, for shorter output of this example (and for the heck of it)
      ->filterCols(["isbn", "published", "author_publisher", "description"]);

var_dump($books->toArray());



