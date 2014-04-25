<?php

function __autoload($class_name) {
    print $class_name;
    include 'vanilla/' . $class_name . '.php';
}