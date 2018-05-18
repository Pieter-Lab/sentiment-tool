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
          title: 'Tones Percentages for today, totaling: '+drupalSettings.current_total_headlines+' News Headlines',
          tooltip: { trigger: 'selection' }
        };
        //draw chart on element
        var currentlpiechart = new google.visualization.PieChart(document.getElementById('currentlpiechart'));

        currentlpiechart.setAction({
          id: 'sample',
          text: 'See responsible Head Lines',
          action: function() {
            selection = currentlpiechart.getSelection();
            switch (selection[0].row) {
              case 0: alert('Ender\'s Game-'+ selection[0].row); break;
              case 1: alert('Feynman Lectures on Physics-'+ selection[0].row); break;
              case 2: alert('Numerical Recipes in JavaScript-'+ selection[0].row); break;
              case 3: alert('Truman-'+ selection[0].row); break;
              case 4: alert('Freakonomics-'+ selection[0].row); break;
              case 5: alert('The Mezzanine-'+ selection[0].row); break;
              case 6: alert('The Color of Magic-'+ selection[0].row); break;
              case 7: alert('The Law of Superheroes-'+ selection[0].row); break;
            }
          }
        });
        //finalise
        currentlpiechart.draw(data, options);
      }
      //Historical Graph
      google.charts.setOnLoadCallback(historicalChart);
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
          title: 'Tone Performance over '+drupalSettings.total_headlines+' headliness.',
          curveType: 'function',
          legend: { position: 'right' }
        };
        console.log(drupalSettings.graphheadline);
        if(typeof drupalSettings.graphheadline !== "undefined"){
          options.title =  drupalSettings.graphheadline+' performance over '+drupalSettings.total_headlines+' headlines.';
        }
        //set chart
        var curve_chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
        //Draw
        curve_chart.draw(data, options);
      }
      //Draw pie chart
      google.charts.setOnLoadCallback(totalPieChart);
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
          title: 'Tones Percentages for all '+drupalSettings.total_headlines+' News Headlines over the last 5 days.'
        };
        //draw chart on element
        var chart = new google.visualization.PieChart(document.getElementById('totalpiechart'));
        //finalise
        chart.draw(data, options);
      }
      //Curve chart
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
          title : 'Tracking Anger across '+drupalSettings.total_headlines+' headlines over the last 5 days.',
          vAxis: {title: 'Tone Intensity'},
          hAxis: {title: 'date'},
          seriesType: 'bars',
          series: {1: {type: 'line'}}
        };

        var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }

      $(window).resize(function(){
        console.log("Window Resize start");
        currentPieChart();
        historicalChart();
        totalPieChart();
        drawVisualization();
        //Current daily tones
        google.charts.setOnLoadCallback(currentPieChart);
        google.charts.setOnLoadCallback(historicalChart);
        google.charts.setOnLoadCallback(totalPieChart);
        google.charts.setOnLoadCallback(drawVisualization);
        console.log("Window Resize end");
      });

    }
  };
})(jQuery, window, Drupal, drupalSettings);
