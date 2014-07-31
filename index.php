<?php

/**
 * Theamus -- a modular content management system that makes websites easy.
 *
 * PHP Version 5.5.3
 * Version 0.6
 * @package Theamus
 * @link http://www.theamus.com/
 * @author Eyrah Temet (Eyraahh) <info@theamus.com>
 *
 *  This program is free software: you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation, either version 3 of the License, or
 *   (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Define any ini_set variables
ini_set("session.gc_maxlifetime", 7*24*60*60);

session_start(); // Start the session!
define("ROOT", dirname(__FILE__)); // Define the root of the system

$params = isset($_GET['params']) ? $_GET['params'] : ""; // Define the given parameters

require "system/bootstrap.php"; // Require the bootstrap to load the page