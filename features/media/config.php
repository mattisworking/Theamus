<?php

// Define custom feature folders
$feature['js']['folder']		= 'js';
$feature['css']['folder']		= 'css';
$feature['scripts']['folder']	= 'php';
$feature['class']['folder']   = 'php';

// Define feature information
$feature['folder']		= 'media';
$feature['name']		= 'Theamus Media';
$feature['groups']		= array('adminstrators');
$feature['permissions']	= array('add_media', 'remove_media');
$feature['db_prefix']	= 'tm_';