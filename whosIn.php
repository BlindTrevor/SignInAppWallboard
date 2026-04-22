<?php
    // Set the API key, secret and site ID. Get this information from Sign In App.
    $baseUrl = 'https://backend.signinapp.com/client-api/v1';
    $key = 'XXXXXXXXXXXXXXXXXXXXXXXX';
    $secret = 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX';
    $siteId = 'XXXXX';
    $defaultPhotoUrl = 'assets/default-person.svg';
    // If you want to limit which IP Addresses can see this page, set $limitIpAddress
    // to true and add the allowed IPs into the $allowedIPs array
    $limitIpAddress = false;
    $allowedIPs = array("111.111.111.111", "222.222.222.222", "333.333.333.333", "444.444.444.444", "555.555.555.555");
?>
<?php
function fetchTodayData($baseUrl, $siteId, $key, $secret)
{
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
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['groups' => [], 'error' => 'Unable to contact Sign In App API: ' . $curlError];
    }

    if ($httpStatus >= 400) {
        return ['groups' => [], 'error' => 'Sign In App API request failed (HTTP ' . $httpStatus . ').'];
    }

    $array = json_decode($response, true);
    if (!is_array($array)) {
        return ['groups' => [], 'error' => 'Invalid response from Sign In App API.'];
    }

    return ['groups' => $array, 'error' => null];
}

function renderSignedInHtml($groups, $defaultPhotoUrl)
{
    ob_start();
    $renderedAny = false;
    foreach ($groups as $item) {
        if (!is_array($item)) {
            continue;
        }

        $groupName = (string)($item['name'] ?? '');
        $visitors = is_array($item['visitors'] ?? null) ? $item['visitors'] : [];
        $signedInVisitors = array_values(array_filter($visitors, fn($person) => is_array($person) && (($person['status'] ?? '') === 'signed_in')));
        if (count($signedInVisitors) === 0) {
            continue;
        }

        usort($signedInVisitors, fn($a, $b) => (($a['name'] ?? '') <=> ($b['name'] ?? '')));
        echo "<h2>" . htmlspecialchars($groupName, ENT_QUOTES, 'UTF-8') . "</h2>";
        foreach ($signedInVisitors as $person) {
            $photoUrl = trim($person['photo_url'] ?? '');
            if ($photoUrl === '') {
                $photoUrl = $defaultPhotoUrl;
            }
            echo '<div class="tile">';
            echo '<img src="' . htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars((string)($person['name'] ?? ''), ENT_QUOTES, 'UTF-8') . '">';
            echo '<h3>' . htmlspecialchars((string)($person['name'] ?? ''), ENT_QUOTES, 'UTF-8') . '</h3>';
            echo '</div>';
        }
        echo "<br>";
        $renderedAny = true;
    }

    if (!$renderedAny) {
        echo '<p class="empty-state">No one is currently signed in.</p>';
    }

    return ob_get_clean();
}

    if (($limitIpAddress == true) && (!in_array ($_SERVER['REMOTE_ADDR'], $allowedIPs))) {
    echo "<b>Error: </b>" . $_SERVER['REMOTE_ADDR'] . " is not an allowed IP Address.";
    exit();
}

$apiResult = fetchTodayData($baseUrl, $siteId, $key, $secret);
if ((isset($_GET['ajax'])) && ($_GET['ajax'] === '1')) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'html' => renderSignedInHtml($apiResult['groups'], $defaultPhotoUrl),
        'error' => $apiResult['error'],
        'fetchedAt' => gmdate('c')
    ]);
    exit();
}
?>
<!DOCTYPE html>
<html translate="no">
	<head>
		<meta name="google" content="notranslate" />
		<meta name="robots" content="notranslate">
		<title>Staff Members In The Building</title>
		<style>
			body {
				text-align: center;
				font-family: Open Sans;
				color: #1d1c31;
				background-color: #1d1c31;
				padding-bottom: 48px;
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
			.refresh-meta {
				position: fixed;
				left: 12px;
				bottom: 12px;
				display: flex;
				align-items: center;
				gap: 8px;
				font-size: 12px;
				color: rgba(255, 255, 255, 0.7);
				z-index: 10;
			}
			.refresh-controls button {
				background-color: transparent;
				color: rgba(255, 255, 255, 0.72);
				border: 1px solid rgba(255, 255, 255, 0.35);
				padding: 3px 8px;
				border-radius: 4px;
				cursor: pointer;
				font-size: 12px;
			}
			.refresh-controls button:disabled {
				opacity: 0.6;
				cursor: default;
			}
			.refresh-status {
				font-size: 12px;
			}
			.empty-state {
				color: #ffffff;
			}
		</style>
	</head>
	<body>
		<h1>Who's In</h1>
		<div class="refresh-meta">
			<div class="refresh-status" id="refreshStatus">
				<?php
                    if ($apiResult['error']) {
                        echo htmlspecialchars($apiResult['error'], ENT_QUOTES, 'UTF-8');
                    } else {
                        echo 'Last updated: just now';
                    }
                ?>
			</div>
			<div class="refresh-controls">
				<button type="button" id="refreshButton">Refresh now</button>
			</div>
		</div>
		<div id="peopleContainer"><?php echo renderSignedInHtml($apiResult['groups'], $defaultPhotoUrl); ?></div>
		<script>
			const refreshButton = document.getElementById('refreshButton');
			const refreshStatus = document.getElementById('refreshStatus');
			const peopleContainer = document.getElementById('peopleContainer');
			const refreshRequestUrl = new URL(window.location.href);
			refreshRequestUrl.searchParams.set('ajax', '1');
			const refreshUrl = refreshRequestUrl.toString();
			const refreshIntervalMs = 60000;
			const refreshTimeoutMs = 10000;
			let refreshTimer = null;
			let isRefreshing = false;

			async function refreshData() {
				if (isRefreshing) {
					return;
				}
				isRefreshing = true;
				refreshButton.disabled = true;
				const controller = new AbortController();
				const timeout = setTimeout(() => controller.abort(), refreshTimeoutMs);
				try {
					const response = await fetch(refreshUrl, {
						headers: { 'Accept': 'application/json' },
						cache: 'no-store',
						signal: controller.signal
					});
					if (!response.ok) {
						throw new Error('Refresh failed (HTTP ' + response.status + ').');
					}
					const payload = await response.json();
					if (typeof payload.html === 'string') {
						peopleContainer.innerHTML = payload.html;
					}
					if (payload.error) {
						refreshStatus.textContent = payload.error;
					} else {
						if (payload.fetchedAt) {
							const updateTime = new Date(payload.fetchedAt);
							refreshStatus.textContent = 'Last updated: ' + updateTime.toLocaleTimeString();
						} else {
							refreshStatus.textContent = 'Last updated: just now';
						}
					}
				} catch (error) {
					refreshStatus.textContent = error.name === 'AbortError'
						? 'Refresh timed out.'
						: error.message;
				} finally {
					clearTimeout(timeout);
					refreshButton.disabled = false;
					isRefreshing = false;
				}
			}

			refreshButton.addEventListener('click', refreshData);

			function startRefreshTimer() {
				if (!refreshTimer) {
					refreshTimer = setInterval(refreshData, refreshIntervalMs);
				}
			}

			function stopRefreshTimer() {
				if (refreshTimer) {
					clearInterval(refreshTimer);
					refreshTimer = null;
				}
			}

			document.addEventListener('visibilitychange', () => {
				if (document.hidden) {
					stopRefreshTimer();
					return;
				}
				refreshData();
				startRefreshTimer();
			});

			startRefreshTimer();
		</script>
	</body>
</html>
