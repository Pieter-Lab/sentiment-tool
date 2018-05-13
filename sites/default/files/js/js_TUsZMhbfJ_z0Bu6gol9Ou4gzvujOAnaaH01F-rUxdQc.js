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
                var data = google.visualization.arrayToDataTable([
                    ['Task', 'Hours per Day'],
                    ['Work',     11],
                    ['Eat',      2],
                    ['Commute',  2],
                    ['Watch TV', 2],
                    ['Sleep',    7]
                ]);

                var options = {
                    title: 'My Daily Activities'
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
