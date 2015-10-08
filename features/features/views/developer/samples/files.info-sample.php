<?php

// File specification
switch ($Theamus->Call->get_called_file()) {
    case "index.php":
        $feature['title']  = "Index of <feature_name>";
        $feature['header'] = "<feature_name>";
        break;
}