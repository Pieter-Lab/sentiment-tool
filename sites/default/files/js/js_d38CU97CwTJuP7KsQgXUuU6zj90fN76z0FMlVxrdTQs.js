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
            google.charts.setOnLoadCallback(totalPieChart)
            //Pie Chat Total Function
            function totalPieChart() {
                //Set the Data holder and top line
                var chartData = [
                    ['Tones', 'Headlines per Tone']
                ];
                //inject count into array
                $.each(drupalSettings.tonescollect, function( index, value,data ) {
                    chartData.push([index,value.headline_count]);
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

        }
    };
})(jQuery, window, Drupal, drupalSettings);
;
