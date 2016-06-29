$(document).ready(function () {

    var currentCheck = $("input[type='radio']:checked").val();
    if (currentCheck == 'report') {
        updateInfo("popStudents");
    }

    $("form").on("click", "input[type=radio]", function () {
        toggleHidden(this);
    });

    $("#auto-pop").on("click", function () {
        updateInfo("mostProto");
    });

    $("#txtFileUpload").on("change", fileUp);

    $("#populate").on("click", function () {
        updateInfo("popStudents");
    });

    if($('#csv').val() != ''){
        console.log('This got called');
        updateInfo('class list');
    }
});

function fileUp(evt) {
    var file = evt.target.files[0];
    var reader = new FileReader();
    var csvData = "";

    reader.readAsText(file);
    reader.onload = function (e) {
        csvData = e.target.result;
        $("#csv").val(csvData);

        updateInfo("class list");
    };

    //console.log("Selected on file load", selected);
}

function toggleHidden(ele) {

    var eleID = $(ele).attr("id");

    //console.log("Element ID", eleID);

    var options = $('#options-div');
    if (eleID == "error") {
        options.hide();
    } else {
        options.show();
    }

    $(".show").removeClass("show");

    $("." + eleID).addClass("show");

    if (eleID == "populate") {
        $(".proto").addClass("show");
    }
}

function updateInfo(action) {

    var data = {
        action: action,
        roster: $('#roster option:selected').val(),
        csvFile: $("#csv").val()
    };

    console.log(data);

    $.ajax({
        url: "actions/parse.php",
        method: "post",
        data: data,
        dataType: "json",
        cache: false,
        success: function (res) {

            switch (action) {
                case "mostProto":
                    $("#maxProto").val(res.count);
                    break;
                case "popStudents":
                    console.log("Populate Students case");
                    if (res.success) {
                        popStuList(res.students);
                        $('#btn').attr('disabled', false);
                    }
                    break;
                case "class list":
                    console.log("Class list case", res);
                    if (res.success){
                        showOptions(res['class list']);
                    }
                    break;
                default:
                    console.log("Unknown action");
                    break;
            }
        },
        error: function (err) {
            console.warn('Ajax call failed with: ', err);
        }
    });
}

function popStuList(stuArr) {
    console.log("Thanks");
    var len = stuArr.length;

    var label = $("<label>", {
        for: "students",
        text: "Select Student"
    });

    var sel = $("<select>", {
        class: "students show",
        name: "students",
        id: "students"
    });

    for (var i = 0; i < len; i++) {
        var stu = stuArr[i];

        var opt = $("<option>", {
            class: "stuOpt",
            value: stu,
            text: stu
        });
        opt.appendTo(sel);
    }

    $(".contain").append(sel);
}

function showOptions(list){
    console.log('showOptions function', list);

    var listElem = $('#roster');
    for(var v in list){
        var option = $('<option>', {
            class: 'classOpt',
            value: v,
            text: v
        });

        option.appendTo(listElem);
    }

    $('.radio-contain').removeClass('hide');
    $('.btn-contain').removeClass('hide');

}