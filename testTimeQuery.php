<form method="POST">
	<input type="url" name="host" value="<?php echo !empty($_POST['host']) ? $_POST['host'] : '' ?>"
	       style="width: 1000px" placeholder="Host"/>
	<br/>
	<input type="text" name="request" value="<?php echo !empty($_POST['request']) ? $_POST['request'] : '' ?>"
	       style="width: 1000px" placeholder="Request"/>
	<br/>
	<input type="number" name="count" value="<?php echo !empty($_POST['count']) ? $_POST['count'] : 200 ?>"
	       style="width: 100px" placeholder="count"/>
	<br/>
	<button style="width: 1000px">TEST</button>
</form>


<?php
if (!empty($_POST['host']) && !empty($_POST['request'])) {

	set_time_limit(500);
	define('DEVELOPMENT_IP', '127.0.0.1');
	require_once './varDumperCasper.php';

	$time = array();
	$url  = preg_replace('%(?<=\w)\/+%s', '/', $_POST['host'] . $_POST['request']);

	echo "<a href='" . $url . "' target=_blank>" . $url . '</a><br/><br/>';

	for ($count = $_POST['count']; $count; $count--) {
		if ($count % 50 === 0) {
			sleep(0.5);
		}

		$handler = curl_init();
		curl_setopt($handler, CURLOPT_URL, $url);
		curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);

//		if (!ini_get('open_basedir')) {
//			curl_setopt($handler, CURLOPT_FOLLOWLOCATION, true);
//		}

		//curl_setopt($handler, CURLOPT_TIMEOUT, $options['timeout']);
		//curl_setopt($handler, CURLOPT_CONNECTTIMEOUT, $options['timeout']);
		//curl_setopt($handler, CURLOPT_PROXY, $proxy);

		curl_setopt($handler, CURLOPT_VERBOSE, true);

		curl_setopt($handler, CURLOPT_USERPWD, 'admin:skdf$#&&%tg');
		curl_setopt($handler, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

		//curl_setopt($handler, CURLOPT_POST, true);
		curl_setopt($handler, CURLOPT_HTTPGET, true);
		curl_setopt($handler, CURLOPT_HTTPHEADER, array('Content-Type: text/xml; charset=utf-8'));

		$httpResponse = curl_exec($handler);

		if ($httpResponse !== false && $httpResponse !== null) {
			$data = $httpResponse;
			$info = curl_getinfo($handler);
			$time[] = $info['total_time'];

			$headers   = array();
			$headers[] = 'HTTP/1.1 ' . $info['http_code'] . ' OK';
		}
		else {
			$headers = array();
			$data    = '';
		}

		if (empty($data) && count($headers) == 0) {
			throw new HttpException('HTTP request failed, ' . curl_error($handler));
		}

		curl_close($handler);
	}

	$res = array_sum($time) / count($time);

	echo'<pre> array_sum($time) / count($time) = ';print_r(round($res, 3) . ' sec');echo"</pre>";

	varDumperCasper::dump($time, '_time', true);
	varDumperCasper::dump(json_decode($httpResponse, true), 'httpResponse');
}
