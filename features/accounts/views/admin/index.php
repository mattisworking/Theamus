<div class='admin-tabs'><?php echo $Accounts->accounts_tabs(FILE); ?></div>

<div id='accounts-list' class='accounts-list'></div>

<script>
    $(document).ready(function() {
        admin_window_run_on_load('accounts_next_page');
        admin_window_run_on_load('change_accounts_tab');
        admin_window_run_on_load('add_account_listeners');
    });
</script>