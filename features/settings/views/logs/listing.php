<?php

/**
 * localhost/settings/logs/listing/<result limit>/<current page>/<log type>/<order by>/<order way>/
 * localhost/settings/logs/listing/20/1/developer/time/asc
 */

$result_limit   = isset($Theamus->Call->parameters[0]) ? $Theamus->Call->parameters[0]  : 15;
$current_page   = isset($Theamus->Call->parameters[1]) ? $Theamus->Call->parameters[1]  : 1;
$log_type       = isset($Theamus->Call->parameters[2]) ? $Theamus->Call->parameters[2]  : "";
$order_by       = isset($Theamus->Call->parameters[3]) ? $Theamus->Call->parameters[3]  : "";
$order_way      = isset($Theamus->Call->parameters[4]) ? $Theamus->Call->parameters[4]  : "DESC";

$Logs->set_result_limit($result_limit);
$Logs->set_current_page($current_page);
$Logs->set_log_type($log_type);
$Logs->set_order_by($order_by, $order_way);

$results = $Logs->get_logs();

if (empty($results) || empty($results[0])):
    $Theamus->notify("info", "There are no logs to show!");
else:
?>

<div class="settings_logs-wrapper">
    <?php foreach ($results as $log): ?>

    <div class="settings_logs-row">
        <span class="settings_logs-row-type"><?php echo $log['type']; ?></span>
        <span class="settings_logs-row-date"><?php echo date("m-d-Y \a\\t g:i:sA", strtotime($log['time'])); ?></span>
        <span class="settings_logs-row-message"><?php echo $log['message']; ?></span>
        <div class="settings_logs-row-details-wrapper">
            <span class="settings_logs-row-details-classfunc">
                <?php echo "{$log['class']}->{$log['function']}()"; ?>
            </span> on line
            <span class="settings_logs-row-details-line">
                <?php echo $log['line']; ?>
            </span> in file
            <span class="settings_logs-row-details-file">
                <?php echo $log['file']; ?>
            </span>
        </div>
    </div>
    
    <?php endforeach; ?>
</div>

<div class="settings_logs-pages-wrapper">
    <?php echo implode($Logs->get_page_links()); ?>
</div>

<?php
endif;