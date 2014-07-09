<!-- Pages Tabs -->
<div class='admin-tabs'><?php echo $Pages->pages_tabs(FILE); ?></div>

<!-- Search Form -->
<form id='form' style='margin-top: 15px;'>
    <div class='form-group'>
        <input type='text' class='form-control' id='search' name='search' autocomplete='off' placeholder='Start typing to search' onkeyup='return search_pages();'>
    </div>
</form>

<!-- List of Pages -->
<div id='pages_list'></div>

<script>
    admin_window_run_on_load('change_pages_tab');
</script>