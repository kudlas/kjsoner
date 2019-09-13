<?php
require "../JsonFactory.php";
require "../Dto_JsonFactory.php";

// "books.json" could be url
$json = file_get_contents("cakes.json");

// Now we have json of cakes, every cakes has topping
// lets say we want to save information about each cake's topping to a table with structure
// cake_id => topping_id
$cakes = new Kudlas\JsonFactory();

// for create method, you have to supply json itself, and key where are the cakes stored
// kjsoner expects data to be array of objects stored inside key, in this case "cakes"
$cakes->create($json, "cakes");

// this very special method inject column from upper structure to lower. In this case we want cake id
// in each topping
// First argument is key of toppings array, second is which column of cake (id) i want toppings to have,
// third argument is not mandatory, its new name of the column inside toppings
$cakes->plungeValue("topping", "id", "parent_id");

// as you can see, every topping now have its parent_id, which we have plunged
var_dump($cakes->colToArray("topping"));

// But still I have array of arrays plus I neeed to get rid of "type" column
// One of the solutions is make JsonFactory out of topping column, which is what colToFactory is used for
$toppings = $cakes->colToFactory("topping");

// now I can use all methods availible in JsonFactory, lets do some renames and filtering
$toppings
    ->rename("cake_id", "parent_id")
    ->rename("topping_id", "id")
    ->filterCols(["cake_id", "topping_id"]);

// final result
var_dump($toppings->toArray());



