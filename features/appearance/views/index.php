<!-- Appearance Tabs -->
<div class='admin-tabs'><?php echo $Appearance->appearance_tabs(FILE); ?></div>

<!-- Search Form -->
<form id='form' style='margin-top: 15px;'>
    <div class='form-group'>
        <input type='text' class='form-control' id='search' name='search' autocomplete='off' placeholder='Start typing to search' onkeyup='return search_themes();'>
    </div>
</form>

<!-- Themes List -->
<div id='themes_list'></div>

<script>
    admin_window_run_on_load('change_themes_tab');
</script>