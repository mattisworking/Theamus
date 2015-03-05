if (typeof Admin === "undefined") Admin = {};
if (typeof Admin.Settings === "undefined") {
    Admin.Settings = {
        Logs: { Pages: {} } 
    };
}

Admin.Settings.Logs.PageVariables = {
    pageNumber: 1,
    resultCount: 15,
    logType: "all",
    orderBy: "time",
    orderWay: "DESC"
};

Admin.Settings.Logs.f = {
    loadInitialLogListing: function() {
        $("#settings_logs-listing").html(Theamus.Notify("spinner", "Loading logs..."));
        
        Theamus.Ajax.run({
            url:    Theamus.base_url + "/settings/logs/listing/",
            type:   "include",
            result: "settings_logs-listing",
            after: function() {
                Admin.Settings.Logs.f.expandLog();
                Admin.Settings.Logs.f.logPageListeners();
            }
        });
        
        return 0;
    }
    
    , loadLogPage: function() {
        var listingVariables = [
            Admin.Settings.Logs.PageVariables.resultCount,
            Admin.Settings.Logs.PageVariables.pageNumber,
            Admin.Settings.Logs.PageVariables.logType,
            Admin.Settings.Logs.PageVariables.orderBy,
            Admin.Settings.Logs.PageVariables.orderWay
        ];
        
        $("#settings_logs-listing").html(Theamus.Notify("spinner", "Loading logs..."));
        
        Theamus.Ajax.run({
            url:    Theamus.base_url + "/settings/logs/listing/" + listingVariables.join("/"),
            type:   "include",
            result: "settings_logs-listing",
            after: function() {
                Admin.Settings.Logs.f.expandLog();
                Admin.Settings.Logs.f.logPageListeners();
            }
        });
        
        return 0;
    }
    
    , expandLog: function() {
        var logRows = $(".settings_logs-row");
        logRows.unbind();
        
        logRows.on("click", function(e) {
            e.preventDefault();
            $(this).toggleClass("expanded");
        });
        
        return 0;
    }
    
    , logPageListeners: function() {
        var links = $(".settings_logs-pages-wrapper").children("a");
        links.unbind();
        
        links.on("click", function(e) {
            e.preventDefault();
            
            var nextPage = $(this).html();
            if (nextPage === "&gt;") nextPage = Admin.Settings.Logs.PageVariables.pageNumber + 1;
            else if (nextPage === "&lt;") nextPage = Admin.Settings.Logs.PageVariables.pageNumber - 1;
            
            Admin.Settings.Logs.PageVariables.pageNumber = parseInt(nextPage);
            Admin.Settings.Logs.f.loadLogPage();
        });
        
        return 0;
    }
};


/** Page initializers **/
Admin.Settings.Logs.Pages = {
    view: function() {
        Admin.Settings.Logs.f.loadInitialLogListing();
        return 0;
    }
};