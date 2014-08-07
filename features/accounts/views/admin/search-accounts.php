<div class='admin-tabs'><?php echo $Accounts->accounts_tabs(FILE); ?></div>

<form class='form-horizontal search-form'>
    <div class='form-group'>
        <div class='input-group'>
            <input type='text' class='form-control' name='search_query' placeholder='John Smith'>
            <span class='input-group-btn'>
                <button type='submit' class='btn btn-default'><span class='glyphicon ion-search'></span></button>
            </span>
        </div>
    </div>
</form>

<div id='account-search-results' class='accounts-list'></div>

<script>
    admin_window_run_on_load('change_accounts_tab');
    admin_window_run_on_load('search_accounts');
</script>