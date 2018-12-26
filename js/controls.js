$.extend($.expr[":"], {
    "containsAny": function(element, index, match, array) {
        var inputWords = match[3].split(/\s+/);
        var textWords = $(element).text().split(/\s+/);
        var elementValue = $(element).text();

        textWords = textWords.map(function(val){
            return val.toLowerCase();
        });
        //console.log('', inputWords, textWords)
        var isSubset = inputWords.every(function(val) { 
          return elementValue.toLowerCase().indexOf(val.toLowerCase()) >= 0;
        });
        return isSubset;
    }
});

function filterQuantityLessThan(val, colIndex) {
    console.log("run filter quantity with : ", val);
    
    // Declare variable
    var myTable = $("table:first");
    var listTR = myTable.find("tbody tr");
    // hide all TR
    listTR.hide();
    
    // find and show TR that have quantity < val
    listTR.filter(function (i, v) {
        var cellValue = $(this).find('td:eq(' + colIndex + ')').text();
        if(parseInt(cellValue) <= parseInt(val)) {
          	return true;
        }
        return false;
    }).show();
}

function filterName(val) {
    console.log("run filter name with : ", val);
    if(val.trim()) {
        // hide all TR
        $("table tbody tr").hide();
        
        // show TR match value
        $("table tbody tr td:containsAny('" + val.trim() + "')").parents('tr').show();
    } else {
        $("table tbody tr").show();
    }
}

window.addEventListener('load', function(){
	// Everything has loaded!
  	console.log('Everything has loaded!');
    var fName = $("#filterName").val().trim();
    var fQty = $("#filterQty").val().trim();

    if(fName.length) {
        filterName(fName);
    }
    if(fQty.length) {
        filterQuantityLessThan(fQty, 2);
    }

    $("#filterName").keyup(function(){
        var inputVal = $("#filterName").val();
        console.log('name changed : ', inputVal);

        // return if empty imput
        if(!inputVal){
            return;
        }

        // return if inputVal not ending with space
        if(!(/\s+$/.test(inputVal))){
            return;
        }

        filterName(inputVal);
    });

    $("#filterQty").keyup(function(){
        var inputVal = $("#filterQty").val();
        console.log('quantity changed : ', inputVal);

        // return if empty imput
        if(!inputVal){
            return;
        }

        filterQuantityLessThan(inputVal, 2);
    });
})