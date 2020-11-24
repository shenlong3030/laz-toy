function copyToClipBoard(text) {
    var $temp = $("<textarea>");
    $("body").append($temp);
    $temp.val(text).select();
    document.execCommand("copy");
    $temp.remove();
}

function properText(text) {
	return str = text.replace(/(^\w{1})|(\s{1}\w{1})/g, match => match.toUpperCase());    
}