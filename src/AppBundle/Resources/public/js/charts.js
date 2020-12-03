
$(document).ready(function () {
    initCharts();

    $("body").on('change', '.granularity', function () {
        var func = $(this).attr('data-func');
        eval(func + "()");
    });

    $("body").on('change', '.categories', function () {
        var func = $(this).attr('data-func');
        eval(func + "()");
    });

    $("body").on('change', '.type', function () {
        if ($(this).parent().parent().find('div.association').length > 0)
            $(this).parent().parent().find('div.association').addClass('hide');
        if ($(this).parent().parent().find('div.merchant').length > 0)
            $(this).parent().parent().find('div.merchant').addClass('hide');

        if ($(this).val() === 'association') {
            if ($(this).parent().parent().find('div.association').length > 0)
                $(this).parent().parent().find('div.association').removeClass('hide');
        }
        if ($(this).val() === 'merchant') {
            if ($(this).parent().parent().find('div.merchant').length > 0)
                $(this).parent().parent().find('div.merchant').removeClass('hide');
        }
        var func = $(this).attr('data-func');
        eval(func + "()");
    });
    $("body").on('change', '.association', function () {
        var func = $(this).attr('data-func');
        eval(func + "()");
    });
    $("body").on('change', '.merchant', function () {
        var func = $(this).attr('data-func');
        eval(func + "()");
    });
    $("body").on('change', '.volunteer', function () {
        var func = $(this).attr('data-func');
        eval(func + "()");
    });
    $("body").on('change', '.time', function () {
        var func = $(this).attr('data-func');
        eval(func + "()");
    });
    $("body").on('change', '.enabled', function () {
        var func = $(this).attr('data-func');
        eval(func + "()");
    });

    $("body").on('change', '.typeComment', function () {
        if ($(this).parent().parent().find('div.event').length > 0)
            $(this).parent().parent().find('div.event').addClass('hide');
        if ($(this).parent().parent().find('div.article').length > 0)
            $(this).parent().parent().find('div.article').addClass('hide');

        if ($(this).val() === 'event') {
            if ($(this).parent().parent().find('div.event').length > 0)
                $(this).parent().parent().find('div.event').removeClass('hide');
        }
        if ($(this).val() === 'article') {
            if ($(this).parent().parent().find('div.article').length > 0)
                $(this).parent().parent().find('div.article').removeClass('hide');
        }

        var func = $(this).attr('data-func');
        eval(func + "()");
    });

    $("body").on('change', '.typeEvent', function () {
        if ($(this).parent().parent().find('div.categories').length > 0)
            $(this).parent().parent().find('div.categories').addClass('hide');
        if ($(this).val() === 'association' || $(this).val() === 'merchant') {
            if ($(this).parent().parent().find('div.categories').length > 0)
                $(this).parent().parent().find('div.categories').removeClass('hide');
        }

        var func = $(this).attr('data-func');
        eval(func + "()");
    });
    $("body").on('change', '.category', function () {
        var func = $(this).attr('data-func');
        eval(func + "()");
    });
    $("body").on('change', '.article', function () {
        var func = $(this).attr('data-func');
        eval(func + "()");
    });
    $("body").on('change', '.event', function () {
        var func = $(this).attr('data-func');
        eval(func + "()");
    });

    $("body").on('submit', "#stats_filter", function (e) {
        initCharts();
        return false;
    });

    function initCharts() {
        generalChart1();
        generalChart2();
        generalChart3();
        generalChart4();
        //generalChart5();
        generalChart6();
        //generalChart7();
        generalChart8();
        generalChart9();
        generalChart10();
        generalChart11();
        generalChart12();
        //generalChart13();
        generalChart14();

        contentChart1();
        contentChart2();
        contentChart3();
        contentChart4();
        contentChart5();
        contentChart6();
        contentChart7();
        contentChart8();
        contentChart9();
        contentChart10();

        userChart1();
        userChart2();
        //userChart3();
        userChart4();
        userChart5();


        cityhallChart1();
        cityhallChart2();
        cityhallChart3();
        cityhallChart4();
        cityhallChart5();
        cityhallChart6();
        cityhallChart7();
        //cityhallChart8();
    }

    function generalChart1() {
        if ($('#general_chart1').length) {
            $('.loader').removeClass('hide');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_general_chart1'),
                data: {
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("general_chart1", {
                        "type": "pie",
                        "theme": "light",
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueField": "quantity",
                        "titleField": "type",
                    });

                    $('#general_chart1').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function generalChart2() {
        if ($('#general_chart2').length) {
            var granularity = $('#general_chart2').parent().parent().find('select.granularity').find("option:selected").val();
            $('.loader').removeClass('hide');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_general_chart2'),
                data: {
                    granularity: granularity,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("general_chart2", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] comptes citoyens</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#general_chart2').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function generalChart3() {
        if ($('#general_chart3').length) {
            var granularity = $('#general_chart3').parent().parent().find('select.granularity').find("option:selected").val();
            $('.loader').removeClass('hide');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_general_chart3'),
                data: {
                    granularity: granularity,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("general_chart3", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] comptes citoyens crées</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantityUser"
                            }, {
                                "balloonText": "<span style='font-size:13px;'>[[value]] sessions actives</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantitySession"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#general_chart3').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function generalChart4() {
        if ($('#general_chart4').length) {
            var granularity = $('#general_chart4').parent().parent().find('select.granularity').find("option:selected").val();
            $('.loader').removeClass('hide');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_general_chart4'),
                data: {
                    granularity: granularity,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("general_chart4", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] minutes par session</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#general_chart4').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function generalChart5() {
        if ($('#general_chart5').length) {
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_general_chart5'),
                data: {},
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("general_chart5", {
                    });

                    $('#general_chart5').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                }
            });
        }
    }
    function generalChart6() {
        if ($('#general_chart6').length) {
            $('.loader').removeClass('hide');
            var granularity = $('#general_chart6').parent().parent().find('select.granularity').find("option:selected").val();
            var type = $('#general_chart6').parent().parent().find('select.type').find("option:selected").val();
            var association = $('#general_chart6').parent().parent().find('select.association').find("option:selected").val();
            var merchant = $('#general_chart6').parent().parent().find('select.merchant').find("option:selected").val();
            var event = $('#general_chart6').parent().parent().find('select.event').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_general_chart6'),
                data: {
                    granularity: granularity,
                    type: type,
                    association: association,
                    merchant: merchant,
                    event: event,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("general_chart6", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] clic sur agenda</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantityClickAgenda"
                            }, {
                                "balloonText": "<span style='font-size:13px;'>[[value]] clic sur détail évenement</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantityClickDetail"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#general_chart6').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function generalChart7() {
        if ($('#general_chart7').length) {
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_general_chart7'),
                data: {},
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("general_chart7", {
                    });

                    $('#general_chart7').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                }
            });
        }
    }
    function generalChart8() {
        if ($('#general_chart8').length) {
            $('.loader').removeClass('hide');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_general_chart8'),
                data: {},
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("general_chart8", {
                        "type": "pie",
                        "theme": "light",
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueField": "quantity",
                        "titleField": "value",
                    });

                    $('#general_chart8').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function generalChart9() {
        if ($('#general_chart9').length) {
            $('.loader').removeClass('hide');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_general_chart9'),
                data: {},
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("general_chart9", {
                        "type": "pie",
                        "theme": "light",
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueField": "quantity",
                        "titleField": "value",
                    });

                    $('#general_chart9').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function generalChart10() {
        if ($('#general_chart10').length) {
            var granularity = $('#general_chart10').parent().parent().find('select.granularity').find("option:selected").val();
            $('.loader').removeClass('hide');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_general_chart10'),
                data: {
                    granularity: granularity,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("general_chart10", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] sessions par utilisateur</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#general_chart10').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function generalChart11() {
        if ($('#general_chart11').length) {
            var granularity = $('#general_chart11').parent().parent().find('select.granularity').find("option:selected").val();
            var categories = [];
            $('#general_chart11').parent().parent().find('.categories').each(function () {
                if ($(this).is(':checked'))
                    categories.push($(this).val());
            });
            $('.loader').removeClass('hide');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_general_chart11'),
                data: {
                    granularity: granularity,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                    categories: categories
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("general_chart11", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] associations</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#general_chart11').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function generalChart12() {
        if ($('#general_chart12').length) {
            var granularity = $('#general_chart12').parent().parent().find('select.granularity').find("option:selected").val();
            var categories = [];
            $('#general_chart12').parent().parent().find('.categories').each(function () {
                if ($(this).is(':checked'))
                    categories.push($(this).val());
            });
            $('.loader').removeClass('hide');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_general_chart12'),
                data: {
                    granularity: granularity,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                    categories: categories
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("general_chart12", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] commerçants</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#general_chart12').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function generalChart13() {
        if ($('#general_chart13').length) {
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_general_chart13'),
                data: {},
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("general_chart13", {
                    });

                    $('#general_chart13').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                }
            });
        }
    }
    function generalChart14() {
        if ($('#general_chart14').length) {
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_general_chart14'),
                data: {
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    $('#eventCreated').html(datas.percent);
                }
            });
        }
    }





    function contentChart1() {
        if ($('#content_chart1').length) {
            $('.loader').removeClass('hide');
            var granularity = $('#content_chart1').parent().parent().find('select.granularity').find("option:selected").val();
            var type = $('#content_chart1').parent().parent().find('select.type').find("option:selected").val();
            var association = $('#content_chart1').parent().parent().find('select.association').find("option:selected").val();
            var merchant = $('#content_chart1').parent().parent().find('select.merchant').find("option:selected").val();
            var volunteer = $('#content_chart1').parent().parent().find('select.volunteer').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_content_chart1'),
                data: {
                    granularity: granularity,
                    type: type,
                    association: association,
                    merchant: merchant,
                    volunteer: volunteer,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("content_chart1", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] évènements</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#content_chart1').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function contentChart2() {
        if ($('#content_chart2').length) {
            $('.loader').removeClass('hide');
            var granularity = $('#content_chart2').parent().parent().find('select.granularity').find("option:selected").val();
            var type = $('#content_chart2').parent().parent().find('select.type').find("option:selected").val();
            var association = $('#content_chart2').parent().parent().find('select.association').find("option:selected").val();
            var merchant = $('#content_chart2').parent().parent().find('select.merchant').find("option:selected").val();
            var volunteer = $('#content_chart2').parent().parent().find('select.volunteer').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_content_chart2'),
                data: {
                    granularity: granularity,
                    type: type,
                    association: association,
                    merchant: merchant,
                    volunteer: volunteer,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("content_chart2", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] évènements</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#content_chart2').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function contentChart3() {
        if ($('#content_chart3').length) {
            $('.loader').removeClass('hide');
            var granularity = $('#content_chart3').parent().parent().find('select.granularity').find("option:selected").val();
            var type = $('#content_chart3').parent().parent().find('select.type').find("option:selected").val();
            var association = $('#content_chart3').parent().parent().find('select.association').find("option:selected").val();
            var merchant = $('#content_chart3').parent().parent().find('select.merchant').find("option:selected").val();
            var event = $('#content_chart3').parent().parent().find('select.event').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_content_chart3'),
                data: {
                    granularity: granularity,
                    type: type,
                    association: association,
                    merchant: merchant,
                    event: event,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("content_chart3", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] clic sur détail événement</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#content_chart3').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function contentChart4() {
        if ($('#content_chart4').length) {
            $('.loader').removeClass('hide');
            var granularity = $('#content_chart4').parent().parent().find('select.granularity').find("option:selected").val();
            var type = $('#content_chart4').parent().parent().find('select.type').find("option:selected").val();
            var association = $('#content_chart4').parent().parent().find('select.association').find("option:selected").val();
            var merchant = $('#content_chart4').parent().parent().find('select.merchant').find("option:selected").val();
            var event = $('#content_chart4').parent().parent().find('select.event').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_content_chart4'),
                data: {
                    granularity: granularity,
                    type: type,
                    association: association,
                    merchant: merchant,
                    event: event,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("content_chart4", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] clic sur participer événement</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#content_chart4').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function contentChart5() {
        if ($('#content_chart5').length) {
            $('.loader').removeClass('hide');
            var granularity = $('#content_chart5').parent().parent().find('select.granularity').find("option:selected").val();
            var type = $('#content_chart5').parent().parent().find('select.type').find("option:selected").val();
            var association = $('#content_chart5').parent().parent().find('select.association').find("option:selected").val();
            var merchant = $('#content_chart5').parent().parent().find('select.merchant').find("option:selected").val();
            var event = $('#content_chart5').parent().parent().find('select.event').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_content_chart5'),
                data: {
                    granularity: granularity,
                    type: type,
                    association: association,
                    merchant: merchant,
                    event: event,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("content_chart5", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] clic sur je viens aider événement</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#content_chart5').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function contentChart6() {
        if ($('#content_chart6').length) {
            $('.loader').removeClass('hide');
            var type = $('#content_chart6').parent().parent().find('select.type').find("option:selected").val();
            var association = $('#content_chart6').parent().parent().find('select.association').find("option:selected").val();
            var merchant = $('#content_chart6').parent().parent().find('select.merchant').find("option:selected").val();
            var granularity = $('#content_chart6').parent().parent().find('select.granularity').find("option:selected").val();
            var enabled = $('#content_chart6').parent().parent().find('select.enabled').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_content_chart6'),
                data: {
                    granularity: granularity,
                    type: type,
                    association: association,
                    merchant: merchant,
                    enabled: enabled,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("content_chart6", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] articles</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#content_chart6').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function contentChart7() {
        if ($('#content_chart7').length) {
            $('.loader').removeClass('hide');
            var typeComment = $('#content_chart7').parent().parent().find('select.typeComment').find("option:selected").val();
            var association = $('#content_chart7').parent().parent().find('select.association').find("option:selected").val();
            var merchant = $('#content_chart7').parent().parent().find('select.merchant').find("option:selected").val();
            var event = $('#content_chart7').parent().parent().find('select.event').find("option:selected").val();
            var article = $('#content_chart7').parent().parent().find('select.article').find("option:selected").val();
            var granularity = $('#content_chart7').parent().parent().find('select.granularity').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_content_chart7'),
                data: {
                    granularity: granularity,
                    typeComment: typeComment,
                    event: event,
                    article: article,
                    association: association,
                    merchant: merchant,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("content_chart7", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] commentaires</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#content_chart7').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function contentChart8() {
        if ($('#content_chart8').length) {
            $('.loader').removeClass('hide');
            var typeComment = $('#content_chart8').parent().parent().find('select.typeComment').find("option:selected").val();
            var association = $('#content_chart8').parent().parent().find('select.association').find("option:selected").val();
            var merchant = $('#content_chart8').parent().parent().find('select.merchant').find("option:selected").val();
            var event = $('#content_chart8').parent().parent().find('select.event').find("option:selected").val();
            var article = $('#content_chart8').parent().parent().find('select.article').find("option:selected").val();
            var granularity = $('#content_chart8').parent().parent().find('select.granularity').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_content_chart8'),
                data: {
                    granularity: granularity,
                    typeComment: typeComment,
                    event: event,
                    article: article,
                    association: association,
                    merchant: merchant,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("content_chart8", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] commentaires laissés</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantityComment"
                            }, {
                                "balloonText": "<span style='font-size:13px;'>[[value]] commentaires supprimés</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantityCommentDelete"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#content_chart8').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function contentChart9() {
        if ($('#content_chart9').length) {
            var typeEvent = $('#content_chart9').parent().parent().find('select.typeEvent').find("option:selected").val();
            var event = $('#content_chart9').parent().parent().find('select.event').find("option:selected").val();
            var category = $('#content_chart9').parent().parent().find('select.category').find("option:selected").val();
            var granularity = $('#content_chart9').parent().parent().find('select.granularity').find("option:selected").val();
            $('.loader').removeClass('hide');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_content_chart9'),
                data: {
                    typeEvent: typeEvent,
                    event: event,
                    category: category,
                    granularity: granularity,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("content_chart9", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] pushs</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantityPush"
                            }, {
                                "balloonText": "<span style='font-size:13px;'>[[value]] sessions actives</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantitySession"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#content_chart9').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function contentChart10() {
        if ($('#content_chart10').length) {
            var typeEvent = $('#content_chart10').parent().parent().find('select.typeEvent').find("option:selected").val();
            var event = $('#content_chart10').parent().parent().find('select.event').find("option:selected").val();
            var category = $('#content_chart10').parent().parent().find('select.category').find("option:selected").val();
            var granularity = $('#content_chart10').parent().parent().find('select.granularity').find("option:selected").val();
            $('.loader').removeClass('hide');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_content_chart10'),
                data: {
                    typeEvent: typeEvent,
                    event: event,
                    category: category,
                    granularity: granularity,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("content_chart10", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] pushs</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantityPush"
                            }, {
                                "balloonText": "<span style='font-size:13px;'>[[value]] clic sur détail événement</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantityDetail"
                            }, {
                                "balloonText": "<span style='font-size:13px;'>[[value]] clic sur je participe</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantityParticipation"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#general_chart10').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }






    function userChart1() {
        if ($('#user_chart1').length) {
            $('.loader').removeClass('hide');
            var categories = [];
            $('#user_chart1').parent().parent().find('.categories').each(function () {
                if ($(this).is(':checked'))
                    categories.push($(this).val());
            });
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_user_chart1'),
                data: {
                    categories: categories,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("user_chart1", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "startDuration": 1,
                        "graphs": [{
                                "alphaField": "alpha",
                                "balloonText": "<span style='font-size:13px;'>[[value]] utilisateurs</span>",
                                "dashLengthField": "dashLengthColumn",
                                "fillAlphas": 1,
                                "title": "Income",
                                "type": "column",
                                "valueField": "quantity"
                            }],
                        "categoryField": "day",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#user_chart1').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function userChart2() {
        if ($('#user_chart2').length) {
            $('.loader').removeClass('hide');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_user_chart2'),
                data: {
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("user_chart2", {
                        "type": "pie",
                        "theme": "light",
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueField": "quantity",
                        "titleField": "interest",
                    });

                    $('#user_chart2').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function userChart3() {
        /*if ($('#user_chart3').length) {
         $('.loader').removeClass('hide');
         var granularity = $('#user_chart3').parent().parent().find('select.granularity').find("option:selected").val();
         $.ajax({
         type: 'POST',
         url: Routing.generate('app_stat_content_chart3'),
         data: {
         granularity: granularity,
         dateBefore: $('#stat_filter_dateBefore').val(),
         dateAfter: $('#stat_filter_dateAfter').val(),
         },
         dataType: "json",
         success: function (datas) {
         var chart = AmCharts.makeChart("user_chart3", {
         "type": "serial",
         "theme": "light",
         "autoMargins": true,
         "marginLeft": 50,
         "marginRight": 8,
         "marginTop": 10,
         "marginBottom": 120,
         "fontFamily": 'Open Sans',
         "color": '#888',
         "dataProvider": datas,
         "valueAxes": [{
         "axisAlpha": 0,
         "position": "left"
         }],
         "graphs": [{
         "balloonText": "<span style='font-size:13px;'>[[value]] utilisateur ayant changé leur ville pricipale</span>",
         "bullet": "round",
         "lineThickness": 3,
         "bulletSize": 7,
         "bulletBorderAlpha": 1,
         "bulletColor": "#FFFFFF",
         "useLineColorForBulletBorder": true,
         "bulletBorderThickness": 3,
         "fillAlphas": 0,
         "lineAlpha": 1,
         "valueField": "quantity"
         }],
         "categoryField": "value",
         "categoryAxis": {
         "gridPosition": "start",
         "axisAlpha": 0,
         "tickLength": 0,
         "labelRotation": 75,
         "labelOffset": 10
         },
         });
         
         $('#user_chart3').closest('.portlet').find('.fullscreen').click(function () {
         chart.invalidateSize();
         });
         $('.loader').addClass('hide');
         }
         });
         }*/
    }
    function userChart4() {
        if ($('#user_chart4').length) {
            $('.loader').removeClass('hide');
            var granularity = $('#user_chart4').parent().parent().find('select.granularity').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_user_chart4'),
                data: {
                    granularity: granularity,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("user_chart4", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] utilisateur ayant changé leur ville principale</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#user_chart4').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function userChart5() {
        if ($('#user_chart5').length) {
            $('.loader').removeClass('hide');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_user_chart5'),
                data: {
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("user_chart5", {
                        "type": "pie",
                        "theme": "light",
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueField": "quantity",
                        "titleField": "city",
                    });

                    $('#user_chart5').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }



    function cityhallChart1() {
        if ($('#cityhall_chart1').length) {
            var granularity = $('#cityhall_chart1').parent().parent().find('select.granularity').find("option:selected").val();
            $('.loader').removeClass('hide');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_cityhall_chart1'),
                data: {
                    granularity: granularity,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("cityhall_chart1", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] travaux</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#cityhall_chart1').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }


    function cityhallChart2() {
        if ($('#cityhall_chart2').length) {
            $('.loader').removeClass('hide');
            var granularity = $('#cityhall_chart2').parent().parent().find('select.granularity').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_cityhall_chart2'),
                data: {
                    granularity: granularity,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("cityhall_chart2", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] évènements</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#cityhall_chart2').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function cityhallChart3() {
        if ($('#cityhall_chart3').length) {
            $('.loader').removeClass('hide');
            var granularity = $('#cityhall_chart3').parent().parent().find('select.granularity').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_cityhall_chart3'),
                data: {
                    granularity: granularity,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("cityhall_chart3", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] pushs</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#cityhall_chart3').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function cityhallChart4() {
        if ($('#cityhall_chart4').length) {
            $('.loader').removeClass('hide');
            var granularity = $('#cityhall_chart4').parent().parent().find('select.granularity').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_cityhall_chart4'),
                data: {
                    granularity: granularity,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("cityhall_chart4", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] projets</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantity"
                            }],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#cityhall_chart4').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function cityhallChart5() {
        if ($('#cityhall_chart5').length) {
            $('.loader').removeClass('hide');
            var type = $('#cityhall_chart5').parent().parent().find('select.type').find("option:selected").val();
            var association = $('#cityhall_chart5').parent().parent().find('select.association').find("option:selected").val();
            var merchant = $('#cityhall_chart5').parent().parent().find('select.merchant').find("option:selected").val();
            var time = $('#cityhall_chart5').parent().parent().find('select.time').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_cityhall_chart5'),
                data: {
                    type: type,
                    association: association,
                    merchant: merchant,
                    time: time,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    $('#timeEvent').html(datas.time);
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function cityhallChart6() {
        if ($('#cityhall_chart6').length) {
            $('.loader').removeClass('hide');
            var time = $('#cityhall_chart6').parent().parent().find('select.time').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_cityhall_chart6'),
                data: {
                    time: time,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    $('#timeReporting').html(datas.time);
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function cityhallChart7() {
        if ($('#cityhall_chart7').length) {
            var granularity = $('#cityhall_chart7').parent().parent().find('select.granularity').find("option:selected").val();
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_cityhall_chart7'),
                data: {
                    granularity: granularity,
                    dateBefore: $('#stat_filter_dateBefore').val(),
                    dateAfter: $('#stat_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("cityhall_chart7", {
                        "type": "serial",
                        "theme": "light",
                        "autoMargins": true,
                        "marginLeft": 50,
                        "marginRight": 8,
                        "marginTop": 10,
                        "marginBottom": 120,
                        "fontFamily": 'Open Sans',
                        "color": '#888',
                        "dataProvider": datas,
                        "valueAxes": [{
                                "axisAlpha": 0,
                                "position": "left"
                            }],
                        "graphs": [{
                                "balloonText": "<span style='font-size:13px;'>[[value]] session ouverte</span>",
                                "bullet": "round",
                                "lineThickness": 3,
                                "bulletSize": 7,
                                "bulletBorderAlpha": 1,
                                "bulletColor": "#FFFFFF",
                                "useLineColorForBulletBorder": true,
                                "bulletBorderThickness": 3,
                                "fillAlphas": 0,
                                "lineAlpha": 1,
                                "valueField": "quantitySessions"
                            }, {
                                "alphaField": "alpha",
                                "balloonText": "<span style='font-size:13px;'>[[value]] minutes par session ouverte</span>",
                                "dashLengthField": "dashLengthColumn",
                                "fillAlphas": 1,
                                "title": "Income",
                                "type": "column",
                                "valueField": "quantitySessionsDuration"
                            }
                        ],
                        "categoryField": "value",
                        "categoryAxis": {
                            "gridPosition": "start",
                            "axisAlpha": 0,
                            "tickLength": 0,
                            "labelRotation": 75,
                            "labelOffset": 10
                        },
                    });

                    $('#cityhall_chart7').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                    $('.loader').addClass('hide');
                }
            });
        }
    }
    function cityhallChart8() {
        if ($('#cityhall_chart8').length) {
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_stat_cityhall_chart8'),
                data: {},
                dataType: "json",
                success: function (datas) {
                    var chart = AmCharts.makeChart("cityhall_chart8", {
                    });

                    $('#cityhall_chart8').closest('.portlet').find('.fullscreen').click(function () {
                        chart.invalidateSize();
                    });
                }
            });
        }
    }
});


