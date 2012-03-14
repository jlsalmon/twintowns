<?php
/**
 * File: gallery.php
 * Author: Justin Lewis Salmon
 * 
 * Description:
 *      Makes use of the Tixik API to pull images of interest based
 *      on latitude/longitude. The images are then output in a nice
 *      little thumbnail gallery. Client-side javascript provides 
 *      image switching and caption overlaying.
 */
require_once 'lib.php';
$config = loadConfig();

$town = $config->xpath('//town[name="' . $_SESSION['town'] . '"]');
$twin = $config->xpath('//town[name="' . $_SESSION['twin'] . '"]');

getImages($town, $position = "left");
getImages($twin, $position = "right");

/**
 * Calls the Tixik API, retrieving an XML file, then prints the 
 * images according to the requested position on the page.
 * 
 * @global SimpleXMLElement $config the configuration tree.
 * @param SimpleXMLElement $town the XML tree of the current 
 *        town being processed.
 * @param string $position the position on the page to print
 *        the images.
 */
function getImages($town, $position) {
    global $config;

    $module = $config->xpath('//module[name="Images"]');
    $base = $module[0]->xpath('url[@source="Tixik"]');
    $feed_params = $base[0]->params;

    $url = (string) ($base[0]->base
            . "lat=" . $town[0]->location->lat
            . "&lng=" . $town[0]->location->long
            . "&limit=" . $feed_params[0]['limit']
            . "&key=" . $module[0]->apikey);
    $xml = simplexml_load_string(proxy_retrieve($url));

    printImages($xml, $position);
}

/**
 * Takes a Tixik XML tree and uses it to create an HTML block 
 * full of images and their captions which will be output to 
 * the calling page.
 * 
 * @param SimpleXMLElement $xml the Tixik XML tree
 * @param type $position he position on the page to print
 *        the images.
 */
function printImages($xml, $position) {
    ?>
    <div id="gallery-<? echo $position; ?>">
        <div class="wrapper">
            <?
            if ($position == 'left') {
                ?>
                <div id="exposure-<? echo $position; ?>"></div>
                <?
            }
            ?>
            <ul id="images-<? echo $position; ?>"> 
                <?
                foreach ($xml->items->item as $item) {
                    ?>
                    <li>
                        <a href="<? echo $item->tn_big; ?>">
                            <img src="<? echo $item->tn; ?>" title="<? echo $item->name; ?>" />
                        </a>
                    </li>
                    <?
                }
                ?>
            </ul>
            <?
            if ($position == 'right') {
                ?>
                <div id="exposure-<? echo $position; ?>"></div>
                <?
            }
            ?>
            <div id="controls-<? echo $position; ?>"></div>
        </div>
        <div class="clear"></div>
    </div>

    <?
}

/**
 * Outputs attribution URL for this module.
 * 
 * @global SimpleXMLElement $config the global configuration tree.
 */
function printImageAttrib() {
    global $config;

    $attrib = $config->xpath("//module[name='Images']/url");
    ?>
    <div class="clear"></div>
    <div class="attribution">
        Images courtesy of 
        <a href="<? echo $attrib[0]->attrib; ?>">
            tixik.com
        </a>
        . Distance/time calculations are wildly inaccurate.
    </div>
    <?
}
?>

<script type="text/javascript">
    $(function(){
        var gallery_left = $('#images-left');
        gallery_left.exposure({target : '#exposure-left',
            controlsTarget : '#controls-left',
            slideshowControlsTarget : '#slideshow-left',
            controls : {
                prevNext : true,
                firstLast : false,
                pageNumbers : true				
            }, 
            showThumbs : true,				
            showCaptions : true,
            showExtraData : false,
            maxWidth: 273,
            onImage : function(image, imageData, thumb) {
                gallery_left.wrapper.find('.exposureLastImage').stop().fadeOut(500, function() {
                    $(this).remove();
                });
                image.hide().stop().fadeIn(1000);
            }
        });
                
        var gallery_right = $('#images-right');
        gallery_right.exposure({target : '#exposure-right',
            controlsTarget : '#controls-right',
            slideshowControlsTarget : '#slideshow-right',
            controls : {
                prevNext : true,
                firstLast : false,
                pageNumbers : true				
            }, 					
            showThumbs : true,
            showCaptions : true,
            showExtraData : false,
            maxWidth: 273,
            onImage : function(image, imageData, thumb) {
                gallery_right.wrapper.find('.exposureLastImage').stop().fadeOut(500, function() {
                    $(this).remove();
                });
                image.hide().stop().fadeIn(1000);
            }
        });
    });
</script>