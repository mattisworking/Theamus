<!-- Accounts Tabs -->
<div class='admin-tabs'><?php echo $Accounts->accounts_tabs(FILE); ?></div>

<!-- Accounts Listing -->
<div id='accounts-list'></div>

<script>
    $(document).ready(function() {
        admin_window_run_on_load('accounts_next_page');
        admin_window_run_on_load('change_accounts_tab');
    });
</script>