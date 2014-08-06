<div class='admin-tabs'><?php echo $DefaultAdmin->admin_tabs(FILE); ?></div>

<div id='home-result'></div>
<form class='form' id='save-home-form'>
    <h2 class='form-header'>Show Home Apps</h2>
    <div class='form-group'>
    <?php
    // Query the database for all of the home apps
    $query = $Theamus->DB->select_from_table(
        'dflt_home-apps',
        array('active', 'path', 'name'));

    // Check the query for errors
    if ($query != false) {
        // Check the query for results
        if ($Theamus->DB->count_rows($query) > 0) {
            $results = $Theamus->DB->fetch_rows($query); // Fetch the results from the databsae

            // Loop through all of the results
            foreach (isset($results[0]) ? $results : array($results) as $app):
                $checked = $app['active'] == 1 ? 'checked' : '';
            ?>
            <label class='checkbox'>
                <input type='checkbox' <?php echo $checked; ?> name='homeapp' id='<?php echo $app['path']; ?>'>
                <?php echo $app['name']; ?>
            </label>
            <?php
            endforeach;

        // Notify the user that there are no apps
        } else $Theamus->notify('info', 'You have no home apps!');

    // Notify the user that the query failed
    } else $Theamus->notify('danger', 'Failed to load home apps.');
    ?>
    </div>

    <hr class='form-split'>

    <?php if ($query != false && $Theamus->DB->count_rows($query) > 0): ?>
    <div class='form-button-group'>
        <button type='submit' class='btn btn-success'>Save Dashboard Apps</button>
    </div>
    <?php endif; ?>
</form>

<script>
    $(document).ready(function() {
        admin_window_run_on_load('change_admin_tab');
        admin_window_run_on_load('save_home_listen');
    });
</script>