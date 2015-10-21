$(document).ready(function(){

    $("form").on("click", "input[type=radio]", function(){
        toggleHidden(this);
    });

    $("#auto-pop").on("click", function(){
        updateInfo("mostProto");
    });

    $("#txtFileUpload").on("change", fileUp);

    $("#populate").on("click", function(){
        updateInfo("popStudents");
    });
});

function fileUp(evt){
    var file = evt.target.files[0];
    var reader = new FileReader();
    var csvData = "";

    reader.readAsText(file);
    reader.onload = function(e){
        csvData = e.target.result;
        $("#csv").val(csvData);
    }

    var selected = $("input[type=radio]:checked").attr("id");

    console.log("Selected on file load", selected);

    if(selected == "populate"){
        updateInfo("popStudents");
    }
}

function toggleHidden(ele){

    var eleID = $(ele).attr("id");

    console.log("Element ID", eleID);

    $(".show").removeClass("show");

    $("." + eleID).addClass("show");

    if(eleID == "populate"){
        $(".proto").addClass("show");
    }

}

function updateInfo(action){

    var data = {
        action: action,
        csvFile: $("#csv").val()
    }

    //console.log(data);

    $.ajax({
       url: "actions/parse.php",
       method: "post",
       data: data,
       dataType: "json",
       cache: false,
       success: function(res) {
           console.log(res);

           switch (action) {
               case "mostProto":
                   $("#maxProto").val(res.high);
                   break;
               case "popStudents":
                   console.log("Populate Students case");
                   if(res.success) {
                       popStuList(res.students);
                   }
                   break;
               default:
                   console.log("Unknown action");
                   break;
           }
       }
    });
}

function popStuList(stuArr){
    console.log("Thanks");
    var len = stuArr.length;

    var sel = $("<select>", {
        class: "students show"
    });

    for(var i=0; i<len; i++){
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