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
    },
    "containsSubstring": function(element, index, match, array) {
        var input = match[3];
        var text = $(element).text();
        
        return (text.toLowerCase().indexOf(input.toLowerCase()) >= 0);
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
        //$("table tbody tr td:containsAny('" + val.trim() + "')").parents('tr').show();
        $("table tbody tr td:containsSubstring('" + val.trim() + "')").parents('tr').show();
    } else {
        $("table tbody tr").show();
    }
}

window.addEventListener('load', function(){
	// Everything has loaded!
  	console.log('Everything has loaded! Start SHEN filter');
    var fName = $("#filterName").val().trim();
    var fQty = $("#filterQty").val().trim();

    if(fName.length) {
        //auto filter Name after submit
        //filterName(fName);
    }
    if(fQty.length) {
        //auto filter Qty after submit
        //filterQuantityLessThan(fQty, 2);
    }

    $("#filterName").keyup(function(e){
        if (e.which == 13) {
          var inputVal = $("#filterName").val();
          filterName(inputVal);
          return false;    //<---- this line is the same as calling e.preventDefault and e.stopPropagation()
        }
    });

    $("#filterQty").keyup(function(e){
        if (e.which == 13) {
          var inputVal = $("#filterQty").val();
          filterQuantityLessThan(inputVal, 2);
          return false;    //<---- this line is the same as calling e.preventDefault and e.stopPropagation()
        }
    });
})