jQuery(document).ready(function() {
    bindDeleteCategory();
    bindAddCategoryForm();
});

function bindDeleteCategory() {
    $(document).on('click', '.delete-category', function() {
        $(this).closest('.category').remove();
    });
}

function bindAddCategoryForm() {
    var $collectionHolder = $('#appbundle_community_categories');
    $collectionHolder.data('index', $collectionHolder.find('input').length);
    $(document).on('click', '.add-category', function () {
        var prototype = $collectionHolder.data('prototype');
        var index = $collectionHolder.data('index');
        prototype = prototype.replace(/__name__/g, index);
        var newForm = $(prototype);
        $collectionHolder.data('index', index + 1);
        $collectionHolder.append(newForm);
    });
}