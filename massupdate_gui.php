<?php
//include_once "src/show_errors.php";
include_once "check_token.php";
require_once('_main_functions.php');
?>

<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MASS UPDATE</title>
    
    <?php include('src/head.php');?>
</head>

<body>
<table>
    <tr>
     <th>SKUs</th>
     <th>Names</th>
     <th>Models</th>
     <th>Colors</th>
     <th>Prices</th>
     <th>Images >> Start index (1-8) <input size="3" type="text" name="imageindex" value="0"></th>
     <th>Actives</th>
    </tr>
    <tbody>
        <tr>
            <td><textarea class="nowrap" id="txt_skus" rows="20" cols="30"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="50"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="10"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="10"></textarea></td>
            <td><textarea class="nowrap" name="txt_prices" rows="20" cols="10"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="50"></textarea></td>
            <td><textarea class="nowrap" name="col[]" rows="20" cols="5"></textarea></td>
        </tr>
    </tbody>
</table>
<br>
<button id="btn_qty500">Qty =500</button><br>
<button id="btn_updatePrice">Sale price = </button><input id="sprice" type="text" name="sprice"><br>
<br>
<hr>

<iframe id="responseIframe" name="responseIframe" width="1000" height="1000"></iframe>

</div>
</body>

<script type="text/javascript">
    //######### AJAX QUEUE ######################################################################################
      var ajaxManager = (function() {
        var requests = [];

         return {
            addReq:  function(opt) {
                requests.push(opt);
            },
            removeReq:  function(opt) {
            if( $.inArray(opt, requests) > -1 )
                requests.splice($.inArray(opt, requests), 1);
            },
            run: function() {
                var self = this,
                    oriSuc;

                if( requests.length ) {
                    oriSuc = requests[0].complete;

                    requests[0].complete = function() {
                         if( typeof(oriSuc) === 'function' ) oriSuc();
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
            stop:  function() {
                requests = [];
                clearTimeout(this.tid);
            }
         };
      }());
      ajaxManager.run(); 

      function randomNumber(min, max) {
        return parseInt(Math.random() * (max - min) + min);
        }

      function productUpdateWithAjaxQueue(params) {
          // send response to this iframe
          var myFrame = $("#responseIframe").contents().find('body'); 

          ajaxManager.addReq({
               type: 'POST',
               url: 'update-api.php',
               data: params,
               success: function(data){
                  var res = JSON.parse(data); // data is string, convert to obj
                  var d = new Date();
                  var n = d.toLocaleTimeString();

                  const htmlColor = "#" + randomNumber(100,255).toString(16) + randomNumber(100,255).toString(16) + randomNumber(100,255).toString(16);

                  // if(parseInt(res.code)) {
                  //   myFrame.prepend('<p style="background-color:' + htmlColor + '">' + n + '&nbsp;' + params['sku'] + '&nbsp;' + data + '</p>'); 
                  // } else {
                  //   myFrame.prepend('<p style="background-color:' + htmlColor + '">' + n + '&nbsp;' + params['sku'] + '&nbsp;'  + 'SUCCESS</p>');
                  // }

                  if(parseInt(res.code)) {
                    myFrame.prepend('<p style="background-color:' + htmlColor + '">' + n + '&nbsp;' + JSON.stringify(res) + '<br>@@@<br>' + JSON.stringify(params) + '</p>'); 
                  } else {
                    myFrame.prepend('<p style="background-color:' + htmlColor + '">' + n + '&nbsp;' + res["message"] + '<br>@@@<br>' + JSON.stringify(params) + '</p>'); 
                  }
               },
               error: function(error){
                  myFrame.prepend(n + ' FAILED<br>'); 
               }
          });
      }
    //##########################################################################################################
    $("#btn_qty500").click(function() {
        var myFrame = $("#responseIframe").contents().find('body'); 
        var d = new Date();
        var n = d.toLocaleTimeString();
        myFrame.prepend('### ' + n + ' QTY=500 ###################################################<hr>');

        var lines = $('#txt_skus').val().replaceAll("#N/A","").split('\n');
        lines = lines.filter(function(e){return e}); //remove empty

        do {
            var set10 = lines.slice(0, 10); // get 10 left
            lines = lines.slice(10);        // renove 10 left 
            var skus = set10.join(',');     // create string sku,sku,sku

            productUpdateWithAjaxQueue({ skus: skus, action: "massQty", qty: 500});
        } while(lines.length);
    });

    $("#btn_updatePrice").click(function() {
        var myFrame = $("#responseIframe").contents().find('body'); 
        var d = new Date();
        var n = d.toLocaleTimeString();
        myFrame.prepend('### ' + n + ' UDPATE PRICES ###################################################<hr>');

        var lines = $('#txt_skus').val().replaceAll("#N/A","").split('\n');
        lines = lines.filter(function(e){return e}); //remove empty
        var sprice = $('#sprice').val();

        do {
            var set10 = lines.slice(0, 10); // get 10 left
            lines = lines.slice(10);        // renove 10 left 
            var skus = set10.join(',');     // create string sku,sku,sku

            productUpdateWithAjaxQueue({ skus: skus, action: "massPrice", sprice: sprice});
        } while(lines.length);
    });

</script>

</html>


