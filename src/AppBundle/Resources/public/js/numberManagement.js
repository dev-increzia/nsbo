//init
var phoneControl = true;
var documentCotrol = true;
//french phone regex
var phoneNumberRegexString = /^(?:(?:\+|00)33[\s.-]{0,3}(?:\(0\)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$/gmi;

//phone input change control
$('#number_phone').on('change', function () {
    var text = $(this).val().trim();
    text = text.replace(/(?:(?:\r\n|\r|\n)\s*){2}/gm, "\n");
    $(this).val(text);
    var arrayOfPhoneNumbers = $(this).val().split('\n');
    var phoneCounter = 0;
    var errorMessage = "";
    $.each(arrayOfPhoneNumbers, function (index, phoneNumber) {
        phoneCounter = index + 1;
        var phoneRegex = new RegExp(phoneNumberRegexString);
        if (phoneRegex.test(phoneNumber)) {
        } else {
            if (phoneNumber != "") {
                errorMessage += "la ligne : " + phoneCounter + " avec la valeur : " + phoneNumber + " n'est pas un numéro valide<br>";
            }
        }
    });
    if (errorMessage != "") {
        phoneControl = false;
        $("#phoneNumberErrorPanel").html("<i>" + errorMessage + "</i>");
    } else {
        $("#phoneNumberErrorPanel").html("");
        phoneControl = true;
    }
    buttonStatus();
});

//phone input keyup control
$('#number_phone').on('keyup', function () {
    var text = $(this).val();
    text = text.replace(/(?:(?:\r\n|\r|\n)\s*){3}/gm, "\n\n");
    $('#number_phone').val(text);
});

//allowed extensions array
var allowedDocumentExtension = ['pdf', 'xls', 'xlsx', 'doc', 'docx', 'ppt', 'pptx'];

//function that valide the file extension
function validFile(fileName) {
    if (fileName != "") {
        var fileExtension = fileName.split('.').pop();
        if (allowedDocumentExtension.includes(fileExtension)) {
            return true;
        } else {
            return false;
        }
    }
    return true;
}

//document input change control
$("#number_document_file_file").on('change', function () {
    var errorMessage = "";
    if (!validFile($("#number_document_file_file").val())) {
        errorMessage += "Type invalide! les fichiers acceptés sont '" + "." + allowedDocumentExtension.join(', .') + "'";
    }

    if (errorMessage != "") {
        documentCotrol = false;
        $("#documentExtensionErrorPanel").html("<i>" + errorMessage + " </i>");
    } else {
        $("#documentExtensionErrorPanel").html("");
        documentCotrol = true;
    }
    buttonStatus();
});

function buttonStatus() {
    if (documentCotrol && phoneControl) {
        $("#number_save").removeAttr("disabled");
    } else {
        $("#number_save").attr("disabled", "disabled");
    }
}