<?php

// Define a failure variable to determine whether or not to disable the next step button
$failed = false;

?>

<div class="content col-6">
    <div class="dependency-row">
        <p class="col-9">PHP version 5.2 and newer</p>
        <span class="col-3">
            <?php

            // Check the PHP version, make sure it is newer than 5.2.4
            if (version_compare(phpversion(), "5.2.4", ">=")) {
                echo "<span class='glyphicon ion-checkmark-round'></span>";
            } else {
                echo "<span class='glyphicon ion-close-round'></span>";
                $failed = true;
            }

            ?>
        </span>
    </div><!-- /version check -->


    <div class="dependency-row">
        <p class="col-9">Apache module <strong>mod_rewrite</strong></p>
        <span class="col-3">
            <span class="glyphicon ion-checkmark-round"></span>
        </span>
    </div><!-- /mod rewrite -->


    <div class="dependency-row">
        <p class="col-9">PHP extension cURL</p>
        <span class="col-3">
            <?php

            // Check for the existance of the cURL extension in PHP
            if (function_exists("curl_init")) {
                echo "<span class='glyphicon ion-checkmark-round'></span>";
            } else {
                echo "<span class='glyphicon ion-close-round'></span>";
                $failed = true;
            }

            ?>
        </span>
    </div><!-- /cURL check -->


    <div class="dependency-row">
        <p class="col-9">PHP MySQL driver for PDO</p>
        <span class="col-3">
            <?php

            // Check for the PDO MySQL driver
            if (in_array("mysql", PDO::getAvailableDrivers())) {
                echo "<span class='glyphicon ion-checkmark-round'></span>";
            } else {
                echo "<span class='glyphicon ion-close-round'></span>";
                $failed = true;
            }

            ?>
        </span>
    </div><!-- /PDO MySQL driver check -->


    <div class="dependency-row">
        <p class="col-9">Folder and file permissions</p>
        <span class="col-3">
            <?php

            // Check if the root of the system is writable
            if (is_writable($Theamus->file_path(ROOT))) {
                echo "<span class='glyphicon ion-checkmark-round'></span>";
            } else {
                echo "<span class='glyphicon ion-close-round'></span>";
                $failed = true;
            }

            ?>
        </span>
    </div><!-- /folder permission check -->
</div>

<div class="next-step-wrapper col-6">
    <button type="button" id="check-again" class="btn btn-default">Check Again</button>
    <button type="button" id="next-step" <?php if ($failed == true) echo "disabled"; ?> class="btn btn-primary">Next Step: Configure Installation <span class="glyphicon ion-arrow-right-c"></span></button>
</div><!-- /buttons -->

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function(){
        $("#check-again").click(function() {
            window.location = Theamus.base_url+"/install/dependencies/";
        });

        $("#next-step").click(function() {
            window.location = Theamus.base_url+"/install/setup/";
        });
    });
</script>