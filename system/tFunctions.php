<?php

/**
 * Prints out a notification on the website
 *
 * @param string $for
 * @param string $type
 * @param string $message
 * @param string $extras
 * @return boolean
 */
function notify($for, $type, $message, $extras = "", $return = false) {
    $ret = "<div class='" . $for . "-notify" . $type . "' id='notify' " . $extras . ">";
    $ret .= $message;
    $ret .= "</div>";
    if ($return == false) {
        echo $ret;
    } else {
        return $ret;
    }
}

/**
 * Prints out an alert on the website
 *
 * @param string $for
 * @param string $type
 * @param string $message
 * @param string $extras
 * @return boolean
 */
function alert_notify($type = "success", $message = "", $extras = "", $return = false) {
    $glyph = array(
        "success" => "ion-checkmark-round",
        "danger" => "ion-close",
        "warning" => "ion-alert",
        "info" => "ion-information",
        "spinner" => "spinner spinner-fixed-size"
    );
    $ret = "<div class='alert alert-$type' id='notify' $extras>";
    $ret .= "<span class='glyphicon ".$glyph[$type]."'></span>$message";
    $ret .= "</div>";

    if ($return == false) {
        echo $ret;
    } else {
        return $ret;
    }
}


/**
 * Prints out an input that requests the site to include an extra javascript file
 *
 * @param string $path
 * @return boolean
 */
function add_js($path) {
    echo "<input type='hidden' name='addscript' value='".$path."?x=".time()."' />";
    return true;
}


/**
 * Runs a javascript function after an ajax call
 *
 * @param string $function
 * @param string $arguments
 * @return boolean
 */
function run_after_ajax($function, $arguments="") {
    echo "<input type='hidden' name='run_after' function='" . $function . "' arguments='" . $arguments . "' />";
    return true;
}


/**
 * Shows the holder for a countdown timer
 *
 * @return string
 */
function js_countdown() {
    return "<span id='countdown'></span><span id='elipses'></span>";
}


/**
 * This function will configure paths to be acceptable on both
 *  Windows and *nix based machines.
 *
 * @param string $path
 * @return string
 */
function path($path) {
    if (strpos($path, ":\\") !== false) $path = str_replace("/", "\\", $path);
    return $path;
}


/**
 * This function will configure paths to be readable to web browsers
 *
 * @param string $path
 * @return string
 */
function web_path($path) {
    if (strpos($path, "\\") !== false) {
        $path = str_replace("\\", "/", $path);
    }
    return $path;
}


/**
 * Takes the user back a page
 *
 * @return header
 */
function back_up() {
    $protocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
    $url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

    if ($url != base_url) {
        if (substr($url, -1) != "/") {
            header("Location: $url/");
        } else {
            header("Location: ../");
        }
    }
}


/**
 * Shortcut to email people through the provided database information (and SMTP)
 *
 * @param string $to
 * @param string $subject
 * @param string $message
 * @return boolean
 */
function tMail($to, $subject, $message) {
    $tData      = new tData();
    $tData->db  = $tData->connect(true);

    $query      = $tData->select_from_table(DB_PREFIX."settings", array("email_protocol", "email_host", "email_port", "email_user", "email_password", "email_user", "name"));
    $settings   = $tData->fetch_rows($query);

    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->SMTPAuth   = true;
    $mail->SMTPSecure = $settings['email_protocol'];
    $mail->Host       = $settings['email_host'];
    $mail->Port       = $settings['email_port'];
    $mail->Username   = $settings['email_user'];
    $mail->Password   = $settings['email_password'];
    $mail->From       = $settings['email_user'];
    $mail->FromName   = $settings['name'];

    $mail->IsHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $message;

    $mail->AddAddress($to);

    return $mail->Send();
}


/**
 * Shows a holder for an upload progress bar and percentage holder
 *
 * @param string $pro
 * @param string $per
 * @return string
 */
function show_upload_progress($pro = "upload-progress", $per = "upload-percentage") {
    $prog = "<div id='$pro'>";
    $prog .= $per ? "<span id='$per'></span>" : "";
    $prog .= "</div>";

    return $prog;
}


/**
 * Shows all of the relevant page navigation defined by the links in the database
 *
 * @return boolean
 */
function show_page_navigation($loc = "main", $child_of = 0) {
    $ret        = array();
    $tData      = new tData();
    $tData->db  = $tData->connect();
    $tUser      = new tUser();

    $query_data = array(
        "table"     => DB_PREFIX."links",
        "columns"   => array("groups", "path", "text", "id"),
        "clause"    => array(
            "operator"  => "AND",
            "conditions"=> array("location" => $loc, "child_of" => $child_of)
        )
    );

    $query = $tData->select_from_table($query_data['table'], $query_data['columns'], $query_data['clause']);

    if ($tData->count_rows($query) > 0) {
        $results = $tData->fetch_rows($query);
        if (!isset($results[0])) {
            $results = array($results);
        }

        foreach ($results as $link) {
            $in = array();
            foreach (explode(",", $link['groups']) as $group) {
                $in[] = $tUser->in_group($group) ? "true" : "false";
            }

            if (in_array("true", $in)) {
                $c = $tData->select_from_table($query_data['table'], array(), array("operator" => "", "conditions" => array("child_of" => $link['id'])));
                $ret[] = "<li>";
                $ret[] = "<a href='".$link['path']."'>".$link['text']."</a>";
                if ($tData->count_rows($c) > 0) $ret[] = "<ul>";
                $ret[] = show_page_navigation($loc, $link['id']);
                if ($tData->count_rows($c) > 0) $ret[] = "</ul>";
                $ret[] = "</li>";
            }
        }
    }

    return implode($ret);
}


/**
 * Shows navigation that is made for the html-nav layout.  As defined by
 *  static pages or features
 *
 * @param string $navigation
 * @return string $nav|boolean
 */
function extra_page_navigation($navigation, $classes = "") {
    if (!empty($navigation)) {
        $class = ($classes != "") ? "class='$classes'" : "";
        $nav = "<ul $class>";
        foreach ($navigation as $text => $path) {
            if ($text != "path") {
                if ($text == "hr") $nav .= "<li class='nav-hr'><hr /></li>";
                elseif (is_array($navigation[$text])) {
                    $nav .= "<li><a href='".$navigation[$text]['path']."'>".$text."</a>";
                    $nav .= extra_page_navigation($navigation[$text]);
                    $nav .= "</li>";
                } else $nav .= "<li><a href='".$path."'>".$text."</a></li>";
            }
        }
        $nav .= "</ul>";

        return $nav;
    }
    return false;
}

/**
 * Prints out an array wrapped in <pre> tags.  Super helpful
 *
 * @param array $array
 */
function Pre($array, $return = false) {
    $ret[] = "<pre>";
    $ret[] = print_r($array, true);
    $ret[] = "</pre>";

    if ($return == true) return implode("", $ret);
    else echo implode("", $ret);
}


/**
 * Sends a user to the login form with the current address attached for routing
 */
function send_to_login() {
    $protocol = isset($_SERVER['HTTPS']) ? "https://" : "http://";
    $url = urlencode($protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    $login_url = base_url."accounts/login?redirect=$url";
    header("Location: $login_url");
}
