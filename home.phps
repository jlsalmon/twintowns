<?php
/**
 * File: home.php
 * Author: Justin Lewis Salmon
 * 
 * Description:
 *      This file generates the content for the Home tab on the site.
 *      The Home tab includes a small weather box, geolocation data,
 *      Image galleries and Twitter feeds.
 */
include 'lib.php';

session_start();
$config = loadConfig();
?>

<div id="weather_box">
    <? include 'weather_box.php'; ?>
</div>
<div class="clear"></div>

<div id="info">
    <? include 'time.php'; ?> 
    <div class="clear"></div>

    <div id="geo">Getting your location...</div>
    <div class="clear"></div>

    <div id="gallery">
        <? include 'gallery.php'; ?>
    </div>
    <div class="clear"></div>
</div>
<? printImageAttrib(); ?>
<div class="clear"></div>

<div id="twitter-wrapper">
    <div id="twitter-container">
        <? include 'twitter.php'; ?>
        <div class="clear"></div>
    </div> 
    <div class="clear"></div>
</div>
<div class="clear"></div>
<? printTwitterAttrib(); ?>

