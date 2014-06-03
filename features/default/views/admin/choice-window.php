<ul class='admin-tabs'>
    <li><a href='#' name='dashboard-home-link'>View Dashboard Apps</a></li>
    <li class='current'><a href='#'>Manage Dashboard Apps</a></li>
</ul>

<div id="home-result"></div>
<form id="home-form" onsubmit="return save_home();">
<?php
$apps = true;
$query = $tData->select_from_table("dflt_home-apps", array("active", "path", "name"));

if ($query != false) {
    if ($tData->count_rows($query) > 0) {
        $results = $tData->fetch_rows($query);
        foreach ($results as $app):
            $checked = $app['active'] == 1 ? "checked" : "";
        ?>
        <label class='checkbox'>
            <input type='checkbox' <?php echo $checked; ?> name='homeapp' id='<?php echo $app['path']; ?>'>
            <?php echo $app['name']; ?>
        </label>
        <?php
        endforeach;
    } else {
        $apps = false;
        alert_notify("info", "You have no home apps!");
    }
} else {
    $apps = false;
    alert_notify("info", "There was an error querying the database.");
}

    if ($apps === true): ?>
    <div class="form-button-group">
        <button type="submit" class="btn btn-success">Save Dashboard Apps</button>
    </div>
    <?php endif; ?>
</form>

<script>
    $('[name="dashboard-home-link"]').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-dashboard', 'default/adminHome/');
    });
</script>