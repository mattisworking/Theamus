<div class='admin-tabs'><?php echo $Navigation->navigation_tabs(FILE); ?></div>

<form class='form' id='navigation-search-form' style='margin-top: 15px; width: 600px;'>
    <div class='form-group'>
        <div class='input-group'>
            <input type='text' class='form-control' id='navigation-search' placeholder='"Home Page"'>
            <span class='input-group-btn'>
                <button type='submit' class='btn btn-default'><span class='glyphicon ion-search'></span></button>
            </span>
        </div>
    </div>
</form>

<div id='navigation-list'></div>

<script>
    admin_window_run_on_load('change_navigation_tab');
    admin_window_run_on_load('search_links');
</script>