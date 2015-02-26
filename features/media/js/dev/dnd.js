var files = new Array;

function remove_file(item) {
    var tempfiles = new Array;
    for (var i = 0; i < files.length; i++) {
        if (i != item) {
            tempfiles.push(files[i]);
        }
    }

    files = tempfiles;
    if (files.length > 0) {
        show_files();
    } else {
        show_dnd();
    }
    return false;
}

function handle_single_file() {
    var tempfile = $("#tempfile_input")[0];
    var tempfiles = new Array;

    for (var i = 0; i < files.length; i++) {
        tempfiles.push(files[i]);
    }

    tempfiles.push(tempfile.files[0]);

    files = tempfiles;
    $(tempfile).replaceWith($(tempfile).clone(true));
    show_files();
}

function show_dnd() {
    $("#dnd_area").show();
    $("#media_add-list").hide();
    $("#media_add-list").html("");
    $("#dnd_alt-add").html("or select files to upload...");
}

function show_files() {
    $("#dnd_alt-add").html("add more files...");

    var html = "";
    for (var i = 0; i < files.length; i++) {
        html += "<div class='media_add-fileitem' id='mfi-"+i+"'>";
        html += "<div id='uprog-"+i+"' class='media_upload-prog'>"+
                "<span id='uper-"+i+"' class='media_upload-perc'></span></div>";
        html += "<div class='media_add-fileinfo'>";
        html += "<a href='javascript:void(0);' class='media_remove-fileitem' onclick=\"return remove_file('"+i+"');\">X</a>";
        html += "<span class='media_add-filename'>"+files[i]['name']+"</span>";
        html += "<span class='media_add-filesize'>"+files[i]['size']+" kb</span>";
        html += "<div class='media_result' id='media_result-"+i+"'></div>";
        html += "</div></div>";
    }

    $("#dnd_area").hide();
    $("#media_add-list").show();
    $("#media_add-list").html(html);
}

function upload_media(media_item) {
    if (media_item < files.length && files.length > 0) {
        $("#theamus-media").animate({
            scrollTop: parseInt($("#mfi-"+media_item)[0].offsetTop) - 58
        }, "slow");

        Theamus.Ajax.api({
            type: 'post',
            url: Theamus.base_url+'/media/',
            method: ['Media', 'upload_media'],
            upload: {
                files: [files[media_item]],
                during: function(data) {
                    $('#uprog-'+media_item).css('width', (data.percent_completed * 6));
                    $('#uper-'+media_item).html(data.percentage);
                }
            },
            success: function(data) {
                if (data.error.status === 1) {
                    $('#media_result-'+media_item).html(data.error.message);
                } else {
                    var successes = ['Success!', 'Completed!', 'Uploaded!', 'It worked!'];
                    $('#media_result-'+media_item).html(successes[Math.floor(Math.random() * successes.length)]);
                    setTimeout(function() { upload_media(media_item + 1); }, 500);
                }
            }
        });
    } else {
        update_admin_window_content('theamus-media', '/media/');
        change_admin_window_title('theamus-media', 'Theamus Media');
    }
}

function dnd_listen() {
    var area = $("#dnd_area");

    area.on("dragenter", function(e) {
        e.stopPropagation();
        e.preventDefault();

        $(this).addClass("dnd_area-hover");
    });

    area.on("dragleave", function(e) {
        e.stopPropagation();
        e.preventDefault();

        $(this).removeClass("dnd_area-hover");
    });

    area.on("dragover", function(e) {
        e.stopPropagation();
        e.preventDefault();
    });

    area.on("drop", function(e) {
        e.preventDefault();
        files = e.originalEvent.dataTransfer.files;
        $(this).removeClass("dnd_area-hover");

        show_files();
    });

    $("#dnd_alt-add").click(function(e) {
        e.preventDefault();

        $("#tempfile_input").click();
    });

    $("#tempfile_input").change(function(e) {
        handle_single_file();
    });

    $("#upload_media").click(function(e) {
        e.preventDefault();
        upload_media(0);
        $(".media_remove-fileitem").hide();
        $(".media_dnd-alt").hide();
        $("#upload_media").prop("disabled", true);
        $("#media_add-close").val("Close");
    });

    $("#media_add-close").click(function(e) {
        e.preventDefault();
        load_media_list();
        close_add_media();
    });
}