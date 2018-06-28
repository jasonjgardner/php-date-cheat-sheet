<?php
define('DEV', strtolower(getenv('SERVER_NAME')) === 'localhost');

error_reporting(DEV ? E_ALL : 0);
ini_set('session.use_cookies', '0');

define('VERSION', '1.0.0');
define('TRANSLATE', false !== stripos('en', (getenv('HTTP_ACCEPT_LANGUAGE') ?: 'en')));

/// Version ID constant not available in versions prior to 5.2.7
if (!defined('PHP_VERSION_ID')) {
	$version = explode('.', PHP_VERSION);

	define('PHP_VERSION_ID', $version[0] * 10000 + $version[1] * 100 + $version[2]);
}

/// Prevent Bing translator from storing third-party cookies
$headers = headers_list();

if (TRANSLATE && (bool) $headers && !headers_sent()) {
	foreach ($headers as $header) {
		if (false !== stripos($header, 'Set-Cookie')) {
			if (PHP_VERSION_ID >= 50300) {
				header_remove('Set-Cookie');
			}
			else {
				header('Set-Cookie:');
			}

			break;
		}
	}
}

/**
 * List of formatting placeholder characters
 */
$formats = [
	'day'            => [
		'd' => 'Day of the month: Two digits with leading zeros.',
		'D' => 'Three-letter textual representation of a day.',
		'j' => 'Day of the month, without leading zeroes.',
		'l' => 'A full textual representation of the day of the week.',
		'S' => 'English ordinal suffix for the day of the month: Two characters.',
		'w' => 'Numeric representation of the day of the week',
		'z' => 'The day of the year (starting from zero).'
	],
	'week'           => [
		'W' => 'ISO-8601 week number of year. (Weeks start on Monday.)'
	],
	'month'          => [
		'F' => 'A full textual representation of a month',
		'm' => 'Numeric representation of a month, with leading zeros.',
		'M' => 'A short textual representation of a month: Three letters',
		'n' => 'Numeric representation of a month, without leading zeros.',
		't' => 'The number of days in the given month.'
	],
	'year'           => [
		'L' => 'Returns `1` if the year is a leap year, otherwise `0`.',
		'Y' => 'A full numeric representation of a year: Four digits',
		'y' => 'A short numeric representation of a year: Two digits'
	],
	'time'           => [
		'a' => 'Lowercase Ante meridiem and Post meridiem',
		'A' => 'Uppercase Ante meridiem and Post meridiem',
		'B' => 'Swatch Internet time',
		'g' => 'Twelve-hour format of an hour, without leading zeros.',
		'G' => 'Twenty-four-hour format of an hour, without leading zeros.',
		'h' => 'Twelve-hour format of an hour, with leading zeros.',
		'H' => 'Twenty-four-hour format of an hour, with leading zeros.',
		'i' => 'Minutes, with leading zeros.',
		's' => 'Seconds, with leading zeros.'
	],
	'timezone'       => [
		'I' => 'Returns `1` if date is during Daylight Saving Time, otherwise `0`',
		'O' => 'Difference to Greenwich time (GMT), in hours.',
		'T' => 'Timezone abbreviation',
		'Z' => 'Timezone offset in seconds.'
	],
	'full date/time' => [
		'c'               => 'ISO 8601 date',
		'r'               => 'RFC 2822 formatted date',
		'U'               => 'Seconds since the Unix Epoch',
		DateTime::W3C     => 'W3C',
		DateTime::ATOM    => 'ATOM',
		DateTime::RSS     => 'RSS',
		DateTime::ISO8601 => 'ISO8601',
		DateTime::RFC822  => 'RFC822',
		DateTime::RFC850  => 'RFC850',
		DateTime::RFC1036 => 'RFC1036',
		DateTime::RFC1123 => 'RFC1123',
		DateTime::RFC2822 => 'RFC2822',
		DateTime::RFC3339 => 'RFC3339',
	]
];

/**
 * List of formatting placeholders which do or do not include leading zeros
 */
$leadingZeros = [
	'day'   => [
		'd' => true,
		'j' => false
	],
	'month' => [
		'm' => true,
		'n' => false
	],
	'time'  => [
		'h' => true,
		'g' => false,
		'H' => true,
		'G' => false,
		'i' => true,
		's' => true
	],
	'year'  => [
		'Y' => true,
		'y' => false
	]
];

/**
 * List of formatting placeholders which abbreviate date output
 */
$abbreviations = [
	'day'      => [
		'D' => true,
		'l' => false
	],
	'month'    => [
		'M' => true,
		'F' => false
	],
	'timezone' => [
		'T' => false
	]
];

/**
 * List of time formats
 */
$timeFormats = [
	'g:i a'   => '12-hour time',
	'G:i:s'   => '24-hour time (with seconds)',
	'H:i:s P' => '24-hour time with GMT offset'
];

/**
 * List of date formats
 */
$dateFormats = [
	'Y-m-d'       => 'Dash-separated year, month, day',
	'Y-m-d H:i:s' => 'Dash-separated year, month, day with hours, minutes, and seconds.',
	'n/j/y'       => 'Short, slash-separated month, day, year',
	'm/d/Y'       => 'Long, slash-separated month, day, year',
	'F jS, Y'     => 'Full date',
	'l, F j, Y'   => 'Full day and date'
];

/**
 * List of formatting characters not supported by all PHP versions
 */
$notSupported = array_fill_keys(
	[
		'N',
		'o',
		'e',
		'P',
		'u',
		'v',
		'DateTime::COOKIE',
		'DateTime::RFC3339_EXTENDED'
	],
	true
);

/**
 * Check PHP version to determine supported formatting characters.
 * (PHP version should be checked when open-source material is distributed.)
 */

/// PHP v5.1
/// Supports ISO-8601 day of week and timezone ID formats
if (PHP_VERSION_ID >= 50010) {
	$formats['day']['N'] = 'ISO-8601 numeric representation of the day of the week.';
	$formats['year']['o'] = 'ISO-8601 week-numbering year. This has the same value as <b>Y</b>, except that if the ISO week number <b>(W)</b> belongs to the previous or next year, that year is used instead.';
	$formats['timezone']['e'] = 'Timezone identifier';
	$abbreviations['timezone']['e'] = true;

	unset($notSupported['N'], $notSupported['o'], $notSupported['e']);
}

/// PHP v5.1.3
/// Supports GMT timezone difference
if (PHP_VERSION_ID >= 50103) {
	$formats['timezone']['P'] = 'Difference to Greenwich time (GMT), formatted with colon between hours and minutes.';

	unset($notSupported['P']);
}

/// PHP v5.2.2
/// Supports microseconds formats
if (PHP_VERSION_ID >= 50202) {
	$formats['time']['u'] = 'Microseconds';
	$timeFormats['G:i:s.u'] = '24-hour time with microseconds';

	unset($notSupported['u']);
}

/// PHP v5.4.24
/// Supports HTTP cookie date format
if (PHP_VERSION_ID >= 50424) {
	$formats['full date/time'][DateTime::COOKIE] = 'HTTP cookies';

	unset($notSupported['DateTime::COOKIE']);
}

/// PHP v7
/// Supports milliseconds and RFC 3339 EXTENDED formats
if (PHP_MAJOR_VERSION >= 7) {
	$formats['time']['v'] = 'Milliseconds';
	$formats['full date/time'][DateTime::RFC3339_EXTENDED] = 'RFC 3339 EXTENDED format';

	unset($notSupported['v'], $notSupported['DateTime::RFC3339_EXTENDED']);
}

/**
 * Demo date string value
 * Defaults to current time
 */
$when = 'now';

/// Change date value via request parameters
if (isset($_REQUEST['date']) || array_key_exists('date', $_REQUEST)) {
	$when = filter_var($_REQUEST['date'], FILTER_SANITIZE_STRING);
}

/**
 * Warning message
 */
$warning = null;

try {
	/**
	 * Demo \DateTime instance
	 */
	$date = new DateTime($when);
} catch (\Exception $e) {
	$date = new DateTime();
	$warning = $e->getMessage();
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="x-ua-compatible" content="ie=edge">

	<title>PHP DateTime Cheat Sheet</title>

	<meta name="description" content="PHP date format reference and examples.">
	<meta name="robots" content="index,follow">
	<meta name="theme-color" content="#8892bf">
	<link rel="license" href="http://creativecommons.org/publicdomain/zero/1.0/">

	<link href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.0/normalize.min.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Fira+Mono:400,700|Fira+Sans:400,500,600,700" rel="stylesheet">
	<link href="style.css" rel="stylesheet" media="screen">
	<link rel="icon" sizes="16x16" href="favicon.png">
</head>
<body>
<header>
	<div class="wrapper">
		<h1><span>PHP DateTime</span> Cheat Sheet</h1>
		<?php if (TRANSLATE): ?>
			<div id="MicrosoftTranslatorWidget" class="Light"></div>
		<?php endif; ?>
	</div>
</header>
<div class="wrapper">
	<form action="<?php echo getenv('PHP_SELF'); ?>">
		<label for="set-date">Select example date/time:</label>
		<input id="set-date" name="date" type="datetime-local" placeholder="Parsable datetime"
			   value="<?php echo $date->format('Y-m-d H:i:s'); /// Firefox fallback ?>" tabindex="1">
		<button type="submit">Set Date</button>
	</form>
</div>
<main class="wrapper">
	<?php if ($warning !== null): ?>
		<div class="warning" role="alert"><b>Woops:</b> <?php echo $warning; ?></div>
	<?php endif; ?>

	<h2>Quick Reference</h2>

	<table id="dates">
		<caption>Dates</caption>
		<thead>
		<tr>
			<td scope="col">Format</td>
			<td scope="col">Description</td>
			<td scope="col">Output</td>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($dateFormats as $format => $description) {
			echo "<tr><td>{$format}</td><td>{$description}</td><td>", $date->format($format), '</td></tr>';
		}
		?>
		</tbody>
	</table>

	<table id="times">
		<caption>Times</caption>
		<thead>
		<tr>
			<td scope="col">Format</td>
			<td scope="col">Description</td>
			<td scope="col">Output</td>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($timeFormats as $format => $description) {
			echo "<tr><td>{$format}</td><td>{$description}</td><td><time>", $date->format($format), '</time></td>';
		}
		?>
		</tbody>
	</table>

	<table id="digits">
		<caption>Leading Digits</caption>
		<thead>
		<tr>
			<td scope="col">Format</td>
			<td scope="col">Includes Digits</td>
			<td scope="col">Output</td>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($leadingZeros as $group => $frmt) {
			echo '<tr class="row--header"><td colspan="3">' . ucwords($group) . '</td></tr>';

			foreach ($frmt as $format => $leading) {
				$class = 'positive';
				$val = 'Yes';

				if (!$leading) {
					$class = 'negative'; /// TODO: Style positive/negative classes in CSS
					$val = 'No';
				}

				echo "<tr class=\"row--{$class}\"><td>{$format}</td><td>{$val}</td><td>", $date->format($format), '</td>';
			}
		}
		?>
		</tbody>
	</table>

	<table id="abbr">
		<caption>Abbreviations</caption>
		<thead>
		<tr>
			<td scope="col">Format</td>
			<td scope="col">Abbreviated</td>
			<td scope="col">Output</td>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($abbreviations as $group => $frmt) {
			echo '<tr class="row--header"><td colspan="3">' . ucwords($group) . '</td></tr>';

			foreach ($frmt as $format => $abbr) {
				$class = 'positive';
				$val = 'Yes';

				if (!$abbr) {
					$class = 'negative';
					$val = 'No';
				}

				echo "<tr class=\"row--{$class}\"><td>{$format}</td><td>{$val}</td><td>", $date->format($format), '</td>';
			}
		}
		?>
		</tbody>
	</table>

	<h2 id="documentation">Documentation</h2>
	<p>
		<a href="https://secure.php.net/manual/<?php echo (string) (getenv('HTTP_ACCEPT_LANGUAGE') ?: 'en'); ?>/function.date.php#refsect1-function.date-parameters"
		   target="_blank"
		   rel="noopener">View full documentation</a></p>

	<?php
	foreach ($formats as $title => $section) {
		$id = preg_replace('/[^a-z][^a-z0-9_\-]*/i', '-', strtolower($title));
		echo "<section id=\"documentation-{$id}\"><h1>", ucwords($title), '</h1><table><thead><tr>',
		'<td>Format</td><td>Description</td><td>Output</td></tr></thead><tbody>';

		foreach ($section as $format => $description) {
			echo "<tr><td>{$format}</td><td>{$description}</td><td>", $date->format($format), '</td></tr>';
		}

		echo '</tbody></table></section>';
	}

	if (DEV) {
		if ((bool) $notSupported) {
			printf(
				'<div class="warning"><p>The following formats are not supported by your PHP version (%s): %s</p></div>',
				PHP_VERSION,
				implode(', ', array_keys($notSupported))
			);
		}
		else {
			printf(
				'<div class="tip"><p>Your PHP version <span class="version">(%s)</span> supports all date/time formatting options.</p></div>',
				PHP_VERSION
			);
		}
	}
	?>
</main>
<footer class="wrapper">
	<p>
		Brought to you by <a href="https://jasongardner.co" target="_blank" rel="noopener">Jason Gardner</a>.
		View source on <a href="https://github.com/jasonjgardner/php-date-cheat-sheet/" target="_blank" rel="noopener">GitHub</a>.
		&nbsp;<span class="version"
			  title="DateTime Cheat Sheet version <?php echo VERSION; ?>">v<?php echo VERSION; ?></span>
	</p>

	<div class="license">
		<a rel="license"
		   href="http://creativecommons.org/publicdomain/zero/1.0/">
			<img src="https://licensebuttons.net/p/zero/1.0/88x31.png" style="border-style: none;" alt="CC0" />
		</a>
		<p>To the extent possible under law, <a href="http://jasongardner.co/php-date-cheat-sheet/" rel="dct:publisher">
				<span property="dct:title">Jason Gardner</span></a> has waived all copyright and related or neighboring
			rights to <span property="dct:title">PHP DateTime Cheat Sheet</span>. This work is published from:
			<span property="vcard:Country" datatype="dct:ISO3166" content="US" about="http://jasongardner.co/php-date-cheat-sheet/">
			United States</span>.
			<a href="https://github.com/jasonjgardner/php-date-cheat-sheet/README.md#License" target="_blank" rel="noopener">
				See license and attribution.
			</a>
		</p>
	</div>
	<!-- /.license -->
</footer>

<?php if (TRANSLATE): ?>
	<script>
		setTimeout(function () {
			var s     = document.createElement('script'),
				p     = document.getElementsByTagName('title')[0] || document.body.lastChild;
			s.type    = 'text/javascript';
			s.charset = 'UTF-8';
			s.src     = ((location && location.href && location.href.indexOf('https') === 0) ? 'https://ssl.microsofttranslator.com' : 'http://www.microsofttranslator.com') + '/ajax/v3/WidgetV3.ashx?siteData=ueOIGRSKkd965FeEGM5JtQ**&ctf=False&ui=true&settings=undefined&from=en';
			p.insertBefore(s, p.firstChild);
		}, 0);
	</script>
<?php endif; ?>
</body>
</html>
