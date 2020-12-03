jQuery(document).ready(function () {
    // ECHARTS
    require.config({
        paths: {
            echarts: '/bundles/app/plugins/echarts/'
        }
    });

    // DEMOS
    require(
            [
                'echarts',
                'echarts/chart/bar',
                'echarts/chart/chord',
                'echarts/chart/eventRiver',
                'echarts/chart/force',
                'echarts/chart/funnel',
                'echarts/chart/gauge',
                'echarts/chart/heatmap',
                'echarts/chart/k',
                'echarts/chart/line',
                'echarts/chart/map',
                'echarts/chart/pie',
                'echarts/chart/radar',
                'echarts/chart/scatter',
                'echarts/chart/tree',
                'echarts/chart/treemap',
                'echarts/chart/venn',
                'echarts/chart/wordCloud'
            ],
            function (ec) {
                //chart1
                $.ajax({
                    type: 'GET',
                    url: Routing.generate('app_metric_chart1'),
                    data: {},
                    dataType: "json",
                    success: function (datas) {
                        var myChart = ec.init(document.getElementById('chart1'));
                        myChart.setOption({
                            tooltip: {
                                trigger: 'axis'
                            },
                            dataZoom: {
                                show: true,
                                realtime: true,
                                start: 0,
                                end: 100
                            },
                            toolbox: {
                                show: true,
                                feature: {
                                    mark: {
                                        show: false
                                    },
                                    dataView: {
                                        show: true,
                                        readOnly: false
                                    },
                                    magicType: {
                                        show: true,
                                        type: ['line', 'bar']
                                    },
                                    restore: {
                                        show: true
                                    },
                                    saveAsImage: {
                                        show: true
                                    }
                                }
                            },
                            calculable: true,
                            xAxis: [
                                {
                                    type: 'category',
                                    data: datas.schools
                                }
                            ],
                            yAxis: [
                                {
                                    type: 'value',
                                    splitArea: {
                                        show: true
                                    }
                                }
                            ],
                            series: [
                                {
                                    name: 'Nombre d\'utilisateur',
                                    type: 'bar',
                                    data: datas.users
                                }
                            ]
                        });
                    }
                });
                //chart2
                $.ajax({
                    type: 'GET',
                    url: Routing.generate('app_metric_chart2'),
                    data: {},
                    dataType: "json",
                    success: function (datas) {
                        var myChart = ec.init(document.getElementById('chart2'));
                        myChart.setOption({
                            tooltip: {
                                trigger: 'axis'
                            },
                            toolbox: {
                                show: true,
                                feature: {
                                    mark: {
                                        show: false
                                    },
                                    dataView: {
                                        show: true,
                                        readOnly: false
                                    },
                                    magicType: {
                                        show: true,
                                        type: ['line', 'bar']
                                    },
                                    restore: {
                                        show: true
                                    },
                                    saveAsImage: {
                                        show: true
                                    }
                                }
                            },
                            calculable: true,
                            xAxis: [
                                {
                                    type: 'category',
                                    data: datas.type
                                }
                            ],
                            yAxis: [
                                {
                                    type: 'value',
                                    splitArea: {
                                        show: true
                                    }
                                }
                            ],
                            series: [
                                {
                                    name: 'Nombre d\'utilisateur',
                                    type: 'bar',
                                    data: datas.visits
                                }
                            ]
                        });
                    }
                });
                //chart3
                $.ajax({
                    type: 'GET',
                    url: Routing.generate('app_metric_chart3'),
                    data: {},
                    dataType: "json",
                    success: function (datas) {
                        var myChart = ec.init(document.getElementById('chart3'));
                        myChart.setOption({
                            tooltip: {
                                trigger: 'axis'
                            },
                            toolbox: {
                                show: true,
                                feature: {
                                    mark: {
                                        show: false
                                    },
                                    dataView: {
                                        show: true,
                                        readOnly: false
                                    },
                                    magicType: {
                                        show: true,
                                        type: ['line', 'bar']
                                    },
                                    restore: {
                                        show: true
                                    },
                                    saveAsImage: {
                                        show: true
                                    }
                                }
                            },
                            dataZoom: {
                                show: true,
                                realtime: true,
                                start: 0,
                                end: 100
                            },
                            calculable: true,
                            xAxis: [
                                {
                                    type: 'category',
                                    data: datas.events
                                }
                            ],
                            yAxis: [
                                {
                                    type: 'value',
                                    splitArea: {
                                        show: true
                                    }
                                }
                            ],
                            series: [
                                {
                                    name: 'Nombre d\'utilisateur',
                                    type: 'bar',
                                    data: datas.users
                                }
                            ]
                        });
                    }
                });
            }
    );
});