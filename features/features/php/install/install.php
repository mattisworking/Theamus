<?php

try {
    $Features->install_feature();
    alert_notify("success", "This feature has been installed. - ".js_countdown());
} catch (Exception $ex) {
    $Features->clean_temp_folder();
    $Features->remove_feature_folder();
    die(alert_notify('danger', $ex->getMessage()));
}