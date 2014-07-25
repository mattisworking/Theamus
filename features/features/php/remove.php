<?php

try {
    $Features->remove_feature();
} catch (Exception $ex) {
    die(alert_notify("danger", $ex->getMessage()));
}