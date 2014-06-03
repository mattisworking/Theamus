<ul class='admin-tabs'>
    <li class='current'><a href='#'>View Dashboard Apps</a></li>
    <li><a href='#' id='manage-apps-tab'>Manage Dashboard Apps</a></li>
</ul>

<div id='home-result'></div>

<div id='apps' class='dashboard-apps'>
    <?=$this->include_file("admin/apps")?>
</div>

<script>
    $('#manage-apps-tab').click(function(e) {
        e.preventDefault();
        update_admin_window_content('theamus-dashboard', 'default/admin/choice-window/');
    });
</script>