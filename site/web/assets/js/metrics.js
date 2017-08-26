$(function () {
  $('[data-toggle="tooltip"]').tooltip();
  var graphRendered;
  $('#project-metrics-tab').on('shown.bs.tab', function (e) {
    if (!graphRendered) {
      $('.commit-graph').each(function (i, c) {
        var data = c.innerText.trim();
        if (!data) {
          return;
        }
        data = JSON.parse(data);

        c.innerText = '';
        new Chartist.Bar(c, data, {
          fullWidth: true,
          axisX: {
            showGrid: false
          },
          classNames: {
            bar: 'librecores-ct-bar'
          }
        });
      });
      $('.language-graph').each(function (index, chart) {
        var data = chart.innerText.trim();
        if (!data) {
          return;
        }

        data = JSON.parse(data);

        chart.innerText = '';
        new Chartist.Pie(chart, data, {
          classNames: {
            label: 'librecores-lang-chart-label'
          }
        });
      });
      $('.contributors-graph').each(function (index, chart) {
        var data = chart.innerText.trim();
        if (!data) {
          return;
        }
        data = JSON.parse(data);
        console.log(data);
        chart.innerText = '';
        new Chartist.Bar(chart, data, {
          fullWidth: true,
          axisX: {
            showGrid: false
          },
          classNames: {
            bar: 'librecores-ct-bar'
          }
        });
      });
    }
  });

  $('.activity-graph').each(function (i, c) {
    var data = c.innerText.trim();
    if (!data) {
      return;
    }
    c.innerText = '';
    new Chartist.Line(c, {
      series: [
        data.split(',')
      ]
    }, {
      fullWidth: true,
      showPoint: false,
      axisX: {
        offset: 0,
        showLabel: false,
        showGrid: false
      },
      axisY: {
        offset: 0,
        showLabel: false,
        showGrid: false
      },
      classNames: {
        line: 'librecores-ct-line'
      }
    });
  });
});
