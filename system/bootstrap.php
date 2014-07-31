<?php

// Require the miscellaneous functions
require ROOT."/system/tFunctions.php";

// Require any external libraries
require path(ROOT."/system/external/phpmailer/class.phpmailer.php");

// Require all of the Theamus classes
require path(ROOT."/system/editor/tEditor.class.php");
require path(ROOT."/system/tData.class.php");
require path(ROOT.'/system/tFiles.class.php');
require path(ROOT."/system/tUser.class.php");
require path(ROOT."/system/tPages.class.php");
require path(ROOT."/system/tTheme.class.php");
require path(ROOT."/system/tCall.class.php");

// Require the page initializer
require path(ROOT."/system/init.php");