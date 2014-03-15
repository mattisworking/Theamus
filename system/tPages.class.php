<?php

/**
 * tPages - Theamus data list/pagination class
 * PHP Version 5.5.3
 * Version 1.0
 * @package Theamus
 * @link http://www.theamus.com/
 * @author Matthew Tempinski (Eyraahh) <matt.tempinski@theamus.com>
 * @copyright 2014 Matthew Tempinski
 */

class tPages {
    /**
     * Holds the Theamus data class object
     *
     * @var object $tDataClass
     */
    private $tDataClass;


    /**
     * Holds the mysqli object
     *
     * @var object $tData
     */
    private $tData;


    /**
     * Holds the data that will be printed out
     *
     * @var array $data
     */
    private $data;


    /**
     * Variable start number
     *
     * e.g. Start at 1, or start at 5
     *
     * @var int $start
     */
    private $start;


    /**
     * Variable end number
     *
     * e.g. Start at 1, end at 5
     *
     * @var int $end
     */
    private $end;


    /**
     * Current page number
     *
     * @var int $current
     */
    private $current;


    /**
     * How much data information desired to show per page
     *
     * @var int $per_page
     */
    private $per_page;


    /**
     * SQL query to be run that will provide the data to show
     *
     * @var string $sql
     */
    private $sql;


    /**
     * Pre-gathered information that will be shown in place of a SQL query
     *
     * @var array $defined_data
     */
    private $defined_data;


    /**
     * Desired way the information will be presented
     *
     * @var string $list_template
     */
    private $list_template;


    /**
     * If there is a header to the information, this will take it's place
     *
     * @var string $template_header
     */
    private $template_header;


    /**
     * How the errors will be displayed, if there ever are any
     *
     * Can either be "site" or "admin"
     *
     * @var string $notify
     */
    private $notify;


    /**
     * Constructs the database, initializing any class-specific variables
     *
     * @return boolean
     */
    public function __construct() {
        $this->initialize_variables();
        return true;
    }


    /**
     * Deconstructs the class, disconnecting from the database
     *
     * @return boolean
     */
    public function __descruct() {
        $this->tDataClass->disconnect();
        return true;
    }


    /**
     * Defines class related variables and defaults
     *
     * @return boolean
     */
    private function initialize_variables() {
        $this->tDataClass = new tData();
        $this->tData = $this->tDataClass->connect();
        $this->data = array();
        return true;
    }


    /**
     * Gathers and defines information provided by the developer
     *
     * @param array $array
     * @return boolean
     */
    public function set_page_data($array) {
        if (is_array($array)) {
            $this->sql              = isset($array['sql']) ? $array['sql'] : false;
            $this->defined_data     = isset($array['data']) ? $array['data'] : array();
            $this->per_page         = $array['per_page'];
            $this->current          = $array['current'];
            $this->list_template    = $array['list_template'];
            $this->template_header  = isset($array['template_header']) ? $array['template_header'] : "";
            $this->notify           = isset($array['notify']) ? $array['notify'] : "admin";
            return true;
        }
        return false;
    }


    /**
     * Checks to see if there is any data to show
     *
     * @return boolean
     */
    private function check_count() {
        if (count($this->data) == 0)
            die(notify($this->notify, "info", "There are no results matching your criteria."));
        return true;
    }


    /**
     * Defines what key the data should start and end at
     *
     * @return boolean
     */
    private function get_limits() {
        $this->start = 0;
        if ($this->current > 1) $this->start = $this->per_page * ($this->current - 1);
        $this->end = $this->per_page;
        return true;
    }


    /**
     * Gets a count of all the data records/rows
     *
     * @return int
     */
    private function get_total_record_count() {
        if ($this->sql != false) {
            $q = $this->tData->query($this->sql);
            if ($q) return $q->num_rows;
        } else return count($this->defined_data);
        return 0;
    }


    /**
     * Queries the database to get the information data required
     *
     * @return array $ret|boolean
     */
    private function query_db() {
        $this->get_limits();
        $ret = array();
        $q = $this->tData->query($this->sql." LIMIT ".$this->start.", ".$this->end);
        if ($q)
            while ($res = $q->fetch_assoc()) $ret[] = $res;
        return isset($ret) && !empty($ret) ? $ret : false;
    }


    /**
     * Gets the data as provided by the developer and returns what needs to be shown
     *
     * @return array
     */
    private function get_data() {
        $this->get_limits();
        return array_slice($this->defined_data, $this->start, $this->end);
    }


    /**
     * Takes and populates all of the keys in the template to their relative
     *  data array row
     *
     * e.g. "%name%" -> "Matt"
     *
     * @param array $data_item
     * @return string
     */
    private function populate_list_template($data_item) {
        $output = preg_replace_callback(
            "/%(.*?)%/s",
            function($match) use ($data_item) {
                return str_replace($match[0], $data_item[$match[1]], $match[0]);
            },
            $this->list_template
        );
        return $this->eval_template($output);
    }


    /**
     * Evaluates any short commands that are in the list template
     *
     * e.g. "::1 > 0 ? echo "Yes" : "No":: -> Yes
     *
     * @param string $template
     * @return string
     */
    private function eval_template($template) {
        $output = preg_replace_callback(
            "/::(.*?)::/s",
            function($match) {
                $tUser = new tUser();
                $replace = "";
                eval("\$replace = ".$match[1].";");
                return str_replace($match[0], $replace, $match[0]);
            },
            $template
        );
        return $output;
    }


    /**
     * Prints the header to the list
     *
     * @return boolean
     */
    private function print_list_header() {
        $this->check_count();
        echo $this->template_header;
        return true;
    }


    /**
     * Prints the data in the template provided
     *
     * @return boolean|die
     */
    public function print_list() {
        $this->data = $this->sql == false && !empty($this->defined_data) ? $this->get_data() : $this->query_db();
        if ($this->query_db() != false || !empty($this->defined_data)) {
            $this->print_list_header();
            $ret = "";
            foreach ($this->data as $data) $ret .= $this->populate_list_template($data);
            echo $ret;
            return true;
        } die(notify("admin", "info", "There are no results matching your criteria."));
    }


    /**
     * Defines where the pagination numbers should stop
     *
     * @return int $end
     */
    private function get_pagination_stop() {
        $end = ceil($this->get_total_record_count() / $this->per_page);
        return $end;
    }


    /**
     * Defines the pagination links
     *
     * @param int $number
     * @param string $function
     * @return string
     */
    private function make_pagination($number, $function="next_page") {
        $current = "";
        if ($number == $this->current) $current = "class='current'";
        $link = "<a href='#' ".$current." onclick=\"return ".$function."('".$number."');\">".$number."</a>";
        return $link;
    }


    /**
     * Prints out the current page link and it's styling
     *
     * @return boolean
     */
    private function print_current() {
        echo "<input type='hidden' id='current_page' value='$this->current' />";
        return true;
    }


    /**
     * Prints out all of the pagination links that will allow a user to change
     *  pages in the list
     *
     * @param string $function
     * @return boolean
     */
    public function print_pagination($function="next_page") {
        $end = $this->get_pagination_stop();
        $links = "<div class='pagination'>";
        $this->print_current();
        for ($i = 1; $i <= $end; $i++) $links .= $this->make_pagination($i, $function);
        $links .= "</div>";
        echo $links;
        return true;
    }


    /**
     * For use in an eval tag for the template.  Gets a value from a table in the
     *  database and returns it
     *
     * @param string $table
     * @param string $column
     * @param string $colval
     * @param string $return_key
     * @return string
     */
    public function get_db_value($table, $column, $colval, $return_key) {
        $q = $this->tData->query("SELECT * FROM `$table` WHERE `$column`='$colval'");
        $assoc = array();
        if ($q) $assoc = $q->fetch_assoc();
        return $assoc[$return_key];
    }
}