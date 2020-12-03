$(document).on('click', '.typeArticle', function () {
    showEventFields();
});

showEventFields();

function showEventFields() {
    let eventCityContainer = $('#event_toCity').closest('.form-group');
    let eventArticleContainer = $('#event_article').closest('.form-group');

    if ($('#event_type_0').is(':checked')) {
        eventCityContainer.hide();
        eventArticleContainer.hide();
    } else {
        eventCityContainer.show();
        eventArticleContainer.show();
    }
}