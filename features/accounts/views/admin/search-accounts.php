<!-- Accounts Tabs -->
<div class='admin-tabs'><?php echo $Accounts->accounts_tabs(FILE); ?></div>

<!-- Accounts Search Form -->
<form class='form-horizontal search-form'>
    <div class='form-group'>
        <div class='input-group'>
            <input type='text' class='form-control' name='search-query' placeholder='John Smith'>
            <span class='input-group-btn'>
                <button type='submit' class='btn btn-default'><span class='glyphicon ion-search'></span></button>
            </span>
        </div>
    </div>
</form>

<div id='account-search-results'></div>

<script>
    $(document).ready(function() {
        admin_window_run_on_load('change_accounts_tab');
    });
</script>