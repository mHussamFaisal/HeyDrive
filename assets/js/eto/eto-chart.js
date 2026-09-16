

ETO.Chart = function() {
    var etoFn = {};

    etoFn.config = {
        init: [],
        lang: [],
        charts: {},
        chartColors: {
            red: 'rgb(255, 99, 132)',
            orange: 'rgb(255, 159, 64)',
            yellow: 'rgb(255, 205, 86)',
            green: 'rgb(75, 192, 192)',
            blue: 'rgb(54, 162, 235)',
            purple: 'rgb(153, 102, 255)',
            grey: 'rgb(201, 203, 207)'
        },
        options: {
            line: {
                legend: {
                    position: 'bottom',
                },
                responsive: true,
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function (tooltipItem, chart) {
                            return chart.datasets[tooltipItem.datasetIndex].label + ': ' + ETO.formatPrice(tooltipItem.value);
                        }
                    }
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
            },
            bar: {
                legend: {
                    position: 'bottom',
                },
                responsive: true,
                tooltips: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function (tooltipItem, chart) {
                            return chart.datasets[tooltipItem.datasetIndex].label + ': ' + ETO.formatPrice(tooltipItem.value);
                        }
                    }
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
            },
            pie: {
                legend: {
                    position: 'bottom',
                },
                responsive: true,
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, chart) {
                            var value = chart.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];

                            return chart.labels[tooltipItem.index] + ': ' + ETO.formatPrice(value);
                        },
                    }
                },
            },
        },
    };

    etoFn.init = function(config) {
        ETO.assets('plugins/chartjs/Chart.min.css');
        ETO.assets('plugins/chartjs/Chart.min.js');

        $('body').on('click', '.eto-chart-type', function (e) {
            $(this).closest('.eto-chart').find('.eto-chart-type').removeClass('active');
            $(this).addClass('active');

            var id =  $(this).closest('.eto-chart').find('.eto-chart-container').attr('id'),
                type = $(this).data('etoType');

            etoFn.renderChart(id, [], [], type);
        })
        .on('click', '.eto-range', function (e) {
            $('.eto-range').removeClass('active');
            $(this).addClass('active');

            var id =  $(this).closest('.eto-chart').find('.eto-chart-container').attr('id'),
                type = $(this).data('etoType');

            etoFn.renderChart(id, [], type);
        });
    };

    etoFn.renderChart = function (id, labels, datasets, type, title) {
        var container = $('#' + id).closest('.eto-chart'),
            colorNames = Object.keys(etoFn.config.chartColors),
            ctx = document.getElementById(id).getContext('2d'),
            range = container.find('.eto-range.active');

        container.LoadingOverlay('show');

        $.each(datasets, function (k,v) {
            var colorName = colorNames[k],
                newColor = etoFn.config.chartColors[colorName];

            datasets[k].fill = false;
            datasets[k].backgroundColor = newColor;
            datasets[k].borderColor = newColor;
        });

        type = typeof type == 'undefined' ? 'line' : type;

        if (typeof etoFn.config.charts[id] == 'undefined') {
            etoFn.config.charts[id] = {};
            etoFn.config.charts[id].labels = labels;
            etoFn.config.charts[id].datasets = datasets;
            etoFn.config.charts[id].settings = {
                type: type,
                data: etoFn.extendData(type, {labels: labels, datasets: datasets}, range),
                options:  etoFn.extendConfig(type, title),
            };
        } else if(etoFn.config.charts[id].type != type) {
            var parentContainer = $('#'+id).parent();

            $('#'+id).remove();
            parentContainer.append('<canvas id="' + id + '" class="eto-chart-container">'+ ETO.trans('reports.no_support_canvas') +'</canvas>');
            ctx = document.getElementById(id).getContext('2d');

            etoFn.config.charts[id].chart.destroy();
            etoFn.config.charts[id].settings.type = type;
            etoFn.config.charts[id].settings.options = etoFn.extendConfig(type, title);
            etoFn.config.charts[id].settings.data = etoFn.extendData(type, etoFn.config.charts[id], range);
        }

        etoFn.config.charts[id].type = type;
        etoFn.config.charts[id].chart = new Chart(ctx, etoFn.config.charts[id].settings);
        etoFn.config.charts[id].height = 500;
        container.LoadingOverlay('hide');
    };

    etoFn.destroy = function (id) {
        if (typeof etoFn.config.charts[id] != 'undefined') {
            etoFn.config.charts[id].chart.destroy();
        }
    };

    etoFn.extendConfig = function (type, title) {
        var options =  $.extend(true, {}, etoFn.config.options[type]);
        if (type == 'line' || type == 'bar') {
            if (typeof title != 'undefined') {
                options.title = {
                    display: true,
                    text: title
                };
            }

            options.scales = {
                y: {
                    display: true,
                    scaleLabel: {
                        display: true,
                        labelString: ETO.trans('reports.labels.price')
                    }
                }
            }
        }
        return options;
    };

    etoFn.extendData = function (type, configChart, range) {
        var sumDataPerItem = {},
            data = {},
            datasets = configChart.datasets;

        if (range == 'weekly') {
            var weeks = etoFn.getWeeksFromDates(configChart.labels);
            // console.log(weeks);
        } else if (range == 'monthly') {

        }

        if (type == 'line' || type == 'bar') {
            data = {
                labels: configChart.labels,
                datasets: datasets,
            };
        } else if(type == 'pie') {
            data =  {
                datasets: [{
                    data: [],
                    backgroundColor: [],
                }],
                labels: []
            };

            $.each(datasets, function(k,v){
                data.labels.push(v.label);
                data.datasets[0].backgroundColor.push(v.backgroundColor);
                sumDataPerItem[v.label] = 0;
                $.each(v.data, function(i,p){
                    sumDataPerItem[v.label] += parseFloat(p);
                });

                data.datasets[0].data.push(sumDataPerItem[v.label]);
            });
        }

        return data;
    };

    etoFn.getWeeksFromDates = function(dates) {
        var weeks = {};
        var format = 'YYYY-MM-DD';

        $.each(dates, function (k, date) {
            var momentDate = moment(date),
                week = momentDate.weekYear();

           if (typeof  weeks[week] == 'undefined') {
               weeks[week] = {
                   label: moment().weekYear(week).day(1).format(format) + ' - ' + moment().weekYear(week).day(7).format(format),
                   dates: [],
               };
           }

           weeks[week].dates.push(date);
        });

        return weeks;
    };

    etoFn.weekCount = function(year, month_number) {
        var firstDayOfWeek = parseInt(ETO.config.date_start_of_week) || 0,
            firstOfMonth = new Date(year, month_number-1, 1),
            lastOfMonth = new Date(year, month_number, 0),
            numberOfDaysInMonth = lastOfMonth.getDate(),
            firstWeekDay = (firstOfMonth.getDay() - firstDayOfWeek + 7) % 7,
            used = firstWeekDay + numberOfDaysInMonth;

        return Math.ceil( used / 7);
    };

    etoFn.getWeeksStartAndEndInMonth = function(month, year, _start) {
        var monthNames = ["January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"],
            d = new Date();
        // console.log("The current month is " + monthNames[d.getMonth()]);
        var weeks = [],
            firstDate = new Date(year, month, 1),
            lastDate = new Date(year, month + 1, 0),
            numDays = lastDate.getDate();
        var c = Date()
        var start = 1;
        var end = 7 - firstDate.getDay();
        if (_start == 'monday') {
            if (firstDate.getDay() === 0) {
                end = 1;
            } else {
                end = 7 - firstDate.getDay() + 1;
            }
        }
        while (start <= numDays) {
            var businessWeekEnd = end-2
            if(businessWeekEnd > 0){
                if(businessWeekEnd > start){
                    weeks.push({start: start, end: businessWeekEnd});
                }
                else{
                    //Check for last day else end date is within 5 days of the week.
                    weeks.push({start: start, end: end});
                }
            }
            start = end + 1;
            end = end + 7;
            end = start === 1 && end === 8 ? 1 : end;
            if (end > numDays) {
                end = numDays;
            }
        }

        weeks.forEach(week => {
            var _s = parseInt(week.start, 10)+1,
                _e = parseInt(week.end,10)+1;
            // console.log(new Date(year, month, _s).toJSON().slice(0,10).split('-').reverse().join('/') + " - " + new Date(year, month, _e).toJSON().slice(0,10).split('-').reverse().join('/'));
            // console.log(((_e-_s)+1)*8)
        });
        return weeks;
    };

    return etoFn;
}();
