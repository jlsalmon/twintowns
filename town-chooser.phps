<?
/**
 * File: town-chooser.php
 * Author: Justin Lewis Salmon, Chris Smith
 * 
 * Description:
 *      Inserts two dropdown boxes into the page, which contain
 *      a list of towns and the current town's twins respectively.
 * 
 *      When the user switches one of these boxes, a Javascript
 *      function is called, which both tells the server to update
 *      the SESSION variables and refreshes the tab interface.
 *      This has the effect of seamlessly hot-swapping towns/twins
 *      due to the modular, configuration-file driven nature of
 *      the site.
 */
?>

<div>    
    <div id="current-towns" class="container">
                  
        <h1 id="current-town">
            <? echo $_SESSION['town']; ?>
        </h1>
        <!--<h3 id="twinnedwith"> twinned with </h3>-->
        <h1 id="current-twin">
            <? echo $_SESSION['twin']; ?>
        </h1>
    </div>

    <div class="left">
        <label for="town-chooser">Select Town: </label>
        <select id="town-chooser" onchange="switchTown()">
            <?php
            /* Print an option for each main town */
            foreach ($config->town as $town) {
                if ($town->name == $_SESSION['town']) {
                    echo "<option selected=\"selected\" value=\""
                    . $town->name . "\">"
                    . $town->name . "</option>";
                } else if ((string) $town['type'] == 'main') {
                    echo "<option value=\""
                    . $town->name . "\">"
                    . $town->name . "</option>";
                }
            }
            ?>
        </select>
    </div>
    <div class="right" style="padding:0">
        <label for="twin-chooser">Select Twin: </label>
        <select id="twin-chooser" onchange="switchTwin()">
            <?php
            /* Print an option for the twins of the current town */
            $twins = $config->xpath("//town[name='" . $_SESSION['town'] . "']/twins");

            foreach ($twins[0] as $twin) {
                $twin = $config->xpath("//town[name='" . $twin . "']");

                if ($twin[0]->name == $_SESSION['twin']) {
                    echo "<option selected=\"selected\" value=\""
                    . $twin[0]->name . "\">"
                    . $twin[0]->name . "</option>\n";
                } else {
                    echo "<option value=\""
                    . $twin[0]->name . "\">"
                    . $twin[0]->name . "</option>\n";
                }
            }
            ?>
        </select>
    </div>
</div>