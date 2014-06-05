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

<div id='account-search-results' class='accounts-list'></div>

<script>
    $(document).ready(function() {
        admin_window_run_on_load('change_accounts_tab');
        
        $('.search-form').submit(function(e) {
            e.preventDefault();
            
            console.log('hi');
            
            theamus.ajax.api({
                type:       'get',
                url:        theamus.base_url+'accounts/admin/search-for-accounts/',
                method:     ['AccountsApi', 'search_accounts'],
                data:       { form: this },
                success:    function(data) {
                    if (typeof(data) === 'object') {
                        $('#account-search-results').html(data.response.data);
                    } else {
                        $('#account-search-results').html(alert_notify('danger', 'Something went wrong when searching for users.'));
                    }
                }
            });
        });
    });
</script>