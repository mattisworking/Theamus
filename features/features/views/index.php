<!-- Features Tabs -->
<div class='admin-tabs'><?php echo $Features->features_tabs(FILE); ?></div>

<!-- Search Form -->
<form id='form' style='margin-top: 15px;'>
    <div class='form-group'>
        <input type='text' class='form-control' id='search' name='search' autocomplete='off' placeholder='Start typing to search' onkeyup='return search_features();'>
    </div>
</form>

<!-- Feature List -->
<div id="feature-list" style="min-width:700px;"></div>

<script>
    admin_window_run_on_load('load_features');
    admin_window_run_on_load('change_features_tab');
</script>