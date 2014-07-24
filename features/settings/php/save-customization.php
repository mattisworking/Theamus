<?php

try {
    $Settings->save_customization();
    alert_notify('success', 'Information saved.');
} catch (Exception $ex) {
    die(alert_notify('danger', $ex->getMessage()));
}