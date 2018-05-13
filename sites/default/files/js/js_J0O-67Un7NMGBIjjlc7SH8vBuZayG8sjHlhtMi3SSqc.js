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
            //Draw pie chart
            google.charts.setOnLoadCallback(totalPieChart);
            //Current daily tones
            google.charts.setOnLoadCallback(currentPieChart);
            //Historical Graph
            google.charts.setOnLoadCallback(historicalChart);
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
                console.log(chartData);

                var data = google.visualization.arrayToDataTable([
                    ['Year', 'Sales', 'Expenses'],
                    ['2004',  1000,      400],
                    ['2005',  1170,      460],
                    ['2006',  660,       1120],
                    ['2007',  1030,      540]
                ]);

                var options = {
                    title: 'Company Performance',
                    curveType: 'function',
                    legend: { position: 'bottom' }
                };

                var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

                chart.draw(data, options);
            }

        }
    };
})(jQuery, window, Drupal, drupalSettings);
;
