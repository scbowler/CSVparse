$(document).ready(function(){

    $("form").on("click", "input[type=radio]", function(){
        toggleHidden(this);
    });
    $("#auto-pop").on("click", function(){
        autoPop();
    });
    $("#txtFileUpload").on("change", fileUp);
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
}

function toggleHidden(ele){

    var eleID = $(ele).attr("id");

    if($("." + eleID).hasClass("hide")){
        $(".proto").toggleClass("hide");
        $(".rta").toggleClass("hide");
    }
}

function autoPop(){

    var data = {
        action: 'autoPop',
        csvFile: $("#csv").val()
    }

    //console.log(data);

    $.ajax({
       url: "actions/parse.php",
       method: "post",
       data: data,
       dataType: "json",
       cache: false,
       success: function(res){
           console.log(res);
           $("#maxProto").val(res.high);
       }
    });
}