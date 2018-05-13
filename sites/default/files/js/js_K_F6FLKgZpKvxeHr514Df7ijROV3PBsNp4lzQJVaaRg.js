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
        }
    };
})(jQuery, window, Drupal, drupalSettings);
;
