<?php

/**
 * File: process.php
 * Author: Justin Lewis Salmon
 * 
 * Description:
 *      This file gets called whenever the user switches between 
 *      towns/twins. The name of the town/twin will be passed in 
 *      via GET, and the appropriate SESSION variable will be set.
 */
session_start();

if (isset($_POST['town'])) {
    $_SESSION['town'] = $_POST['town'];
} 
if (isset($_POST['twin'])) {
    $_SESSION['twin'] = $_POST['twin'];
}
?>
