function init(){if(typeof show_count_chart==='undefined'){setTimeout(function(){init()},50)}else{show_count_chart()}}
init();

function get_chart_data(pages) {
    var labels = [], data = [], info = [];

    for (key in pages) {
        labels.push(key);
        data.push(pages[key]);
    }

    info.push(labels);
    info.push(data);

    return info;
}

function show_count_chart() {
    var info, chart_data, page_chart = null, largest, options;

    info = get_chart_data(JSON.parse($('#all-pages').val()));
    chart_data = {
        labels: info[0],
        datasets: [{
                fillColor: 'rgba(180,180,180,0.5)',
                strokeColor: 'rgba(150,150,150,0.8)',
                data: info[1]
            }]
    };

    largest = Math.max.apply(Math, info[1]);
    options = {
        scaleOverride: true,
        scaleSteps: 8,
        scaleStepWidth: Math.ceil(largest / 8),
        scaleStartValue: 0,
        maintainAspectRatio: true
    };

    if (typeof(Chart) !== 'undefined') {
        page_chart = new Chart($('#page_canvas')[0].getContext('2d')).Bar(chart_data, options);
        return page_chart;
    } else {
        setTimeout(function() {
            show_count_chart();
        }, 1000);
    }
}