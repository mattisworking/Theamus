if (typeof(Admin) === "undefined") var Admin = {};
if (typeof(Admin.Features) === "undefined") Admin.Features = {};

Admin.Features.Developer = {
    createFeatureListener: function() {
        var form = $("#features_create-form"),
            result = $("#features_create-result"),
            button = form.find("[type='submit']");
    
        form.on("submit", function(e) {
            e.preventDefault();
            
            result.html(Theamus.Notify("spinner", "Creating feature..."));
            button.attr("disabled", "disabled");
            
            Theamus.Ajax.api({
                type: "post",
                url: Theamus.base_url + "/features/",
                method: ["Features", "create_feature"],
                data: { form: form },
                success: function(d) {
                    console.log(d);
                    if (d.error.status === 1) {
                        result.html(Theamus.Notify("danger", d.error.message));
                        button.removeAttr("disabled");
                    } else {
                        result.html(Theamus.Notify("success", "This feature has been created.  Get to work!"));
                        setTimeout(function () { 
                            update_admin_window_content('theamus-features', "/features/index/");
                            change_admin_window_title('theamus-features', "Theamus Features");
                        }, 1500);
                    }
                }
            });
        });
    }
};