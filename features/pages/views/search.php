<div class='admin-tabs'><?php echo $Pages->pages_tabs(FILE); ?></div>

<form class='form' id='pages-search-form' style='margin-top: 15px; width: 600px;'>
    <div class='form-group'>
        <div class='input-group'>
            <input type='text' class='form-control' id='pages-search-query' placeholder='Search by page Title'>
            <span class='input-group-btn'>
                <button type='submit' class='btn btn-default'><span class='glyphicon ion-search'></span></button>
            </span>
        </div>
    </div>
</form>

<div id='pages-list'></div>

<script>
    admin_window_run_on_load('change_pages_tab');
    admin_window_run_on_load('search');
</script>