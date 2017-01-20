<?php

/**
 * @author Rusavskiy Vitaliy <rusavskiy@gmail.com>
 * @date 14.10.14
 */
class varDumperCasper
{
	static public  $ipFilter  = ['77.91.171.194', '91.218.75.2', '127.0.0.1'];
	static public  $depth     = 10;
	static public  $highlight = true;
	static public  $button    = false;
	static private $isConsole = false;
	static private $backtrace = null;
	static private $dataStr;
	static private $id;

	static function dump($var, $name = '', $button = false)
	{
		if (
			is_array(self::$ipFilter)
			&& !empty($_SERVER['REMOTE_ADDR'])
			&& !in_array($_SERVER['REMOTE_ADDR'], self::$ipFilter)
		) {
			return false;
		}

		self::$button = $button;
		self::$isConsole = empty($_SERVER['SERVER_NAME']);
		self::$id = mt_rand();

		if (function_exists('debug_backtrace')) {
			self::$backtrace = debug_backtrace();
		} else {
			self::$backtrace[0]['file'] = 'UNKNOWN FILE';
			self::$backtrace[0]['line'] = 'UNKNOWN LINE';
		}
		self::$backtrace[0]['name'] = $name;

		self::createData($var);
		self::view();
	}

	static private function createData($var)
	{
		if (self::$isConsole) {
			self::$dataStr = $var;
		} else {
			ob_start();
			VarDumper::dump($var, self::$depth, self::$highlight);
			self::$dataStr = ob_get_clean();
		}
	}

	static private function view()
	{
		if (self::$isConsole) {
			self::viewConsole();
		} elseif (self::$button) {
			self::viewButton();
		} else {
			self::viewBlock();
		}
	}

	static private function viewButton()
	{
		self::createblockData();
		self::filterData();

		?>
		<style>
			.debug_buttons {
				color: #E00;
				background: #98F68B;
				margin: 3px;
				border: 1px solid black;
				border-radius: 10px;
				padding: 4px;
				cursor: pointer;
				white-space: nowrap;
				position: relative;
				z-index: 99999;
			}

			.debug_buttons span {
				background: #FFFF7B;
				border-radius: 10px;
				padding: 5px;
				font-weight: bold;
			}
		</style>
		<button class='debug_buttons' type="button" onclick='DEBUG<?php echo self::$id ?>();
			return false;'><?php
			echo (self::$backtrace[0]['name'])
				? '<span>' . self::$backtrace[0]['name'] . '</span> '
				: '';
			echo preg_replace('{^.+(?=\\\+)\\\}', '', self::$backtrace[0]['file']);
			?> >>> <span><?php echo self::$backtrace[0]['line']; ?></span>
		</button>
		<script>
			function DEBUG<?php echo  self::$id ?>() {
				var oWnd = window.open("", "DEBUG", "height=800px, width=800px, resizable=no, status=no");
				oWnd.document.write("<?php echo  self::$dataStr ?>");
				var s = document.createElement("SCRIPT");
				s.innerHTML = 'document.getElementById("close_<?php echo  self::$id ?>").onclick = function() {document.getElementById("<?php echo  self::$id ?>").parentNode.removeChild(document.getElementById("<?php echo  self::$id ?>")); if(document.getElementsByTagName("fieldset").length === 0)window.close();return false;}';
				oWnd.document.getElementById("<?php echo  self::$id ?>").appendChild(s);
				oWnd.focus();
			}
		</script>
		<br/>
		<?php
	}

	static private function viewBlock()
	{
		?>
		<style>
			.debuger_casper {
				background: aliceblue;
				margin: 3px;
				border: 1px solid black;
				border-radius: 10px;
				padding: 10px;
				position: relative;
				z-index: 99999;
				word-wrap: break-word;
				font-size: 11px;
			}

			.debuger_casper p {
				margin: 10px 0;
				color: #000AFF;
				font-size: 13px;
			}

			.debuger_casper p span {
				background: #FFFF7B;
				border-radius: 10px;
				padding: 5px;
				font-weight: bold;
			}
		</style>

		<div class="debuger_casper">
			<p>
				<?php
				echo (self::$backtrace[0]['name'])
					? '<span>' . self::$backtrace[0]['name'] . '</span> '
					: '';

				echo ' Dump - ' . self::$backtrace[0]['file']
					. ' : <span>' . self::$backtrace[0]['line'] . '</span> '
					. '<br>';
				?>
			</p>

			<?php echo self::$dataStr; ?>
		</div>
		<?php
	}

	static private function viewConsole()
	{
		echo (self::$backtrace[0]['name'])
			? self::$backtrace[0]['name']
			: '';

		echo ' Dump - ' . self::$backtrace[0]['file']
			. ':' . self::$backtrace[0]['line'];

		echo PHP_EOL;
		print_r(self::$dataStr);
		echo PHP_EOL;
	}

	static private function createblockData()
	{
		ob_start();
		?>
		<fieldset id="<?php echo self::$id ?>">
			<title>DEBUG INFO</title>
			<style>
				div.closed_element {
					cursor: pointer;
					text-align: left;
					background: #EBFF94;
				}

				div.closed_element:hover {
					background: #CFFF00;
				}

				div.closed_element a {
					margin-left: 20px;
					text-transform: uppercase;
					text-decoration: none;
					color: #000;
				}

				legend {
					color: #35bad5;
					font-size: 14px;
					font-family: Tahoma;
					font-weight: bold;
					font-size: 12px;
					margin: 10px;
				}

				pre.prt {
					color: rgb(230, 0, 150);
				}
			</style>
			<legend>
				Dump - <?php echo self::$backtrace[0]['file']; ?> : <?php echo self::$backtrace[0]['line']; ?>
				(<?php echo date('H:i:s'); ?>)
			</legend>

			<pre class='prt'><?php echo self::$dataStr; ?></pre>

			<div class='closed_element' id="close_<?php echo self::$id ?>"><a href='#'>Закрыть</a></div>
		</fieldset>
		<?php
		self::$dataStr = ob_get_clean();
	}

	static private function filterData()
	{

		self::$dataStr = str_replace(["\r\n", "\r", "\n"], '', self::$dataStr);
		self::$dataStr = preg_replace('%>\s*<%iu', '><', self::$dataStr);
		self::$dataStr = preg_replace('%\s\s+%iu', ' ', self::$dataStr);
		self::$dataStr = preg_replace('%\<br\s/\>%iu', '<br/>', self::$dataStr);
		self::$dataStr = preg_replace('%"%', "'", self::$dataStr);

//		self::$dataStr = preg_replace('%"%', '\"', self::$dataStr);
//		self::$dataStr = preg_replace("%\\\\%iu", '\\\\', self::$dataStr);
		#echo self::$dataStr;
//		echo htmlspecialchars(self::$dataStr);
//		die;
	}

}

/**
 * varDumperCasper is intended to replace the buggy PHP function var_dump and print_r.
 * It can correctly identify the recursively referenced objects in a complex
 * object structure. It also has a recursive depth control to avoid indefinite
 * recursive display of some peculiar variables.
 *
 * varDumperCasper can be used as follows,
 * <pre>
 * varDumperCasper::dump($var);
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.utils
 * @since 1.0
 */
class VarDumper
{
	private static $_objects;
	private static $_output;
	private static $_depth;


	/**
	 * Displays a variable.
	 * This method achieves the similar functionality as var_dump and print_r
	 * but is more robust when handling complex objects such as Yii controllers.
	 * @param mixed $var variable to be dumped
	 * @param integer $depth maximum depth that the dumper should go into the variable. Defaults to 10.
	 * @param boolean $highlight whether the result should be syntax-highlighted
	 */
	public static function dump($var, $depth = 10, $highlight = false)
	{
		echo static::dumpAsString($var, $depth, $highlight);
	}

	/**
	 * Dumps a variable in terms of a string.
	 * This method achieves the similar functionality as var_dump and print_r
	 * but is more robust when handling complex objects such as Yii controllers.
	 * @param mixed $var variable to be dumped
	 * @param integer $depth maximum depth that the dumper should go into the variable. Defaults to 10.
	 * @param boolean $highlight whether the result should be syntax-highlighted
	 * @return string the string representation of the variable
	 */
	public static function dumpAsString($var, $depth = 10, $highlight = false)
	{
		self::$_output = '';
		self::$_objects = [];
		self::$_depth = $depth;
		self::dumpInternal($var, 0);
		if ($highlight) {
			$result = highlight_string("<?php\n" . self::$_output, true);
			self::$_output = preg_replace('/&lt;\\?php<br \\/>/', '', $result, 1);
		}

		return self::$_output;
	}

	/**
	 * @param mixed $var variable to be dumped
	 * @param integer $level depth level
	 */
	private static function dumpInternal($var, $level)
	{
		switch (gettype($var)) {
			case 'boolean':
				self::$_output .= $var ? 'true' : 'false';
				break;
			case 'integer':
				self::$_output .= "$var";
				break;
			case 'double':
				self::$_output .= "$var";
				break;
			case 'string':
				self::$_output .= "'" . addslashes($var) . "'";
				break;
			case 'resource':
				self::$_output .= '{resource}';
				break;
			case 'NULL':
				self::$_output .= "null";
				break;
			case 'unknown type':
				self::$_output .= '{unknown}';
				break;
			case 'array':
				if (self::$_depth <= $level) {
					self::$_output .= '[...]';
				} elseif (empty($var)) {
					self::$_output .= '[]';
				} else {
					$keys = array_keys($var);
					$spaces = str_repeat(' ', $level * 4);
					self::$_output .= '[';
					foreach ($keys as $key) {
						self::$_output .= "\n" . $spaces . '    ';
						self::dumpInternal($key, 0);
						self::$_output .= ' => ';
						self::dumpInternal($var[$key], $level + 1);
						self::$_output .= ',';
					}
					self::$_output .= "\n" . $spaces . ']';
				}
				break;
			case 'object':
				if (($id = array_search($var, self::$_objects, true)) !== false) {
					self::$_output .= get_class($var) . '#' . ($id + 1) . '(...)';
				} elseif (self::$_depth <= $level) {
					self::$_output .= get_class($var) . '(...)';
				} else {
					$id = array_push(self::$_objects, $var);
					$className = get_class($var);
					$spaces = str_repeat(' ', $level * 4);
					self::$_output .= "$className#$id\n" . $spaces . '(';
					foreach ((array)$var as $key => $value) {
						$keyDisplay = strtr(trim($key), "\0", ':');
						self::$_output .= "\n" . $spaces . "    [$keyDisplay] => ";
						self::dumpInternal($value, $level + 1);
					}
					self::$_output .= "\n" . $spaces . ')';
				}
				break;
		}
	}

	/**
	 * Exports a variable as a string representation.
	 *
	 * The string is a valid PHP expression that can be evaluated by PHP parser
	 * and the evaluation result will give back the variable value.
	 *
	 * This method is similar to `var_export()`. The main difference is that
	 * it generates more compact string representation using short array syntax.
	 *
	 * It also handles objects by using the PHP functions serialize() and unserialize().
	 *
	 * PHP 5.4 or above is required to parse the exported value.
	 *
	 * @param mixed $var the variable to be exported.
	 * @return string a string representation of the variable
	 */
	public static function export($var)
	{
		self::$_output = '';
		self::exportInternal($var, 0);

		return self::$_output;
	}

	/**
	 * @param mixed $var variable to be exported
	 * @param integer $level depth level
	 */
	private static function exportInternal($var, $level)
	{
		switch (gettype($var)) {
			case 'NULL':
				self::$_output .= 'null';
				break;
			case 'array':
				if (empty($var)) {
					self::$_output .= '[]';
				} else {
					$keys = array_keys($var);
					$outputKeys = ($keys !== range(0, sizeof($var) - 1));
					$spaces = str_repeat(' ', $level * 4);
					self::$_output .= '[';
					foreach ($keys as $key) {
						self::$_output .= "\n" . $spaces . '    ';
						if ($outputKeys) {
							self::exportInternal($key, 0);
							self::$_output .= ' => ';
						}
						self::exportInternal($var[$key], $level + 1);
						self::$_output .= ',';
					}
					self::$_output .= "\n" . $spaces . ']';
				}
				break;
			case 'object':
				self::$_output .= 'unserialize(' . var_export(serialize($var), true) . ')';
				break;
			default:
				self::$_output .= var_export($var, true);
		}
	}
}
//varDumperCasper::dump($modelAdd, 'test', true);
//varDumperCasper::dump($modelAdd, 'test');