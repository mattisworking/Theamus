<?php

class Logs extends Settings {
    private $result_limit;
    private $current_page;
    private $query_limit;
    private $log_type;
    private $order_by;
    private $total_result_count;
    
    
    /**
     * Sets the number of results that will be listed
     * 
     * @param int $limit
     */
    public function set_result_limit($limit = 10) {
        if (!is_numeric($limit) || $limit < 1) $limit = 10;
        $this->result_limit = $limit;
    }
    
    
    /**
     * Returns the number of results to be listed
     * 
     * @return int
     */
    public function get_result_limit() {
        if ($this->result_limit == NULL) $this->set_result_limit();
        return $this->result_limit;
    }
    
    
    /**
     * Sets the current page number
     * 
     * @param int $page_number
     */
    public function set_current_page($page_number = 1) {
        if (!is_numeric($page_number) || $page_number < 1) $page_number = 1;
        $this->current_page = $page_number;
    }
    
    
    /**
     * Returns the current page number
     * 
     * @return type
     */
    public function get_current_page() {
        if ($this->current_page == NULL) $this->set_current_page();
        return $this->current_page;
    }
    
    
    /**
     * Defines the query limit variables
     * 
     * LIMIT <start>, <count>
     */
    private function set_query_limit() {
        $start = ($this->get_result_limit() * $this->get_current_page()) - $this->get_result_limit();
        $this->query_limit = array("start" => $start, "count" => $this->get_result_limit());
    }
    
    
    /**
     * Defines the type of log to be returned
     * 
     * $type can be query|system|developer|general
     * 
     * @param string $type
     */
    public function set_log_type($type = "") {
        if (!is_string($type) || $type == "all") $type = "";
        $this->log_type = $type;
    }
    
    
    /**
     * Returns the type of log to be queried for
     * 
     * @return string
     */
    public function get_log_type() {
        if ($this->log_type == NULL) $this->set_log_type();
        return $this->log_type;
    }
    
    
    /**
     * Returns the query limit variables
     * 
     * @return array
     */
    public function get_query_limit() {
        if ($this->query_limit == NULL) $this->set_query_limit();
        return $this->query_limit;
    }
    
    
    /**
     * Defines the order by column and the way (asc, desc)
     * 
     * @param string $column
     * @param string $way
     */
    public function set_order_by($column = "", $way = "") {
        if (!is_string($column) || $column == "") $column = "time";
        if (!is_string($way) || $way == "") $way = "DESC";
        $this->order_by = array("column" => $column, "way" => $way);
    }
    
    
    /**
     * Returns the order by information
     * 
     * @return array
     */
    public function get_order_by() {
        if ($this->order_by == NULL) $this->set_order_by();
        return $this->order_by;
    }
    
    
    public function get_total_result_count() {
        if ($this->total_result_count == NULL) $this->total_result_count = 0;
        return $this->total_result_count;
    }
    
    
    /**
     * Gets log records from the database
     * 
     * @return type
     * @throws Exception
     */
    public function get_logs() {
        $query_limit = $this->get_query_limit();
        $order_by = $this->get_order_by();
        $log_type = $this->get_log_type();
        
        $conditions = array();
        
        if ($log_type != "") $conditions["type"] = $log_type;
        
        $count_query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table("logs"),
                array(),
                (!empty($conditions) ? array("operator" => "&&", "conditions" => $conditions) : array()));
        
        if (!$count_query) {
            throw new Exception();
        }
        
        $this->total_result_count = $this->Theamus->DB->count_rows($count_query);
        
        if ($this->total_result_count > 200) $query_limit['count'] = 200;
        
        $query = $this->Theamus->DB->select_from_table(
                $this->Theamus->DB->system_table("logs"),
                array(),
                (!empty($conditions) ? array("operator" => "&&", "conditions" => $conditions) : array()),
                "ORDER BY `{$order_by['column']}` {$order_by['way']} LIMIT {$query_limit['start']}, {$query_limit['count']}");
        
        if (!$query) {
            throw new Exception();
        }
        
        $results = $this->Theamus->DB->fetch_rows($query);
        return isset($results[0]) ? $results : array($results);
    }
    
    
    /**
     * Returns an array of valid page links
     * 
     *  1 [2 3 4 5]
     * [1] 2 [3 4 5]
     * [1 2] 3 [4 5]
     * [2 3] 4 [5 6]
     * 
     * @return array
     */
    public function get_page_links() {
        $pages = ceil($this->get_total_result_count() / $this->get_result_limit());
        
        $page_links = array();
        if ($this->get_current_page() > 1) $page_links[] = "<a href='#' class='settings_logs-previous-page'>&lt;</a>";
        for ($i = 0; $i < $pages; $i++) {
            $bold = ($i + 1) == $this->get_current_page() ? true : false;
            $page_links[] = "<a href='#' ".($bold ? "class='settings_logs-current-page'" : "").">".($i + 1)."</a>";
        }
        if ($this->get_current_page() < $pages) $page_links[] = "<a href='#' class='settings_logs-next-page'>&gt;</a>";
        
        return $page_links;
    }
}