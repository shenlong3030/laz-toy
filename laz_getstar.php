<!DOCTYPE html>
<html leng="en-AU">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GET LAZADA STAR</title>
    <link href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" rel="stylesheet">
    <script src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
    <!-- bxSlider Javascript file -->
    <script src="./js/controls.js"></script>
    <script src="./js/jquery.tablesorter.min.js"></script>
    <script src="./js/helper.js"></script>

    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>    

<table>
    <tbody>
        <tr>
            <td><h1>LAZADA URLs</h1></td>
        </tr>
        <tr>
            <td>
                <textarea id="txt_urls" class="nowrap" name="col[]" rows="20" cols="100"></textarea>
            </td>
        </tr>
        <tr>
            <td>
                <button id="btn_get_lazstar">Get LAZADA star</button>
            </td>
        </tr>
    </tbody>
</table>

<hr>

<table id="output_table" class="main" border="1">
    <thead>
        <tr>
            <th>LAZADA Name</th>
            <th>LAZADA URL</th>
            <th>SAO<button id="copy_sao">Copy</button></th>
            <th>DANH GIA<button id="copy_dg">Copy</button></th>
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>


<script>

    // deferred chain pattern, check sample: http://jsfiddle.net/xkhten36/2/
    function getStarAll(urls) {
        urls.reduce(function(p, item) {
            return p.then(function() {
                return getStar(item);
            });
        }, $.Deferred().resolve()).then(function() {
            // all done here
            $("#btn_get_lazstar").prop("disabled", false);
            console.log("done");
            $("#output_table > tbody").append("<tr><td>##########################</td><td>#####</td><td>#####</td></tr>");
        });
    }

    const sleep = (milliseconds) => {
      return new Promise(resolve => setTimeout(resolve, milliseconds))
    }

    // deferred chain pattern, check sample: http://jsfiddle.net/xkhten36/2/
    function getStar(url) {
        var urls = [url];

        var uri = "<?php echo dirname($_SERVER['REQUEST_URI'])."/laz_getstar_api.php"; ?>";
        var data = {urls : urls};
        var d = $.Deferred();
        $.post(uri, data)
        .done(function(res){
            // display json res
            // todo ...
            console.log(res);
            
            res = $.parseJSON(res);
            var n = res["name"];
            var a = res["url"];
            var b = res["sao"];
            var c = res["dg"];
            var tr = "<tr><td>" + n + "</td><td><a target='_blank' href='" + a + "'>" + a + "</a></td><td class='sao'>" + b + "</td><td class='dg'>" + c + "</td></tr>";
            $("#output_table > tbody").append(tr);

            var rd = Math.floor((Math.random() * 10) + 1);
            sleep(rd*100).then(() => {
              d.resolve(res);
            })
        });

        return d.promise();
    }

    $("#btn_get_lazstar").click(function(){
        $(this).prop("disabled", true);
        str = $('#txt_urls').val().replace(/(?:\r\n|\r|\n)/g, ',');
        var urls = str.split(',');
        getStarAll(urls);
    });
    $('#copy_sao').click(function (e) {
          var text = "";
          $("table.main").find("td.sao").each(function(){
              text = text + $(this).text() + "\n";
          });
          console.log("copy text : " + text );
          copyToClipBoard(text);
        });
    $('#copy_dg').click(function (e) {
          var text = "";
          $("table.main").find("td.dg").each(function(){
              text = text + $(this).text() + "\n";
          });
          console.log("copy text : " + text );
          copyToClipBoard(text);
        });
</script>

</body>
</html>

