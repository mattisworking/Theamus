<?php

$get = filter_input_array(INPUT_GET);

$page = 1;
if (isset($get['page'])) {
    $page = $get['page'];
}

$template = <<<TEMPLATE
    <div class='media_list-img'>
        <div class='media_list-options'>
            <a href='#' onclick='return remove_media('%id%');' class='media_list-remove'
                title='Remove'><span class='glyphicon ion-close'></span></a>
        </div>
        ::'%type%' == 'image' ? '<img src='media/images/%path%' alt=''>' : ''::
        ::'%type%' == 'object' ? '<iframe type='application/pdf' src='media/images/%path%'></iframe>' : ''::
    </div>
TEMPLATE;

$query = $tData->select_from_table($tData->prefix.'media', array('id', 'path', 'type'));
if ($query != false) {
    if ($tData->count_rows($query) > 0) {
        $results = $tData->fetch_rows($query);
        $media = isset($results[0]) ? $results : array($results);

        $tPages->set_page_data(array(
            'data'              => $media,
            'per_page'          => 10,
            'current'           => $page,
            'list_template'     => $template
        ));

        $tPages->print_list();
        echo '<div class="clearfix"></div>';
        $tPages->print_pagination('next_page', 'admin-pagination');
    } else {
        alert_notify('info', 'There is no media to display.', 'style="margin-top: 20px;"');
    }
} else {
    alert_notify('danger', 'There was an error querying the database for media.');
}