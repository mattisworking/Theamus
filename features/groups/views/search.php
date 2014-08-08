<div class='admin-tabs'><?php echo $Groups->groups_tabs(FILE); ?></div>

<form class='form' id='search-groups-form' style='margin-top: 15px; width: 600px;'>
    <div class='input-group'>
        <input type='text' class='form-control' id='search' name='search' autocomplete='off' placeholder='"Administrators"'>
        
        <span class='input-group-btn'>
            <button type='submit' class='btn btn-default'>Search</button>
        </span>
    </div>
</form>

<div id='groups-list'></div>

<script>
    admin_window_run_on_load('change_groups_tab');
    admin_window_run_on_load('search_groups');
</script>