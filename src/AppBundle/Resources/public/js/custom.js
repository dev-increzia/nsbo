
$(document).ready(function () {

    /* -------------------------------------INITIALISATION----------------------------------- */
    var datatables = [];
    var datatabeSettings = {
        "ordering": false,
        "pageLength": 25,
        "paginate": true,
        "lengthChange": false,
        "searching": false,
        "processing": true,
        "serverSide": true,
        "sAjaxDataProp": "data",
        "oLanguage": {
            "sProcessing": "Traitement en cours...",
            "sSearch": "Rechercher",
            "sLengthMenu": "Afficher _MENU_ &eacute;l&eacute;ments",
            "sInfo": "Affichage de l'&eacute;lement _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
            "sInfoEmpty": "Affichage de l'&eacute;lement 0 &agrave; 0 sur 0 &eacute;l&eacute;ments",
            "sInfoFiltered": "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
            "sInfoPostFix": "",
            "sLoadingRecords": "Chargement en cours...",
            "sZeroRecords": "Aucun &eacute;l&eacute;ment &agrave; afficher",
            "sEmptyTable": "Aucune donnée disponible dans le tableau",
            "oPaginate": {
                "sFirst": "Premier",
                "sPrevious": "Pr&eacute;c&eacute;dent",
                "sNext": "Suivant",
                "sLast": "Dernier"
            },
            "oAria": {
                "sSortAscending": ": activer pour trier la colonne par ordre croissant",
                "sSortDescending": ": activer pour trier la colonne par ordre décroissant"
            }
        },
        "columns": null,
        "ajax": null
    };
    var marker = null;
    var mymap = null;

    var cityhallsDt = null;
    var abusesDt = null;
    var numbersDt = null;
    var associationsDt = null;
    var merchantsDt = null;
    var reportingsDt = null;
    var interestsDt = null;
    var worksDt = null;
    var citiesDt = null;
    var pushsDt = null;
    var pushsCityhallDt = null;
    var usersDt = null;
    var usersCityhallDt = null;
    var interestsCategoryDt = null;
    var categoriesDt = null;
    var reportingCategoryDt = null;
    var numberCategoryDt = null;
    var surveysDt = null;
    var surveysResponsesDt = null;
    var mapHeadingDt = null;
    var articleHeadingDt = null;
    var reportingHeadingDt = null;
    var phoneBookHeadingDt = null;
    var usefullLinkHeadingDt = null;
    init();
    /* -------------------------------------INITIALISATION END----------------------------------- */


    /* -------------------------------------EVENEMENTS SCROLL----------------------------------- */
    $(window).scroll(function () {
        if ($(window).scrollTop() + $(window).height() == getDocHeight()) {
            articles();
            projects();
            events();
            goodPlans();
            comments();
        }
    });
    function getDocHeight() {
        var D = document;
        return Math.max(
            D.body.scrollHeight, D.documentElement.scrollHeight,
            D.body.offsetHeight, D.documentElement.offsetHeight,
            D.body.clientHeight, D.documentElement.clientHeight
        );
    }
    /* -------------------------------------EVENEMENTS SCROLL END----------------------------------- */



    /* -------------------------------------EVENEMENTS AUTOCOMPLETE----------------------------------- */
    $("#association_suAdminEmail").autocomplete({
        source: function (req, response) {
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_association_suAdmin_autocomplete'),
                dataType: 'json',
                data: {
                    search: $('#association_suAdminEmail').val(),
                    cityhall: $('#association_suAdminEmail').attr('data-cityhall')
                },
                success: function (data) {
                    var jsonArray = $.parseJSON(data);
                    response($.map(jsonArray, function (objet) {
                        return objet;
                    }));
                }
            });
        },
        select: function (event, ui) {
        }
    });
    $("#merchant_suAdminEmail").autocomplete({
        source: function (req, response) {
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_merchant_suAdmin_autocomplete'),
                dataType: 'json',
                data: {
                    search: $('#merchant_suAdminEmail').val(),
                    cityhall: $('#merchant_suAdminEmail').attr('data-cityhall')
                },
                success: function (data) {
                    var jsonArray = $.parseJSON(data);
                    response($.map(jsonArray, function (objet) {
                        return objet;
                    }));
                }
            });
        },
        select: function (event, ui) {
        }
    });
    $("#article_heading_emailAdmin").autocomplete({
        source: function (req, response) {
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_article_heading_admins_autocomplete'),
                dataType: 'json',
                data: {
                    search: $('#article_heading_emailAdmin').val(),
                    cityhall: $('#article_heading_emailAdmin').attr('data-community')
                },
                success: function (data) {
                    var jsonArray = $.parseJSON(data);
                    response($.map(jsonArray, function (objet) {
                        return objet;
                    }));
                }
            });
        },
        select: function (event, ui) {
        }
    });
    /* -------------------------------------EVENEMENTS AUTOCOMPLETE END----------------------------------- */



    /* -------------------------------------EVENEMENTS CLICK----------------------------------- */
    $("body").on('click', ".confirmSecurity", function (e) {
        var conf = confirm("Souhaitez-vous générer un nouveau mot de passe ?");
        if (conf == false)
            return false;
    });

    $("body").on('click', ".mailtoEvent", function (e) {
        e.stopPropagation();
    });
    $("body").on('click', ".updateEvent", function (e) {
        e.stopPropagation();
    });
    $("body").on('click', ".activateEvent", function (e) {
        e.stopPropagation();
    });

    $("body").on('click', ".updateModerateEvent, .updateModerateMerchant, .updateModerateAssociation, .updateModerateEventSecondary", function (e) {
        e.preventDefault();
        e.stopPropagation();
        var moderate = $(this).attr('data-type');
        var href = $(this).attr('href');
        $.ajax({
            type: 'POST',
            url: href,
            data: {'moderate': moderate},
            dataType: "json",
            success: function (datas) {
                location.reload();
            }
        });
    });
    $("body").on('click', '.updateModerateEventOptions', function (e) {
        e.preventDefault();
        e.stopPropagation();
    });
    $("body").on('click', "#exportData", function (e) {
        e.preventDefault();
        e.stopPropagation();
        var href = $(this).attr('href');
        var enabled = null;
        $('#user_filter_enabled').find('option').each(function () {
            if ($(this).is(':selected')) {
                enabled = $(this).val();
            }
        });
        var role = null;
        $('#user_filter_role').find('option').each(function () {
            if ($(this).is(':selected')) {
                role = $(this).val();
            }
        });
        var association = null;
        $('#user_filter_association').find('option').each(function () {
            if ($(this).is(':selected')) {
                association = $(this).val();
            }
        });
        var merchant = null;
        $('#user_filter_merchant').find('option').each(function () {
            if ($(this).is(':selected')) {
                merchant = $(this).val();
            }
        });
        var firstname = $('#user_filter_firstname').val();
        var lastname = $('#user_filter_lastname').val();
        var data = {
            'lastname': $('#user_filter_lastname').val(),
            'firstname': $('#user_filter_firstname').val(),
            'enabled': enabled,
            'role': role,
            'association': association,
            'merchant': merchant
        };
        if (!$.isEmptyObject(data))
        {
            href += (href.indexOf('?') >= 0 ? '&' : '?') + $.param(data);
            console.log(href);
            window.location.replace(href);
        }
    });

    $("body").on('click', ".commentEvent", function (e) {
        window.location.href = $(this).attr('data-url');
    });
    $("body").on('click', ".parentArticle", function (e) {
        e.stopPropagation();
    });
    $("body").on('click', ".mailtoArticle", function (e) {
        e.stopPropagation();
    });
    $("body").on('click', ".updateArticle", function (e) {
        e.stopPropagation();
    });
    $("body").on('click', ".activateArticle", function (e) {
        e.stopPropagation();
    });

    $("body").on('click', ".eventWaitSub", function (e) {
        e.stopPropagation();
    });

    $("body").on('click', ".ajax", function () {
        var url = $(this).attr('href');
        var modalId = $(this).attr('data-target');
        $.ajax({
            type: 'GET',
            url: url,
            data: {},
            dataType: "json",
            success: function (datas) {
                if ($(modalId).length === 0) {
                    $('body').append(datas.content);
                }
                $(modalId).modal('show');
            }
        });
        return false;
    });
    $("body").on('click', ".buttonDay", function () {
        if ($(this).hasClass('active'))
            $(this).removeClass('active');
        else
            $(this).addClass('active');
    });
    /* $("body").on('click', "#eventPersonalized", function (e) {
         e.preventDefault();
         e.stopPropagation();
         $('#eventPersonalizedResult').addClass('hide');


         var monday = false;
         var tuesday = false;
         var wednesday = false;
         var thursday = false;
         var friday = false;
         var saturday = false;
         var sunday = false;
         if ($('#monday').hasClass('active'))
             monday = true;
         if ($('#tuesday').hasClass('active'))
             tuesday = true;
         if ($('#wednesday').hasClass('active'))
             wednesday = true;
         if ($('#thursday').hasClass('active'))
             thursday = true;
         if ($('#friday').hasClass('active'))
             friday = true;
         if ($('#saturday').hasClass('active'))
             saturday = true;
         if ($('#sunday').hasClass('active'))
             sunday = true;
         if (!monday && !tuesday && !wednesday && !thursday && !friday && !saturday && !sunday) {
             alert("Merci d'indiquer au moins un jour de la semaine");
             return false;
         }


         var ageFrom = $('#ageFrom').val();
         var ageTo = $('#ageTo').val();
         if (ageFrom === '' || ageTo === '') {
             alert("Merci d'indiquer un âge minimum et maximum");
             return false;
         }
         if (ageFrom > ageTo) {
             alert("L'âge maximum ne peut pas être inférieur à l'âge minimum");
             return false;
         }
         if (ageFrom < 0) {
             alert("L'âge minimum ne peut pas être inférieur à zéro");
             return false;
         }
         if (ageFrom < 0) {
             alert("L'âge maximum ne peut pas être inférieur à zéro");
             return false;
         }

         var lessThanSix = false;
         var betweenSixTwelve = false;
         var betweenTwelveEighteen = false;
         var allChildrens = false;

         if ($('#childrens0-6').is(':checked'))
             lessThanSix = true;
         if ($('#childrens6-12').is(':checked'))
             betweenSixTwelve = true;
         if ($('#childrens12-18').is(':checked'))
             betweenTwelveEighteen = true;
         if ($('#childrensAll').is(':checked'))
             allChildrens = true;
         if (!lessThanSix && !betweenSixTwelve && !betweenTwelveEighteen && !allChildrens) {
             alert("Merci d'indiquer au moins une tranche d'enfant");
             return false;
         }


         $('.loader').removeClass('hide');
         $.ajax({
             type: 'POST',
             url: Routing.generate('app_event_personalized'),
             data: {
                 ageFrom: ageFrom,
                 ageTo: ageTo,
                 monday: monday,
                 tuesday: tuesday,
                 wednesday: wednesday,
                 thursday: thursday,
                 friday: friday,
                 saturday: saturday,
                 sunday: sunday,
                 lessThanSix: lessThanSix,
                 betweenSixTwelve: betweenSixTwelve,
                 betweenTwelveEighteen: betweenTwelveEighteen,
                 allChildrens: allChildrens,
             },
             dataType: "json",
             success: function (datas) {
                 $('#eventPersonalizedResult').removeClass('hide');
                 $('.loader').addClass('hide');
                 $('#countUsers').html(datas.countUsers);
             }
         });

         return false;
     });
     */

    /* -------------------------------------EVENEMENTS CLICK END----------------------------------- */

    /* -------------------------------------EVENEMENTS KEYDOWN----------------------------------- */
    $('body').keydown(function (event) {
        if (event.keyCode === 120)
            window.location.replace(Routing.generate('app_lock'));
    });
    /* -------------------------------------EVENEMENTS KEYDOWN END----------------------------------- */

    /* -------------------------------------EVENEMENTS KEYPRESS----------------------------------- */
    $("body").on('keypress', '.timepicker', function () {
        return false;
    });
    /* -------------------------------------EVENEMENTS KEYPRESS END----------------------------------- */



    /* -------------------------------------EVENEMENTS CHANGE START----------------------------------- */
    $("body").on('change', '#cityhallSelect', function () {
        var cityhall = $(this).find("option:selected").val();
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_current_cityhall'),
            data: {'cityhall': cityhall},
            dataType: "json",
            success: function (datas) {
                location.reload();
            }
        });
    });
    $("body").on('change', '.updateModerateReporting', function () {
        var moderate = $(this).find("option:selected").val();
        var href = $(this).attr('data-url');
        $.ajax({
            type: 'POST',
            url: href,
            data: {'moderate': moderate},
            dataType: "json",
            success: function (datas) {
            }
        });
    });
    $("body").on('change', '.updateModerateEventOptions', function (e) {
        e.preventDefault();
        e.stopPropagation();
    });

    $(".changeForMap").on('change', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var locationName = $(this).val();
        $.ajax({
            type: 'POST',
            url: Routing.generate('app_locateAddress'),
            dataType: 'json',
            data: {'locationName': locationName},
            success: function (data) {
                if (data.lat !== 0 && data.lng !== 0 && marker !== null) {
                    var newLatLng = new L.LatLng(data.lat, data.lng);
                    marker.setLatLng(newLatLng);
                    mymap.panTo(newLatLng);
                    $('#interest_latitude').val(data.lat)
                    $('#interest_longitude').val(data.lng);
                }
            }
        });
        return false;

    });

    $(".typeArticle").on('change', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('.association').removeAttr('required');
        $('.merchant').removeAttr('required');
        $('.user').removeAttr('required');
        $('.cityhall').removeAttr('required');
        $('.category').removeAttr('required');
        var type = $(this).val();
        $('.' + type).attr('required', 'required');
        return false;
    });

    $(".pushEventType").on('change', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var type = $(this).val();
        if (type == 'merchant' || type == 'association') {
            $('#pushCategory').removeClass('hide');
        } else
            $('#pushCategory').addClass('hide');
        return false;
    });
    $("#user_filter_role").on('change', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var type = $(this).val();
        $('#association').addClass('hide');
        $('#merchant').addClass('hide');
        if (type == 'associationSuAdmin' || type == 'associationAdmin') {
            $('#association').removeClass('hide');
        }
        if (type == 'merchantSuAdmin' || type == 'merchantAdmin') {
            $('#merchant').removeClass('hide');
        }
        return false;
    });
    $("#comment_filter_type").on('change', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var type = $(this).val();
        if (type == 'event') {
            $('#article').addClass('hide');
            $('#event').removeClass('hide');
        }
        if (type == 'article') {
            $('#event').addClass('hide');
            $('#article').removeClass('hide');
        }

        if (type == '') {
            $('#event').removeClass('hide');
            $('#article').removeClass('hide');
        }
        return false;
    });
    /* -------------------------------------EVENEMENTS CHANGE END----------------------------------- */



    /* -------------------------------------EVENEMENTS SWITCH CHANGE----------------------------------- */
    $('#abusesTable').on('switchChange.bootstrapSwitch', 'input.moderate', function (e, state) {
        var url = $(this).attr('data-url');
        var state = $(this).bootstrapSwitch('state');
        $.ajax({
            type: 'POST',
            url: url,
            data: {'moderate': state},
            dataType: "json",
            success: function (datas) {
            }
        });
    });
    $('#usersTable').on('switchChange.bootstrapSwitch', 'input.enabled', function (e, state) {
        var url = $(this).attr('data-url');
        var state = $(this).bootstrapSwitch('state');
        $.ajax({
            type: 'POST',
            url: url,
            data: {'enabled': state},
            dataType: "json",
            success: function (datas) {
            }
        });
    });
    $('body').on('switchChange.bootstrapSwitch', 'input.pushEnabled', function (e, state) {
        var state = $(this).bootstrapSwitch('state');
        pushInfo(state);
    });
    $('body').on('switchChange.bootstrapSwitch', 'input.pushArticleInfo', function (e, state) {
        var state = $(this).bootstrapSwitch('state');
        pushArticleInfo(state);
    });
    $('body').on('switchChange.bootstrapSwitch', 'input.pushGoodPlanInfo', function (e, state) {
        var state = $(this).bootstrapSwitch('state');
        pushGoodPlanInfo(state);
    });


    /* -------------------------------------EVENEMENTS SWITCH CHANGE END----------------------------------- */



    /* -------------------------------------EVENEMENTS SUBMIT END----------------------------------- */
    $("body").on('submit', "#cityhalls_filter", function (e) {
        cityhallsDatatable();
        return false;
    });
    $("body").on('submit', "#cities_filter", function (e) {
        citiesDatatable();
        return false;
    });
    $("body").on('submit', "#intercommunals_filter", function (e) {
        intercommunalsDatatable();
        return false;
    });
    /*$("body").on('submit', "#abuses_filter", function (e) {
        abusesDatatable();
        return false;
    });*/
    $("body").on('submit', "#mapHeading_filter", function (e) {
        mapHeadingDatatable();
        return false;
    });

    $("body").on('submit', "#reportingHeading_filter", function (e) {
        reportingHeadingDatatable();
        return false;
    });

    $("body").on('submit', "#articleHeading_filter", function (e) {
        articleHeadingDatatable();
        return false;
    });
    $("body").on('submit', "#phoneBookHeading_filter", function (e) {
        phoneBookHeadingDatatable();
        return false;
    });
    $("body").on('submit', "#usefullLinkHeading_filter", function (e) {
        usefullLinkHeadingDatatable();
        return false;
    });


    $("body").on('submit', "#numbers_filter", function (e) {
        numbersDatatable();
        return false;
    });
    $("body").on('submit', "#associations_filter", function (e) {
        associationsDatatable();
        return false;
    });
    $("body").on('submit', "#merchants_filter", function (e) {
        merchantsDatatable();
        return false;
    });
    /*$("body").on('submit', "#reportings_filter", function (e) {
        reportingsDatatable();
        return false;
    });*/
    $("body").on('submit', "#interests_filter", function (e) {
        interestsDatatable();
        return false;
    });
    $("body").on('submit', "#works_filter", function (e) {
        worksDatatable();
        return false;
    });
    $("body").on('submit', "#pushs_filter", function (e) {
        pushsDatatable();
        return false;
    });
    $("body").on('submit', "#pushsCityhall_filter", function (e) {
        pushsCityhallDatatable();
        return false;
    });
    $("body").on('submit', "#users_filter", function (e) {
        usersDatatable();
        return false;
    });
    $("body").on('submit', "#usersCityhall_filter", function (e) {
        usersCityhallDatatable();
        return false;
    });

    $("body").on('submit', "#articles_filter", function (e) {
        $('#articles').html('');
        $('#articles').attr('data-page', 0);
        $('#articles').attr('data-break', '0');
        $('#articles').attr('data-loading', 0);
        articles();
        return false;
    });
    $("body").on('submit', "#projects_filter", function (e) {
        $('#projects').html('');
        $('#projects').attr('data-page', 0);
        $('#projects').attr('data-break', '0');
        $('#projects').attr('data-loading', 0);
        projects();
        return false;
    });
    $("body").on('submit', "#events_filter", function (e) {
        $('#events').html('');
        $('#events').attr('data-page', 0);
        $('#events').attr('data-break', '0');
        $('#events').attr('data-loading', 0);
        events();
        return false;
    });
    $("body").on('submit', "#good_plan_filter", function (e) {
        $('#goodPlans').html('');
        $('#goodPlans').attr('data-page', 0);
        $('#goodPlans').attr('data-break', '0');
        $('#goodPlans').attr('data-loading', 0);
        goodPlans();
        return false;
    });
    $("body").on('submit', "#comments_filter", function (e) {
        $('#comments').html('');
        $('#comments').attr('data-page', 0);
        $('#comments').attr('data-break', '0');
        $('#comments').attr('data-loading', 0);
        comments();
        return false;
    });
    /* -------------------------------------EVENEMENTS SUBMIT END----------------------------------- */


    /* -------------------------------------FUNCTIONS----------------------------------- */

    function init() {
        //pages info
        $.fn.dataTableExt.oApi.fnPagingInfo = function (oSettings) {
            return {
                "iStart": oSettings._iDisplayStart,
                "iEnd": oSettings.fnDisplayEnd(),
                "iLength": oSettings._iDisplayLength,
                "iTotal": oSettings.fnRecordsTotal(),
                "iFilteredTotal": oSettings.fnRecordsDisplay(),
                "iPage": oSettings._iDisplayLength === -1 ?
                    0 : Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength),
                "iTotalPages": oSettings._iDisplayLength === -1 ?
                    0 : Math.ceil(oSettings.fnRecordsDisplay() / oSettings._iDisplayLength)
            };
        };
        //tables (managed by dataTable)
        $('table.dataTable').each(function () {
            var id = $(this).attr('id');
            if (id !== "undefined") {
                var config = datatabeSettings;
                if ($(this).hasClass('nopaginate')) {
                    config.bPaginate = false;
                }
                if ($(this).hasClass('withDate')) {
                    var column = $(this).attr('date-column');
                    config.columnDefs = [
                        {type: 'date-eu', targets: parseInt(column)}
                    ];
                    if ($(this).attr('date-column2')) {
                        var column = $(this).attr('date-column2');
                        config.columnDefs = [
                            {type: 'date-eu', targets: parseInt(column)}
                        ];
                    }
                }
                datatables[id] = $('#' + id).dataTable(config);
            }
        });

        // sidebar activation
        $('.page-sidebar-menu li').each(function () {
            if ($(this).find('a').attr('href') === location.pathname) {
                $(this).addClass('active');
                if ($(this).parent().hasClass('sub-menu')) {
                    $(this).parent().parent().addClass('active open');
                    $(this).parent().parent().find('span.arrow').addClass('open');
                    $(this).parent().css('display', 'block');
                }
            }
        });

        if($('#cityhallSelect').length > 0) {
            $('#cityhallSelect').selectpicker();
        }


        $(".login-bg").backstretch(
            [$(".login-bg").attr('data-bg1'),
                $(".login-bg").attr('data-bg2'),
                $(".login-bg").attr('data-bg3')],
            {fade: 1e3, duration: 8e3}
        );
//Get latitude and longitude from Google API
        var geocoder = new google.maps.Geocoder();
        var latitude = $('#map').attr('data-latitude');
        var longitude = $('#map').attr('data-longitude');
        var mapL = null;
        if ($('#map').length === 1)
        {
            document.getElementById('submitAdresse').addEventListener('click', function () {
                geocodeAddress(geocoder);
            });
        }
        function geocodeAddress(geocoder) {
            if (document.getElementById('work_address')){
                var address = document.getElementById('work_address').value;
            }else if(document.getElementById('interest_address')){
                var address = document.getElementById('interest_address').value;
            }else{
                var address = document.getElementById('city_address').value;
            }

            geocoder.geocode({'address': address}, function (results, status) {
                if (status === 'OK') {
                    latitude = results[0].geometry.location.lat();
                    longitude = results[0].geometry.location.lng();
                    updateMarker(latitude, longitude);
                } else {
                    alert('Adresse introuvable, Veuillez réessayer');
                }
            });
        }


        function updateMarker(latitude, longitude) {


            marker.remove();
            marker = L.marker([latitude, longitude], opt).addTo(mymap);


            var position = marker.getLatLng();
            $('.latitude').val(position.lat);
            $('.longitude').val(position.lng);
            marker.setLatLng(position, opt);
            mapL.flyTo([latitude, longitude]);
            marker.on('drag', function (event) {
                var marker = event.target;
                var position = marker.getLatLng();
                $('.latitude').val(position.lat);
                $('.longitude').val(position.lng);
                marker.setLatLng(position, opt);
            });
            marker.on('dragend', function (event) {
                var marker = event.target;
                var position = marker.getLatLng();
                //update adress
                $.ajax({
                    type: 'POST',
                    url: Routing.generate('app_latLngToAddress'),
                    dataType: 'json',
                    data: {'lat': position.lat, 'lng': position.lng},
                    success: function (data) {
                        $('.changeForMap').val(data.address + ' ' + data.city + ' ' + data.cp);
                    }
                });
            });
        }

        if ($('#map').length === 1)
        {
            mapL = L.map('map');
            mymap = mapL.setView([latitude, longitude], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: 'Map data &copy; <a href="https://www.osm.org">OpenStreetMap</a>'}).addTo(mymap);
            var draggable = $('#map').attr('data-draggable');
            var opt = {};
            if (draggable === 'true') {
                opt = {draggable: draggable};
            }
            var marker = L.marker([latitude, longitude], opt).addTo(mymap);
            updateMarker(latitude, longitude);

        }

        if ($('#article_association').length > 0) {
            $('#article_association').attr('required', 'require');
        }
        if ($('#event_cityhall').length > 0) {
            $('#event_cityhall').attr('required', 'require');
        }






        if ($('#event_pushEnabled').length) {
            if ($('#event_pushEnabled').is(':checked')) {
                $('#pushInfo').removeClass('hide');
                $('#event_push_sendAt').attr('required', 'required');
                $('#event_push_content').attr('required', 'required');
            }
        }
        if ($('#article_pushEnabled').length) {
            if ($('#article_pushEnabled').is(':checked')) {
                $('#pushInfo').removeClass('hide');
                $('#article_push_sendAt').attr('required', 'required');
                $('#article_push_content').attr('required', 'required');
            }
        }
        if ($('#good_plan_pushEnabled').length) {
            if ($('#good_plan_pushEnabled').is(':checked')) {
                $('#pushInfo').removeClass('hide');
                $('#good_plan_push_sendAt').attr('required', 'required');
                $('#good_plan_push_content').attr('required', 'required');
            }
        }

        if ($('#comment_filter_type').length) {
            var type = $('#comment_filter_type').val();
            if (type == 'event') {
                $('#article').addClass('hide');
                $('#event').removeClass('hide');
            }
            if (type == 'article') {
                $('#event').addClass('hide');
                $('#article').removeClass('hide');
            }
        }


        //init func
        colorpicker();
        datepicker();
        summernote();
        bootswitch();
        wickedpicker();

        //init ajax datatables
        cityhallsDatatable();
        //abusesDatatable();
        numbersDatatable();
        associationsDatatable();
        merchantsDatatable();
        //reportingsDatatable();
        interestsDatatable();
        worksDatatable();
        citiesDatatable();
        pushsDatatable();
        pushsCityhallDatatable();
        usersDatatable();
        usersCityhallDatatable();
        interestsCategoryDatatable();
        categoriesDatatable();
        reportingCategoryDatatable();
        numberCategoryDatatable();
        surveysDatatable();
        surveysResponsesDatatable();

        mapHeadingDatatable();
        articleHeadingDatatable();
        reportingHeadingDatatable();
        phoneBookHeadingDatatable();
        usefullLinkHeadingDatatable();
        //others list
        articles();
        projects();
        events();
        goodPlans();
        comments();

    }

    function pushInfo(state) {
        if (state) {
            $('#pushInfo').removeClass('hide');
            $('#event_push_sendAt').attr('required', 'required');
            $('#event_push_content').attr('required', 'required');
        } else {
            $('#pushInfo').addClass('hide');
            $('#event_push_sendAt').removeAttr('required');
            $('#event_push_content').removeAttr('required');
        }
    }
    function pushArticleInfo(state) {
        if (state) {
            $('#pushInfo').removeClass('hide');
            $('#article_push_sendAt').attr('required', 'required');
            $('#article_push_content').attr('required', 'required');
        } else {
            $('#pushInfo').addClass('hide');
            $('#article_push_sendAt').removeAttr('required');
            $('#article_push_content').removeAttr('required');
        }
    }
    function pushGoodPlanInfo(state) {
        if (state) {
            $('#pushInfo').removeClass('hide');
            $('#good_plan_push_sendAt').attr('required', 'required');
            $('#good_plan_push_content').attr('required', 'required');
        } else {
            $('#pushInfo').addClass('hide');
            $('#v_sendAt').removeAttr('required');
            $('#good_plan_push_content').removeAttr('required');
        }
    }

    function colorpicker() {
        $('.colorpicker').each(function () {
            $(this).minicolors({
                control: $(this).attr('data-control') || 'hue',
                defaultValue: $(this).attr('data-defaultValue') || '',
                inline: $(this).attr('data-inline') === 'true',
                letterCase: $(this).attr('data-letterCase') || 'lowercase',
                opacity: $(this).attr('data-opacity'),
                position: $(this).attr('data-position') || 'bottom left',
                change: function (hex, opacity) {
                    if (!hex)
                        return;
                    if (opacity)
                        hex += ', ' + opacity;
                },
                theme: 'bootstrap'
            });
        });
    }
    function bootswitch() {
        $('.make-switch').each(function () {
            if ($(this).hasClass('abus')) {
                $(this).bootstrapSwitch({
                    'onText': 'Traité',
                    'offText': 'Non-traité'
                });
            } else {
                $(this).bootstrapSwitch({
                    'onText': 'Activé',
                    'offText': 'Désactivé'
                });
            }

        });
    }
    function datepicker() {
        $.datepicker.regional['fr'] = {
            closeText: 'Fermer',
            prevText: 'Précédent',
            nextText: 'Suivant',
            currentText: 'Aujourd\'hui',
            monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
            dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
            dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
            weekHeader: 'Sem.',
            dateFormat: 'dd/mm/yy',
            firstDay: 1,
            isRTL: false,
            showMonthAfterYear: false,
            yearSuffix: '',
        };
        $.datepicker.setDefaults($.datepicker.regional['fr']);
        var dateToday = new Date();
        //todo not all minDate
        $('.datepicker').each(function () {
            $(this).datetimepicker({
                dateFormat: "dd/mm/yy",
                timeFormat: 'HH:mm',
                closeText: "Fermer",
                currentText: "Maintenant",
                timeOnlyTitle: '',
                timeText: '',
                hourText: 'Heure',
                minuteText: 'Minute',
                secondText: 'Seconde'
                //minDate: dateToday
            });
        });
        $('.datepickerwithouthour').each(function () {
            $(this).datepicker({
                dateFormat: "dd/mm/yy",
                closeText: "Fermer",
                currentText: "Maintenant",
                //minDate: dateToday
            });
        });
    }
    function summernote() {
        $('.summernote').each(function () {
            $(this).summernote({
                height: 300, // set editor height
                minHeight: null, // set minimum height of editor
                maxHeight: null, // set maximum height of editor
                focus: false, // set focus to editable area after initializing summernote
                /*toolbar: [
                 ["style", ["style"]],
                 ["font", ["bold", "underline"]],
                 ["fontname", ["fontname"]],
                 ["color", ["color"]],
                 ["para", ["ul", "ol", "paragraph"]],
                 ["table", ["table"]],
                 ["insert", ["link", "picture", "video"]],
                 ["view", ["fullscreen", "codeview", "help"]]
                 ],*/
            });
        });
    }
    function wickedpicker() {
        $('.timepicker').each(function () {
            var value = $(this).val();
            if (value === '')
                value = '09:00';

            var options = {
                now: value,
                twentyFour: true,
                timeSeparator: ':',
                title: ''
            }
            if ($(this).attr('data-minutesInterval')) {
                options.minutesInterval = $(this).attr('data-minutesInterval');
            }
            $(this).wickedpicker(options);
        });
    }

    function cityhallsDatatable() {
        if (cityhallsDt !== null) {
            cityhallsDt.destroy();
        }
        var intercommunal = null;
        $('#cityhall_filter_intercommunal').find('option').each(function () {
            if ($(this).is(':selected')) {
                intercommunal = $(this).val();
            }
        });
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[6, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_community_grid'),
            "data": {
                dateBefore: $('#cityhall_filter_dateBefore').val(),
                dateAfter: $('#cityhall_filter_dateAfter').val(),
                name: $('#cityhall_filter_name').val(),
                city: $('#cityhall_filter_city').val()
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "name"},
            {"data": "type"},

            {"data": "enabled"},
            {"data": "contact"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        cityhallsDt = $('#cityhallsTable').DataTable(datatabeSettings);
    }
    /*function abusesDatatable() {
        if (abusesDt !== null) {
            abusesDt.destroy();
        }
        var intercommunal = null;
        $('#abus_filter_intercommunal').find('option').each(function () {
            if ($(this).is(':selected')) {
                intercommunal = $(this).val();
            }
        });
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[5, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_abus_grid'),
            "data": {
                dateBefore: $('#abus_filter_dateBefore').val(),
                dateAfter: $('#abus_filter_dateAfter').val(),
                city: $('#abus_filter_city').val(),
                intercommunal: intercommunal
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "citizen"},
            {"data": "city"},
            {"data": "message"},
            {"data": "article"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "moderate"}
        ];
        datatabeSettings["fnDrawCallback"] = function (oSettings) {
            bootswitch();
        };
        abusesDt = $('#abusesTable').DataTable(datatabeSettings);

    }
    */

    function numbersDatatable() {
        if (numbersDt !== null) {
            numbersDt.destroy();
        }
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[6, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_number_grid'),
            "data": {
                dateBefore: $('#number_filter_dateBefore').val(),
                dateAfter: $('#number_filter_dateAfter').val(),
                title: $('#number_filter_title').val(),
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "title"},
            {"data": "address"},
            {"data": "description"},
            {"data": "phone"},
            {"data": "category"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        numbersDt = $('#numbersTable').DataTable(datatabeSettings);
    }

    function associationsDatatable() {
        if (associationsDt !== null) {
            associationsDt.destroy();
        }
        var enabled = null;
        $('#association_filter_enabled').find('option').each(function () {
            if ($(this).is(':selected')) {
                enabled = $(this).val();
            }
        });
        var moderate = null;
        $('#association_filter_moderate').find('option').each(function () {
            if ($(this).is(':selected')) {
                moderate = $(this).val();
            }
        });
        var wait = $('#association_filter_wait').is(':checked');
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[4, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_association_grid'),
            "data": {
                dateBefore: $('#association_filter_dateBefore').val(),
                dateAfter: $('#association_filter_dateAfter').val(),
                name: $('#association_filter_name').val(),
                enabled: enabled,
                moderate: moderate,
                wait: wait,
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "logo"},
            {"data": "name"},
            {"data": "category"},
            {"data": "enabled"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        associationsDt = $('#associationsTable').DataTable(datatabeSettings);
    }
    function merchantsDatatable() {
        if (merchantsDt !== null) {
            merchantsDt.destroy();
        }
        var enabled = null;
        $('#merchant_filter_enabled').find('option').each(function () {
            if ($(this).is(':selected')) {
                enabled = $(this).val();
            }
        });
        var moderate = null;
        $('#merchant_filter_moderate').find('option').each(function () {
            if ($(this).is(':selected')) {
                moderate = $(this).val();
            }
        });
        var wait = $('#merchant_filter_wait').is(':checked');
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[4, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_merchant_grid'),
            "data": {
                dateBefore: $('#merchant_filter_dateBefore').val(),
                dateAfter: $('#merchant_filter_dateAfter').val(),
                name: $('#merchant_filter_name').val(),
                enabled: enabled,
                moderate: moderate,
                wait: wait,
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "logo"},
            {"data": "name"},
            {"data": "category"},
            {"data": "enabled"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        merchantsDt = $('#merchantsTable').DataTable(datatabeSettings);
    }
    /*function reportingsDatatable() {
        if (reportingsDt !== null) {
            reportingsDt.destroy();
        }
        var category = null;
        $('#reporting_filter_category').find('option').each(function () {
            if ($(this).is(':selected')) {
                category = $(this).val();
            }
        });
        var moderate = null;
        $('#reporting_filter_moderate').find('option').each(function () {
            if ($(this).is(':selected')) {
                moderate = $(this).val();
            }
        });
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[7, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_reporting_grid'),
            "data": {
                dateBefore: $('#reporting_filter_dateBefore').val(),
                dateAfter: $('#reporting_filter_dateAfter').val(),
                title: $('#reporting_filter_title').val(),
                category: category,
                moderate: moderate
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "image"},
            {"data": "title"},
            {"data": "category"},
            {"data": "address"},
            {"data": "description"},
            {"data": "author"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "moderate"},
            {"data": "actions"}
        ];
        reportingsDt = $('#reportingsTable').DataTable(datatabeSettings);
    }
    */
    function interestsDatatable() {
        if (interestsDt !== null) {
            interestsDt.destroy();
        }
        var enabled = null;
        $('#interest_filter_enabled').find('option').each(function () {
            if ($(this).is(':selected')) {
                enabled = $(this).val();
            }
        });
        var category = null;
        $('#interest_filter_enabled').find('option').each(function () {
            if ($(this).is(':selected')) {
                category = $(this).val();
            }
        });
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[7, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_interest_grid'),
            "data": {
                dateBefore: $('#interest_filter_dateBefore').val(),
                dateAfter: $('#interest_filter_dateAfter').val(),
                title: $('#interest_filter_title').val(),
                enabled: enabled,
                category: category,
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "title"},
            {"data": "address"},
            {"data": "description"},
            {"data": "enabled"},
            {"data": "category"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        interestsDt = $('#interestsTable').DataTable(datatabeSettings);
    }
    function worksDatatable() {
        if (worksDt !== null) {
            worksDt.destroy();
        }
        var enabled = null;
        $('#work_filter_enabled').find('option').each(function () {
            if ($(this).is(':selected')) {
                enabled = $(this).val();
            }
        });
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[7, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_work_grid'),
            "data": {
                dateBefore: $('#work_filter_dateBefore').val(),
                dateAfter: $('#work_filter_dateAfter').val(),
                title: $('#work_filter_title').val(),
                enabled: enabled,
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "title"},
            {"data": "address"},
            {"data": "description"},
            {"data": "enabled"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        worksDt = $('#worksTable').DataTable(datatabeSettings);
    }
    function citiesDatatable() {
        if (citiesDt !== null) {
            citiesDt.destroy();
        }
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[3, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_city_grid'),
            "data": {
                dateBefore: $('#city_filter_dateBefore').val(),
                dateAfter: $('#city_filter_dateAfter').val(),
                name: $('#city_filter_name').val(),
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "name"},
            {"data": "zipcode"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        citiesDt = $('#citiesTable').DataTable(datatabeSettings);
    }
    function pushsDatatable() {
        if (pushsDt !== null) {
            pushsDt.destroy();
        }
        var eventType = null;
        $('#push_filter_eventType').find('option').each(function () {
            if ($(this).is(':selected')) {
                eventType = $(this).val();
            }
        });
        var event = null;
        $('#push_filter_event').find('option').each(function () {
            if ($(this).is(':selected')) {
                event = $(this).val();
            }
        });
        var category = null;
        $('#push_filter_category').find('option').each(function () {
            if ($(this).is(':selected')) {
                category = $(this).val();
            }
        });
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[1, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_push_grid'),
            "data": {
                dateBefore: $('#push_filter_dateBefore').val(),
                dateAfter: $('#push_filter_dateAfter').val(),
                category: category,
                eventType: eventType,
                event: event,

            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "sendAt"},
            {"data": "parent"},
            {"data": "author"},
            {"data": "category"},
            {"data": "event"},
            {"data": "content"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        pushsDt = $('#pushsTable').DataTable(datatabeSettings);
    }
    function pushsCityhallDatatable() {
        if (pushsCityhallDt !== null) {
            pushsCityhallDt.destroy();
        }

        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[1, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_push_cityhall_grid'),
            "data": {
                dateBefore: $('#push_cityhall_filter_dateBefore').val(),
                dateAfter: $('#push_cityhall_filter_dateAfter').val(),

            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "sendAt"},
            {"data": "author"},
            {"data": "content"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        pushsCityhallDt = $('#pushsCityhallTable').DataTable(datatabeSettings);
    }

    $('#is_comment_active').change(function(){
        var value = $(this).val();
        $.ajax({
            type: "POST",
            url: Routing.generate('app_ajax_community_enable_disable_comment'),
            data: {'item':value},        //POST variable name value
            success: function(msg){

                window.location = window.location;

            }
        });
    });

    function usersDatatable() {
        if (usersDt !== null) {
            usersDt.destroy();
        }
        var enabled = null;
        $('#user_filter_enabled').find('option').each(function () {
            if ($(this).is(':selected')) {
                enabled = $(this).val();
            }
        });
        var role = null;
        $('#user_filter_role').find('option').each(function () {
            if ($(this).is(':selected')) {
                role = $(this).val();
            }
        });
        var association = null;
        $('#user_filter_association').find('option').each(function () {
            if ($(this).is(':selected')) {
                association = $(this).val();
            }
        });
        var merchant = null;
        $('#user_filter_merchant').find('option').each(function () {
            if ($(this).is(':selected')) {
                merchant = $(this).val();
            }
        });
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[5, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_user_grid'),
            "data": {
                lastname: $('#user_filter_lastname').val(),
                firstname: $('#user_filter_firstname').val(),
                enabled: enabled,
                role: role,
                association: association,
                merchant: merchant
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "lastname"},
            {"data": "firstname"},
            {"data": "type"},
            {"data": "role"},
            {"data": "enabled"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        usersDt = $('#usersTable').DataTable(datatabeSettings);
    }

    function usersCityhallDatatable() {
        if (usersCityhallDt !== null) {
            usersCityhallDt.destroy();
        }
        var enabled = null;
        $('#user_cityhall_filter_enabled').find('option').each(function () {
            if ($(this).is(':selected')) {
                enabled = $(this).val();
            }
        });
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[1, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_user_cityhall_grid'),
            "data": {
                lastname: $('#user_cityhall_filter_lastname').val(),
                firstname: $('#user_cityhall_filter_firstname').val(),
                enabled: enabled,
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "lastname"},
            {"data": "firstname"},
            {"data": "enabled"},
            {"data": "suAdmin"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        usersCityhallDt = $('#usersCityhallTable').DataTable(datatabeSettings);
    }


    function interestsCategoryDatatable() {
        if (interestsCategoryDt !== null) {
            interestsCategoryDt.destroy();
        }
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[3, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_interestCategory_grid'),
            "data": {
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "name"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        interestsCategoryDt = $('#interestsCategoryTable').DataTable(datatabeSettings);
    }

    function mapHeadingDatatable() {
        if (mapHeadingDt !== null) {
            mapHeadingDt.destroy();
        }
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[3, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_mapheading_grid'),
            "data": {
                dateBefore: $('#map_heading_filter_dateBefore').val(),
                dateAfter: $('#map_heading_filter_dateAfter').val(),
                enabled: $('#map_heading_filter_enabled').val()
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "title"},
            {"data": "enabled"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        mapHeadingDt = $('#mapHeadingTable').DataTable(datatabeSettings);
    }

    function articleHeadingDatatable() {
        if (articleHeadingDt !== null) {
            articleHeadingDt.destroy();
        }
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[3, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_articleheading_grid'),
            "data": {
                dateBefore: $('#article_heading_filter_dateBefore').val(),
                dateAfter: $('#article_heading_filter_dateAfter').val(),
                enabled: $('#article_heading_filter_enabled').val()
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "title"},
            {"data": "enabled"},
            {"data": "emailAdmin"},
            {"data": "comment"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        articleHeadingDt = $('#articleHeadingTable').DataTable(datatabeSettings);
    }

    function reportingHeadingDatatable() {
        if (reportingHeadingDt !== null) {
            reportingHeadingDt.destroy();
        }
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[3, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_reportingheading_grid'),
            "data": {
                dateBefore: $('#reporting_heading_filter_dateBefore').val(),
                dateAfter: $('#reporting_heading_filter_dateAfter').val(),
                enabled: $('#reporting_heading_filter_enabled').val()
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "title"},
            {"data": "Objets"},
            {"data": "enabled"},

            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        reportingHeadingDt = $('#reportingHeadingTable').DataTable(datatabeSettings);
    }

    function phoneBookHeadingDatatable() {
        if (phoneBookHeadingDt !== null) {
            phoneBookHeadingDt.destroy();
        }
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[3, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_phonebookheading_grid'),
            "data": {
                dateBefore: $('#phone_book_heading_filter_dateBefore').val(),
                dateAfter: $('#phone_book_heading_filter_dateAfter').val(),
                enabled: $('#phone_book_heading_filter_enabled').val()
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "title"},
            {"data": "Objets"},
            {"data": "enabled"},

            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        phoneBookHeadingDt = $('#phoneBookHeadingTable').DataTable(datatabeSettings);
    }

    function usefullLinkHeadingDatatable() {
        if (usefullLinkHeadingDt !== null) {
            usefullLinkHeadingDt.destroy();
        }
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[3, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_usefulllinkheading_grid'),
            "data": {
                dateBefore: $('#usefull_link_heading_filter_dateBefore').val(),
                dateAfter: $('#usefull_link_heading_filter_dateAfter').val(),
                enabled: $('#usefull_link_heading_filter_enabled').val()
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "title"},

            {"data": "enabled"},

            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        usefullLinkHeadingDt = $('#usefullLinkHeadingTable').DataTable(datatabeSettings);
    }





    function categoriesDatatable() {
        if (categoriesDt !== null) {
            categoriesDt.destroy();
        }
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[3, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_category_grid'),
            "data": {
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "name"},
            {"data": "type"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        categoriesDt = $('#categoriesTable').DataTable(datatabeSettings);
    }
    function reportingCategoryDatatable() {
        if (reportingCategoryDt !== null) {
            reportingCategoryDt.destroy();
        }
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[3, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_reportingCategory_grid'),
            "data": {
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "name"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        reportingCategoryDt = $('#reportingCategoryTable').DataTable(datatabeSettings);
    }
    function numberCategoryDatatable() {
        if (numberCategoryDt !== null) {
            numberCategoryDt.destroy();
        }
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[3, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_numberCategory_grid'),
            "data": {
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "name"},
            {"data": "createAt"},
            {"data": "updateAt"},
            {"data": "actions"}
        ];
        numberCategoryDt = $('#numberCategoryTable').DataTable(datatabeSettings);
    }
    function surveysDatatable() {
        if (surveysDt !== null) {
            surveysDt.destroy();
        }
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[3, "desc"]];
        datatabeSettings["ajax"] = {
            "url": Routing.generate('app_survey_grid'),
            "data": {
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "question"},
            {"data": "createAt"},
            {"data": "actions"}
        ];
        surveysDt = $('#surveysTable').DataTable(datatabeSettings);
    }

    function surveysResponsesDatatable() {
        if (surveysResponsesDt !== null) {
            surveysResponsesDt.destroy();
        }
        datatabeSettings["ordering"] = true;
        datatabeSettings["columnDefs"] = [{
            orderable: false,
            targets: "no-sort"
        }];
        datatabeSettings["order"] = [[3, "desc"]];
        datatabeSettings["ajax"] = {
            "url": $('#surveysResponsesTable').data('url'),
            "data": {
            },
            "type": "POST"
        };
        datatabeSettings["columns"] = [
            {"data": "id"},
            {"data": "first_name"},
            {"data": "last_name"},
            {"data": "question"},
            {"data": "response"},
            {"data": "created_at"},
            {"data": "response_at"}
        ];
        surveysResponsesDt = $('#surveysResponsesTable').DataTable(datatabeSettings);
    }

    function articles() {
        if ($('#articles').length > 0) {
            if ($('#articles').attr('data-break') == '1') {
                return false;
            }
            if ($('#articles').attr('data-loading') == '1') {
                return false;
            }
            $('.loader').removeClass('hide');

            var type = null;
            $('#article_filter_type').find('option').each(function () {
                if ($(this).is(':selected')) {
                    type = $(this).val();
                }
            });
            var enabled = null;
            $('#article_filter_enabled').find('option').each(function () {
                if ($(this).is(':selected')) {
                    enabled = $(this).val();
                }
            });

            var page = parseInt($('#articles').attr('data-page'));
            $('#articles').attr('data-loading', '1');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_article_grid'),
                data: {
                    page: page,
                    dateBefore: $('#article_filter_dateBefore').val(),
                    dateAfter: $('#article_filter_dateAfter').val(),
                    title: $('#article_filter_title').val(),
                    type: type,
                    enabled: enabled,
                },
                dataType: "json",
                success: function (datas) {
                    $('#articles').append(datas.content);
                    if (datas.count > 0) {
                        $('#articles').attr('data-page', page + 1);
                    } else {
                        $('#articles').attr('data-break', '1');
                    }
                    $('.loader').addClass('hide');

                    $('#articles').attr('data-loading', '0');
                }
            });
        }
    }

    function projects() {
        if ($('#projects').length > 0) {
            if ($('#projects').attr('data-break') == '1') {
                return false;
            }
            if ($('#projects').attr('data-loading') == '1') {
                return false;
            }
            $('.loader').removeClass('hide');


            var enabled = null;
            $('#project_filter_enabled').find('option').each(function () {
                if ($(this).is(':selected')) {
                    enabled = $(this).val();
                }
            });


            var page = parseInt($('#projects').attr('data-page'));
            $('#projects').attr('data-loading', '1');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_project_grid'),
                data: {
                    page: page,
                    title: $('#project_filter_title').val(),
                    enabled: enabled,
                    dateBefore: $('#project_filter_dateBefore').val(),
                    dateAfter: $('#project_filter_dateAfter').val(),
                },
                dataType: "json",
                success: function (datas) {
                    $('#projects').append(datas.content);
                    if (datas.count > 0) {
                        $('#projects').attr('data-page', page + 1);
                    } else {
                        $('#projects').attr('data-break', '1');
                    }
                    $('.loader').addClass('hide');

                    $('#projects').attr('data-loading', '0');
                }
            });
        }
    }
    function events() {
        if ($('#events').length > 0) {
            if ($('#events').attr('data-break') == '1') {
                return false;
            }
            if ($('#events').attr('data-loading') == '1') {
                return false;
            }
            $('.loader').removeClass('hide');
            var type = null;
            $('#event_filter_type').find('option').each(function () {
                if ($(this).is(':selected')) {
                    type = $(this).val();
                }
            });
            var enabled = null;
            $('#event_filter_enabled').find('option').each(function () {
                if ($(this).is(':selected')) {
                    enabled = $(this).val();
                }
            });
            var moderate = null;
            $('#event_filter_moderate').find('option').each(function () {
                if ($(this).is(':selected')) {
                    moderate = $(this).val();
                }
            });
            var wait = $('#event_filter_wait').is(':checked');


            var page = parseInt($('#events').attr('data-page'));
            $('#events').attr('data-loading', '1');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_event_grid'),
                data: {
                    page: page,
                    type: type,
                    title: $('#event_filter_title').val(),
                    enabled: enabled,
                    moderate: moderate,
                    wait: wait,
                    dateBefore: $('#event_filter_dateBefore').val(),
                    dateAfter: $('#event_filter_dateAfter').val(),
                    startAt: $('#event_filter_startAt').val(),
                    endAt: $('#event_filter_endAt').val(),
                },
                dataType: "json",
                success: function (datas) {
                    $('#events').append(datas.content);
                    if (datas.count > 0) {
                        $('#events').attr('data-page', page + 1);
                    } else {
                        $('#events').attr('data-break', '1');
                    }
                    $('.loader').addClass('hide');

                    $('#events').attr('data-loading', '0');
                }
            });
        }
    }
    function goodPlans() {
        if ($('#goodPlans').length > 0) {
            if ($('#goodPlans').attr('data-break') == '1') {
                return false;
            }
            if ($('#goodPlans').attr('data-loading') == '1') {
                return false;
            }
            $('.loader').removeClass('hide');
            var enabled = null;
            $('#good_plan_filter_enabled').find('option').each(function () {
                if ($(this).is(':selected')) {
                    enabled = $(this).val();
                }
            });
            var moderate = null;
            $('#good_plan_filter_moderate').find('option').each(function () {
                if ($(this).is(':selected')) {
                    moderate = $(this).val();
                }
            });
            var wait = $('#good_plan_filter_wait').is(':checked');

            var page = parseInt($('#goodPlans').attr('data-page'));
            $('#goodPlans').attr('data-loading', '1');
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_goodplan_grid'),
                data: {
                    page: page,
                    title: $('#good_plan_filter_title').val(),
                    enabled: enabled,
                    moderate: moderate,
                    wait: wait,
                    dateBefore: $('#good_plan_filter_dateBefore').val(),
                    dateAfter: $('#good_plan_filter_dateAfter').val(),
                    startAt: $('#good_plan_filter_startAt').val(),
                    endAt: $('#good_plan_filter_endAt').val(),
                },
                dataType: "json",
                success: function (datas) {
                    $('#goodPlans').append(datas.content);
                    if (datas.count > 0) {
                        $('#goodPlans').attr('data-page', page + 1);
                    } else {
                        $('#goodPlans').attr('data-break', '1');
                    }
                    $('.loader').addClass('hide');

                    $('#goodPlans').attr('data-loading', '0');
                }
            });
        }
    }
    function comments() {
        if ($('#comments').length > 0) {
            if ($('#comments').attr('data-break') == '1') {
                return false;
            }
            if ($('#comments').attr('data-loading') == '1') {
                return false;
            }
            $('.loader').removeClass('hide');
            var page = parseInt($('#comments').attr('data-page'));
            $('#comments').attr('data-loading', '1');


            var type = null;
            $('#comment_filter_type').find('option').each(function () {
                if ($(this).is(':selected')) {
                    type = $(this).val();
                }
            });
            var role = null;
            $('#comment_filter_role').find('option').each(function () {
                if ($(this).is(':selected')) {
                    role = $(this).val();
                }
            });

            var event = null;
            $('#comment_filter_event').find('option').each(function () {
                if ($(this).is(':selected')) {
                    event = $(this).val();
                }
            });
            var article = null;
            $('#comment_filter_article').find('option').each(function () {
                if ($(this).is(':selected')) {
                    article = $(this).val();
                }
            });
            var association = null;
            $('#comment_filter_event').find('option').each(function () {
                if ($(this).is(':selected')) {
                    association = $(this).val();
                }
            });
            var merchant = null;
            $('#comment_filter_event').find('option').each(function () {
                if ($(this).is(':selected')) {
                    merchant = $(this).val();
                }
            });
            $.ajax({
                type: 'POST',
                url: Routing.generate('app_comment_grid'),
                data: {
                    page: page,
                    type: type,
                    search: $('#comment_filter_search').val(),
                    role: role,
                    event: event,
                    article: article,
                    association: association,
                    merchant: merchant,
                },
                dataType: "json",
                success: function (datas) {
                    $('#comments').append(datas.content);
                    if (datas.count > 0) {
                        $('#comments').attr('data-page', page + 1);
                    } else {
                        $('#comments').attr('data-break', '1');
                    }
                    $('.loader').addClass('hide');

                    $('#comments').attr('data-loading', '0');

                    readComments();
                }
            });
        }
    }

    function readComments() {
        $('#comments > div').each(function () {
            var element = $(this);
            var id = element.attr('data-id');
            var read = element.attr('data-read');
            if (id && read != '1') {
                $.ajax({
                    type: 'POST',
                    url: Routing.generate('app_comment_read', {'id': id}),
                    data: {},
                    dataType: "json",
                    success: function (datas) {
                        element.attr('data-read', '1');
                    }
                });
            }
        });
    }

    // Auto complete select multiple only for page add & edit event
    // Auto complete select multiple only for page add & edit event
    if ($("#event_save").length > 0) {
        $('#event_categories').select2
        ({
            placeholder: "Choisir des thèmes"
        });
        /*$('#event_city').select2//
        ({
            placeholder: "Choisir une ville"
        });*/
    }

    if ($("#article_save").length > 0) {
        $('select[data-select="true"]').select2
        ({
            placeholder: "Choisir des thèmes"
        });
    }

    if ($("#article_heading_save").length > 0) {
        $('select[data-select="true"]').select2
        ({
            placeholder: "Choisir des thèmes"
        });
    }

    if ($("#project_save").length > 0) {
        $('select[data-select="true"]').select2
        ({
            placeholder: "Choisir des thèmes"
        });
    }
    if ($("#good_plan_save").length > 0) {
        $('select[data-select="true"]').select2
        ({
            placeholder: "Choisir des thèmes"
        });
    }

    $("#CityAutoCompleteInput").autocomplete({
        source: function (req, response) {
            $.ajax({
                type: 'POST',
                url: app_city_selectElementsRoute,
                dataType: 'json',
                data: {
                    search: $('#CityAutoCompleteInput').val()
                },
                success: function (data) {
                    var jsonArray = data;
                    response($.map(jsonArray, function (objet) {
                        return objet;
                    }));
                }
            });
        },
        select: function (event, ui) {
            $("#CityAutoCompleteInput").val(ui.item.value);
            $("input[data-select-city='true']").val(ui.item.id);
        },
        focus: function (event, ui) {
            $("#CityAutoCompleteInput").val(ui.item.value);
            $("input[data-select-city='true']").val(ui.item.id);
        },
        minLength: 1
    });
    $("#CityAutoCompleteInput").on('change',function(){
        if($(this).val()==""){
            $("input[data-select-city='true']").val("");
        }
        if($("input[data-select-city='true']").val()==""){
            $(this).val("");
        }
    });


    $("#CityAutoCompleteSuAdminInput").autocomplete({
        source: function (req, response) {
            $.ajax({
                type: 'POST',
                url: app_city_selectElementsRoute,
                dataType: 'json',
                data: {
                    search: $('#CityAutoCompleteSuAdminInput').val()
                },
                success: function (data) {
                    var jsonArray = data;
                    response($.map(jsonArray, function (objet) {
                        return objet;
                    }));
                }
            });
        },
        select: function (event, ui) {
            $("#CityAutoCompleteSuAdminInput").val(ui.item.value);
            $("input[data-CityAutoCompleteSuAdminInput-select-city='true']").val(ui.item.id);
        },
        focus: function (event, ui) {
            $("#CityAutoCompleteSuAdminInput").val(ui.item.value);
            $("input[data-CityAutoCompleteSuAdminInput-select-city='true']").val(ui.item.id);
        },
        minLength: 1
    });
    $("#CityAutoCompleteSuAdminInput").on('change',function(){
        if($(this).val()==""){
            $("input[data-CityAutoCompleteSuAdminInput-select-city='true']").val("");
        }
        if($("input[data-CityAutoCompleteSuAdminInput-select-city='true']").val()==""){
            $(this).val("");
        }
    });

    if($('#article_heading_admins').length > 0) {
        $('#article_heading_admins').select2
        ({
            placeholder: "Choisir des Administrateurs"
        });
    }






    if ($("#add_admin_community_access_user").length > 0) {
        $("#add_admin_community_access_user").select2
        ({
            placeholder: "Choisir un citoyen"
        });
    }

    if ($("#add_su_admin_community_user").length > 0) {
        $("#add_su_admin_community_user").select2
        ({
            placeholder: "Choisir un citoyen"
        });
    }


    $(".js-example-tags").select2({
        tags: true,
        tokenSeparators: [";"],
        createTag: function (tag) {
            return {
                id: tag.term,
                text: tag.term,
                // add indicator:
                isNew : true
            };
        }
    }).on("select2:select", function(e) {
        if(e.params.data.isNew){

            // append the new option element prenamently:
            $(this).find('[value="'+e.params.data.id+'"]').replaceWith('<option selected value="'+e.params.data.id+'">'+e.params.data.text+'</option>');
            // store the new tag:
            $.ajax({
                url: Routing.generate('app_ajax_post_category'),
                type: "post",
                data:  {'item':e.params.data.id},
                success: function (response) {

                    // you will get response from your php page (what you echo or print)
                    $(".js-example-tags").find('[value="'+e.params.data.id+'"]').replaceWith('<option selected value="'+response.id+'">'+response.title+'</option>');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });

            console.log('<code>New tag: {"' + e.params.data.id + '":"' + e.params.data.id + '"}</code><br>');
        }
    });


    var $collectionHolder1;



// setup an "add a tag" link
    var $addTagLink1 = $('<a href="#" class="add_tag_link"><span class="fa fa-plus"></span></a>');
    var $newLinkLi1 = $('<li></li>').append($addTagLink1);

    jQuery(document).ready(function() {
        // Get the ul that holds the collection of tags
        $collectionHolder1 = $('ul.tags');
        $collectionHolder1.find('li').each(function() {
            addTagFormDeleteLink($(this));
        });

        // add the "add a tag" anchor and li to the tags ul
        $collectionHolder1.append($newLinkLi1);

        // count the current form inputs we have (e.g. 2), use that as the new
        // index when inserting a new item (e.g. 2)
        $collectionHolder1.data('index', $collectionHolder1.find(':input').length);

        $addTagLink1.on('click', function(e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            // add a new tag form (see next code block)
            addTagForm($collectionHolder1, $newLinkLi1);
        });
    });
    function addTagForm($collectionHolder1, $newLinkLi1) {
        // Get the data-prototype explained earlier
        var prototype = $collectionHolder1.data('prototype');

        // get the new index
        var index = $collectionHolder1.data('index');

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        var newForm = prototype.replace(/__name__/g, index);

        // increase the index with one for the next item
        $collectionHolder1.data('index', index + 1);

        // Display the form in the page in an li, before the "Add a tag" link li
        var $newFormLi = $('<li></li>').append(newForm);
        $newLinkLi1.before($newFormLi);

        addTagFormDeleteLink($newFormLi);
    }

    function addTagFormDeleteLink($tagFormLi) {
        var $removeFormA = $('<a href="#" class="btn btn-xs btn-circle btn-danger"><span class="fa fa-trash"></span></a>');
        $tagFormLi.append($removeFormA);

        $removeFormA.on('click', function(e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            // remove the li for the tag form
            $tagFormLi.remove();
        });
    }


    var $collectionHolder;

// setup an "add a tag" link
    var $addImageLink = $('<a href="#" class="add_image_link" id="add_img">Ajouter photo</a>');
    var $newLinkLi = $('<li></li>').append($addImageLink);
    var i = $(".edit-form-imgs input").length;

    jQuery(document).ready(function () {
        // Get the ul that holds the collection of tags
        $collectionHolder = $('ul.images');

        // add the "add a tag" anchor and li to the tags ul
        if (i <= 2) {
            $collectionHolder.append($newLinkLi);
        }

        // count the current form inputs we have (e.g. 2), use that as the new
        // index when inserting a new item (e.g. 2)
        $collectionHolder.data('index', $collectionHolder.find(':input').length);

        $(document).on('click', '#add_img', function (e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            // add a new tag form (see next code block)
            addImageForm($collectionHolder, $newLinkLi);
            i++;
        });


    });

    function addImageForm($collectionHolder, $newLinkLi) {
        // Get the data-prototype explained earlier
        var prototype = $collectionHolder.data('prototype');

        // get the new index
        var index = $collectionHolder.data('index');

        var newForm = prototype;
        // You need this only if you didn't set 'label' => false in your tags field in TaskType
        // Replace '__name__label__' in the prototype's HTML to
        // instead be a number based on how many items we have
        // newForm = newForm.replace(/__name__label__/g, index);

        // Replace '__name__' in the prototype's HTML to
        // instead be a number based on how many items we have
        newForm = newForm.replace(/__name__/g, index);

        // increase the index with one for the next item
        $collectionHolder.data('index', index + 1);

        // Display the form in the page in an li, before the "Add a tag" link li
        var $newFormLi = $('<li></li>').append(newForm);
        $newLinkLi.before($newFormLi);
        addImageFormDeleteLink($newFormLi, $collectionHolder, $newLinkLi);
        if (i >= 2) {
            $newLinkLi.remove();
        }
    }

    function addImageFormDeleteLink($tagFormLi, $collectionHolder, $newLinkLi) {
        var $removeFormA = $('<a href="#">supprimer Cette image</a>');
        $tagFormLi.append($removeFormA);

        $removeFormA.on('click', function (e) {
            // prevent the link from creating a "#" on the URL
            e.preventDefault();

            // remove the li for the tag form
            $tagFormLi.remove();
            $collectionHolder.append($newLinkLi);

            i--;
        });


    }
    $(document).on('click', '#remove-img', function (e) {
        $(this).parent().remove();
        $collectionHolder = $('ul.images');

        if ($('.add_image_link').length == 0) {
            $collectionHolder.append($newLinkLi);
        }
        i--
    });

    $(function () {
        setInterval(function () {
            $(".slideshow ul").animate({marginLeft: -350}, 800, function () {
                $(this).css({marginLeft: 0}).find("li:last").after($(this).find("li:first"));
            })
        }, 3500);
    });
    /* -------------------------------------FUNCTIONS END------------------------------------ */
});


