<!-- Accounts Tabs -->
<div class='admin-tabs'><?php echo $Accounts->accounts_tabs(FILE); ?></div>

<!-- Accounts Search Form -->
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

<!-- Accounts Search Results List -->
<div id='account-search-results' class='accounts-list'></div>

<script>
    $(document).ready(function() {
        admin_window_run_on_load('change_accounts_tab'); // Listen to tab changer

        // Search form submission
        $('.search-form').submit(function(e) {
            e.preventDefault();

            // Make the call to search for accounts
            theamus.ajax.api({
                type:       'get',
                url:        theamus.base_url+'accounts/admin/search-for-accounts/',
                method:     ['Accounts', 'search_accounts'],
                data:       { form: this },
                success:    function(data) {
                    // Show an error if the call returned isn't what it should be
                    if (typeof(data) === 'object') {
                        $('#account-search-results').html(data.response.data);
                        admin_window_run_on_load('add_account_listeners');
                    } else {
                        // Show the success message for a successful result
                        $('#account-search-results').html(alert_notify('danger', 'Something went wrong when searching for users.'));
                    }
                }
            });
        });
    });
</script>