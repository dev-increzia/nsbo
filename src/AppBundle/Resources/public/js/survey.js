jQuery(document).ready(function() {
    bindDeleteChoice();
    //bindDeleteQuestion();
    bindAddQuestionForm();
    bindAllChoiceForm();
});

function bindDeleteChoice() {
    $(document).on('click', '.delete-choice', function() {
        $(this).closest('.choice').remove();
    });
}

function bindDeleteQuestion() {
    $(document).on('click', '.delete-question', function() {
        $(this).closest('.question').remove();
    });
}

function bindAddQuestionForm() {
    var $collectionHolder = $('#appbundle_survey_questions');
    $collectionHolder.data('index', $collectionHolder.find('input').length);
    $(document).on('click', '.add-question', function () {
        var prototype = $collectionHolder.data('prototype');
        var index = $collectionHolder.data('index');
        prototype = prototype.replace(/__name__/g, index);
        var newForm = $(prototype);
        $collectionHolder.data('index', index + 1);
        $collectionHolder.append(newForm);
        bindChoiceForm(newForm.find('.question'));

        $('#add-question').hide();
    });
}

function bindAllChoiceForm() {
    $('.question').each(function() {
        bindChoiceForm($(this));
    });
}

function bindChoiceForm($questionElement) {
    var $collectionHolder = $questionElement.find('.choices');
    $collectionHolder.data('index', $collectionHolder.find('input').length);

    let i = 1;
    $collectionHolder.find('input').each(function() {
        let element = $(this).closest('.choice').html();
        console.log(element);
        element = element.replace(/__choice__/g, i);
        $(this).closest('.choice').html(element);
        i++;
    });

    $questionElement.find('.add-choice').on('click', function () {
        var prototype = $collectionHolder.data('prototype');
        var index = parseInt($collectionHolder.data('index'));
        prototype = prototype.replace(/__name_choice__/g, index);
        prototype = prototype.replace(/__choice__/g, index + 1);
        var newForm = $(prototype);
        $collectionHolder.data('index', index + 1);
        $collectionHolder.append(newForm);
    });
}