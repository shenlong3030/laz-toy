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
    var myFrame = $("#responseIframe").contents().find('body');
    var d = new Date();
    var ntime = d.toLocaleTimeString();
    ajaxManager.addReq({
        type: 'POST',
        url: 'api_create.php',
        data: params,
        success: function(data) {
            sconsole.log(data);
            var res = JSON.parse(data); // data is string, convert to obj
            const randomColor = Math.floor(Math.random() * 16777215).toString(16);
            if (parseInt(res.code)) {
                myFrame.prepend('<pre style="background-color:' + getRandomColor() + '">' + ntime + " " + data + ">>>" + JSON.stringify(params) + '</pre>');
            } else {
                var outputLog = params["action"].includes("mass") ? // 
                    JSON.stringify(res["data"], null, 3) :          // if mass update: display response 
                    JSON.stringify(params);                         // else: display params
                myFrame.prepend('<pre style="background-color:' + getRandomColor() + '">' + ntime + ' SUCCESS ' + outputLog + '</pre>');
            }
        },
        error: function(error) {
            myFrame.prepend(ntime + ' FAILED ' + JSON.stringify(params) + '<br>');
        }
    });
}

function productUpdateWithAjaxQueue(params) {
    // send response to this iframe
    var myFrame = $("#responseIframe").contents().find('body');
    var d = new Date();
    var ntime = d.toLocaleTimeString();
    ajaxManager.addReq({
        type: 'POST',
        url: 'update-api.php',
        data: params,
        success: function(data) {
            console.log(data);
            var res = JSON.parse(data); // data is string, convert to obj

            const randomColor = Math.floor(Math.random() * 16777215).toString(16);
            const htmlColor = "#" + randomColor;
            myFrame.prepend('<pre style="background-color:' + getRandomColor() + '">' + ntime + "; " + JSON.stringify(res["message"], null, 3) + '</pre>');
        },
        error: function(error) {
            myFrame.prepend(ntime + ' FAILED ' + JSON.stringify(params) + '<br>');
        }
    });
}