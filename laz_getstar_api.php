<?php
require_once('./src/helper.php');

//include_once "src/show_errors.php";


//<span class="score-average">3.7</span>
//<div class="count">3<!-- --> <!-- -->đánh giá</div>
function getLazStar($urls) {
	$output = array();
	foreach ($urls as $key => $url) {
		$output['url'][] = $url;
		if(is_url($url)) {
			$content = file_get_contents($url);
			preg_match("'<span class=\"score-average\">(.+?)</span>'si", $content, $matches);

			if(count($matches)) {
				$output['sao'][] = $matches[1];

				preg_match("'<div class=\"count\">(\d+).+?đánh giá</div>'si", $content, $matches);
				$output['dg'][] = $matches[1];
			} else {
				$output['sao'][] = '-';
				$output['dg'][] = '-';
			}
		} else {
			$output['sao'][] = "";
			$output['dg'][] = "";
		}
		if($key%10 == 0) {
			usleep(500000);
		}
	}
	return $output;
}

function printLazStar($data) {
	echo "<table border=1>";
    echo "<thead>";
    echo "<th>URL</th><th>SAO</th><th>DANH GIA</th>";
    echo "</thead>";
    echo "<tbody>";
    foreach ($data["url"] as $index => $url) {
        echo "<tr>";
        echo "<td>$url</td>";
        echo "<td>" . $data["sao"][$index] . "</td>";
        echo "<td>" . $data["dg"][$index] . "</td>";
        echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
}

function printLazStarJson($data) {
    echo json_encode($data);
}

// Pay no attention to this statement.
// It's only needed if timezone in php.ini is not set correctly.
date_default_timezone_set("UTC");
$laz_urls = $_POST['urls'];

if(count($laz_urls)) {
    $output = getLazStar($laz_urls);
    printLazStarJson($output);
}

?>
