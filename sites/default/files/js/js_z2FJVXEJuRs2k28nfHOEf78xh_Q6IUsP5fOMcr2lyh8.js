/**
 * @file
 */

//---------------------------------------------------------------------------------------------------
//IIFE FUnction
(function ($, window, Drupal, drupalSettings) {
    //Attach behaviours
    Drupal.behaviors.tonner_worker = {
        attach: function (context, drupalSettings) {
            //Pull in google packages
            google.charts.load('current', {'packages':['corechart']});
            //Current daily tones
            google.charts.setOnLoadCallback(currentPieChart);
            //Draw pie chart
            google.charts.setOnLoadCallback(totalPieChart);
            //Historical Graph
            google.charts.setOnLoadCallback(historicalChart);

            google.charts.setOnLoadCallback(drawVisualization);

            //Pie Chat Total Function
            function totalPieChart() {
                //Set the Data holder and top line
                var chartData = [
                    ['Tones', 'Headlines per Tone']
                ];
                //inject count into array
                $.each(drupalSettings.tonescollect, function( index, value,data ) {
                    chartData.push([index,value.total_headline_count]);
                });
                var data = google.visualization.arrayToDataTable(chartData);
                //set the title
                var options = {
                    title: 'Tones Percentages for all '+drupalSettings.total_headlines+' News Headlines'
                };
                //draw chart on element
                var chart = new google.visualization.PieChart(document.getElementById('totalpiechart'));
                //finalise
                chart.draw(data, options);
            }
            //Total Stats for today
            function currentPieChart() {
                //Set the Data holder and top line
                var chartData = [
                    ['Tones', 'Headlines per Tone']
                ];
                //inject count into array
                $.each(drupalSettings.tonescollect, function( index, value,data ) {
                    chartData.push([index,value.current_headline_count]);
                });
                var data = google.visualization.arrayToDataTable(chartData);
                //set the title
                var options = {
                    title: 'Tones Percentages for today, totaling: '+drupalSettings.current_total_headlines+' News Headlines'
                };
                //draw chart on element
                var chart = new google.visualization.PieChart(document.getElementById('currentlpiechart'));
                //finalise
                chart.draw(data, options);
            }
            //Historical Chart
            function historicalChart() {
                //set chart data
                var chartData = [];
                //inject headers
                var headers = ['Date']
                $.each(drupalSettings.tonescollect, function( index, value ) {
                    headers.push(index);
                });
                //push into first line of chart
                chartData.push(headers);
                //Push in data
                $.each(drupalSettings.historialdata, function( index, value ) {
                    var ar = [index];
                    $.each(value, function(i,v){
                        ar.push(v);
                    });
                    chartData.push(ar);
                });
                //push into chart data
                var data = google.visualization.arrayToDataTable(chartData);
                //set titles
                var options = {
                    title: 'Tone Performance over '+drupalSettings.total_headlines+' headlines',
                    curveType: 'function',
                    legend: { position: 'right' }
                };
                //set chart
                var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
                //Draw
                chart.draw(data, options);
            }

            function drawVisualization() {
                //set chart data
                var chartData = [];
                //inject headers
                var headers = ['Date']
                $.each(drupalSettings.tonescollect, function( index, value ) {
                    headers.push(index);
                });
                //push into first line of chart
                chartData.push(headers);
                //Push in data
                $.each(drupalSettings.historialdata, function( index, value ) {
                    var ar = [index];
                    $.each(value, function(i,v){
                        ar.push(v);
                    });
                    chartData.push(ar);
                });
                //push into chart data
                var data = google.visualization.arrayToDataTable(chartData);

                var options = {
                    title : 'Tones Header',
                    vAxis: {title: 'Tone Intensity'},
                    hAxis: {title: 'date'},
                    seriesType: 'bars',
                    series: {7: {type: 'line'}}
                };

                var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
                chart.draw(data, options);
            }

        }
    };
})(jQuery, window, Drupal, drupalSettings);
;
