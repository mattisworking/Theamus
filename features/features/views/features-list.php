<?php

$get = filter_input_array(INPUT_GET);
$search = isset($get['search']) ? $get['search'] : '';
$page = isset($get['page']) ? $get['page'] : 1;

$template = implode('', array(
    '<li>',
    '<ul class=\'feature-options\'>',
    $Theamus->User->has_permission('edit_features') ? '<li><a href=\'#\' name=\'edit-feature-link\' data-id=\'%id%\'><span class=\'glyphicon ion-edit\'></span></a></li>' : '',
    $Theamus->User->has_permission('remove_features') ? '::%permanent% == 0 ? "<li><a href=\'#\' name=\'remove-feature-link\' data-id=\'%id%\'><span class=\'glyphicon ion-close\'></span></a></li>" : ""::' : '',
    '</ul>',
    '<span class=\'feature-name\'>%name%</span>',
    '<span class=\'feature-enabled\'>::"%enabled%" == 1 ? "Enabled" : "Disabled";::</span>',
    '<span class=\'feature-version\'>%version%</span>',
    '</li>'
));

$query = $Theamus->DB->select_from_table(
    $Theamus->DB->system_table('features'),
    array('id', 'alias', 'name', 'permanent', 'enabled'),
    array('operator' => '',
        'conditions' => array('[%]name' => $search.'%')),
    'ORDER BY `name` ASC');

if (!$query) {
    $Theamus->Log->query($Theamus->DB->get_last_error());
    die($Theamus->notify('danger', 'Failed to get features.'));
}

if ($Theamus->DB->count_rows($query) == 0) die($Theamus->notify('info', 'Could not find any features.'));

$results = $Theamus->DB->fetch_rows($query);
$all_features = isset($results[0]) ? $results : array($results);

$features = array();

$original_config = $Theamus->Call->feature['config'];

foreach ($all_features as $feature) {
    $config_file = $Theamus->file_path(ROOT.'/features/'.$feature['alias'].'/config.php');

    if ($feature['alias'] == 'features') $version = $original_config['feature_version'];
    elseif (file_exists($config_file)) {
        include_once $config_file;
        $version = isset($Theamus->Call->feature['config']['feature_version']) ? 'v'.$Theamus->Call->feature['config']['feature_version'] : 'Unknown';
        $Theamus->Call->feature['config'] = array();
    }

    $feature['version'] = $version;
    $features[] = $feature;
}

$Theamus->Call->feature['config'] = $original_config;

$Theamus->Pagination->set_page_data(array(
    'data'          => $features,
    'per_page'      => 15,
    'current'       => $page,
    'list_template' => $template
));

echo '<ul>';
$Theamus->Pagination->print_list();
echo '</ul>';
$Theamus->Pagination->print_pagination('get_features', 'admin-pagination');
