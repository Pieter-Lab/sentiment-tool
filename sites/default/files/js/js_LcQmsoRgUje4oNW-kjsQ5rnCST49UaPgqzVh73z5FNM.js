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
                var data = google.visualization.arrayToDataTable([
                    ['Tones', 'Headlines per Tone']
                ]);
                //inject count into array
                $.each(drupalSettings.tonescollect, function( index, value,data ) {
                    data.push([index,value.headline_count]);
                });
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
