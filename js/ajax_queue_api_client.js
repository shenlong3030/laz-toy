// HTML page need responseIframe to display API repsonse
//
// <iframe id="responseIframe" name="responseIframe" width="1000" height="1000"></iframe>
// 

function getAjaxManager() {
    var requests = [];
    return {
        addReq: function(opt) {
            requests.push(opt);
        },
        removeReq: function(opt) {
            if ($.inArray(opt, requests) > -1)
                requests.splice($.inArray(opt, requests), 1);
        },
        run: function() {
            var self = this,
                oriSuc;
            if (requests.length) {
                oriSuc = requests[0].complete;
                requests[0].complete = function() {
                    if (typeof(oriSuc) === 'function') oriSuc();
                    requests.shift();
                    self.run.apply(self, []);
                };
                $.ajax(requests[0]);
            } else {
                self.tid = setTimeout(function() {
                    self.run.apply(self, []);
                }, 1000);
            }
        },
        stop: function() {
            requests = [];
            clearTimeout(this.tid);
        }
    };
}

function getRandomInt(min, max) {
    return parseInt(Math.random() * (max - min) + min); // The maximum is exclusive and the minimum is inclusive
}

function getRandomColor() {
    var r = getRandomInt(150, 255);
    var g = getRandomInt(150, 255);
    var b = getRandomInt(150, 255);
    return "#" + r.toString(16) + g.toString(16) + b.toString(16);
}

function productCreateWithAjaxQueue(params) {
    // send response to this iframe
    console.log(params);
    var myFrame = $("#responseIframe").contents().find('body');
    var d = new Date();
    var ntime = d.toLocaleTimeString();

    params.REQUEST_METHOD = 'PUT';  // simulate REQUEST PUT
    ajaxManager.addReq({
        type: 'POST',
        url: 'api_create_delete.php',
        data: params,
        success: function(data) {
            if(data.trim().length) {
                console.log(data);
                var res = JSON.parse(data); // data is string, convert to obj
                const randomColor = Math.floor(Math.random() * 16777215).toString(16);
                const htmlColor = "#" + randomColor;
                //myFrame.append('<pre style="background-color:' + getRandomColor() + '">' + ntime + ";" + JSON.stringify(res["mymessage"], null, 3).replaceAll('"', '') + '</pre>');
                myFrame.append('<pre>' + ntime + ";" + JSON.stringify(res["mymessage"], null, 3).replaceAll('"', '') + '</pre>');
            } else {
                console.log("return empty json");
            }
        },
        error: function(error) {
            myFrame.append(ntime + ' ERROR ' + JSON.stringify(params) + '<br>');
        }
    });
}

function productUpdateWithAjaxQueue(params) {
    // send response to this iframe
    console.log(params);
    var myFrame = $("#responseIframe").contents().find('body');
    var d = new Date();
    var ntime = d.toLocaleTimeString();

    ajaxManager.addReq({
        type: 'POST',
        url: 'api_update.php',
        data: params,
        success: function(data) {
            if(data.trim().length) {
                console.log(data);
                var res = JSON.parse(data); // data is string, convert to obj

                const randomColor = Math.floor(Math.random() * 16777215).toString(16);
                const htmlColor = "#" + randomColor;
                //myFrame.prepend('<pre style="background-color:' + getRandomColor() + '">' + ntime + ";" + JSON.stringify(res["mymessage"], null, 3) + '</pre>');
                myFrame.prepend('<pre>' + ntime + ";" + JSON.stringify(res["mymessage"], null, 3) + '</pre>');
            } else {
                console.log("return empty json");
            }
        },
        error: function(error) {
            myFrame.prepend(ntime + JSON.stringify(error, 0, 2) + '<br>');
        }
    });
}

function productDeleteWithAjaxQueue(params) {
    // send response to this iframe
    console.log(params);
    var myFrame = $("#responseIframe").contents().find('body');
    var d = new Date();
    var ntime = d.toLocaleTimeString();

    params.REQUEST_METHOD = 'DELETE';  // simulate REQUEST DELETE
    ajaxManager.addReq({
        type: 'POST',
        url: 'api_create_delete.php',
        data: params,
        success: function(data) {
            if(data.trim().length) {
                console.log(data);
                var res = JSON.parse(data); // data is string, convert to obj

                const randomColor = Math.floor(Math.random() * 16777215).toString(16);
                const htmlColor = "#" + randomColor;
                //myFrame.append('<pre style="background-color:' + getRandomColor() + '">' + ntime + ";" + JSON.stringify(res["mymessage"], null, 3) + '</pre>');
                myFrame.append('<pre>' + ntime + ";" + JSON.stringify(res["mymessage"], null, 3) + '</pre>');
            } else {
                console.log("return empty json");
            }
        },
        error: function(error) {
            myFrame.append(ntime + JSON.stringify(error, 0, 2) + '<br>');
        }
    });
}