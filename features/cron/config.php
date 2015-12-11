<?php

$Theamus->CLI->register_command("run_cron_jobs", array("php/cron.class.php", "Cron", "run_cron_jobs"));
$Theamus->CLI->register_command("add_job", array("php/cron.class.php", "Cron", "cli_add_job"));
$Theamus->CLI->register_command("get_jobs", array("php/cron.class.php", "Cron", "cli_get_jobs"));
$Theamus->CLI->register_command("delete_job", array("php/cron.class.php", "Cron", "cli_delete_job"));