<?php
    // Set the API key, secret and site ID. Get this information from Sign In App.
    $baseUrl = 'https://backend.signinapp.com/client-api/v1';
    $key = 'XXXXXXXXXXXXXXXXXXXXXXXX';
    $secret = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
    $siteId = 'XXXXX';
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Staff Members In The Building</title>
		<style>
			body {
				text-align: center;
				font-family: Open Sans;
				color: #1d1c31;
				background-color: #1d1c31;
			}
			h1 {
				color: #b2176f;
				font-weight: bold;
				font-size: 50px;
			}
			h2 {
				color: #0b86c8;
			}
			.tile {
				display: inline-block;
				width: 200px;
				height: 200px;
				margin: 10px;
				padding: 10px;
				border: 1px solid #ccc;
				text-align: center;
				border-radius: 10px;
				overflow: hidden;
				background-color: #ffffff;
			}
			.tile img {
				width: 125px;
				height: 125px;
				border-radius: 50%;
			}
		</style>
	</head>
	<body>
		<h1>Who's In</h1>
		<?php
            $url = $baseUrl . "/sites/" . $siteId . "/today";
			$headers = [
				'Authorization: Basic ' . base64_encode($key . ':' . $secret),
				'Content-type: application/json',
				'Accept: application/json'
			];
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = curl_exec($ch);
			$array = json_decode( $response, true );
			curl_close($ch);
			foreach ($array as $item) {
				$groupName = $item['name'];
				$visitors = $item['visitors'];
				$visitorCount = array_count_values(array_column($visitors, 'status'))["signed_in"];
				if($visitorCount > 0){
					echo "<h2>".$groupName."</h2>";
					foreach ($visitors as $person) {
						if($person['status'] == "signed_in") {
						echo '<div class="tile">';
						echo '<img src="' . $person['photo_url'] . '" alt="' . $person['name'] . '">';
						echo '<h3>' . $person['name'] . '</h3>';
						echo '</div>';
						}
					}
				}
				echo "<br>";
			}
		?>
	</body>
</html>
