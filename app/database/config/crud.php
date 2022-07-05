<?php 

namespace App\Database\Config;

interface crud {
    function create();
    function read();
    function update();
    function delete();
}