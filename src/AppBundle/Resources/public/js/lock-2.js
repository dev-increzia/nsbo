var Lock = function () {

    return {
        //main function to initiate the module
        init: function () {

            $.backstretch([
                $("#lock").attr('data-bg1'),
                $("#lock").attr('data-bg2'),
                $("#lock").attr('data-bg3'),
                $("#lock").attr('data-bg4')
            ], {
                fade: 1000,
                duration: 8000
            });
        }

    };

}();

jQuery(document).ready(function () {
    if (('#lock').length > 0) {
        Lock.init();
    }
});