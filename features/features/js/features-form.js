function load_groups_select() {
    console.log($("#groups").val());
    theamus.ajax.run({
        url:    "features/selects/groups/&groups="+$("#groups").val(),
        result: "group-select",
        type:   "include"
    });
}

function save_feature() {
    $("#edit-result").html(alert_notify('Spinner', 'Working...'));
    theamus.ajax.run({
        url:    "features/save/",
        result: "edit-result",
        form:   "edit-form",
        after:  function() {
            $("[name='file']").attr("disabled", true);
        }
    });
}

$(document).ready(function() {
    $("#edit-form").submit(function(e) {
        e.preventDefault();
        save_feature();
    });

    load_groups_select();
});