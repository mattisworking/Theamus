<?php

try {
    $Appearance->install_theme();
    alert_notify("success", "This theme was installed successfully!");
} catch (Exception $ex) {
    $Appearance->print_exception($ex);
}