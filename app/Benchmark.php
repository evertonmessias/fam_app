<?php

namespace App;

class Benchmark {
	private static $_memory = [];
	private static $_queries = [];
	private static $_times = [];
	private static $_last_time = null;
	private static $_last_memory = null;
	private static $_last_name = null;
	private static $columns = [40, 18, 15, 18, 10, 12];

	public static function format_bytes ($bytes = 0) {
		$t = 'B';
		$neg = ($bytes < 0);

		$bytes = abs($bytes);

		$next = function ($text) use (&$t, &$bytes) {
			if ($bytes >= 1024) {
				$bytes /= 1024;
				$t = $text;
			}
			return $t;
		};

		$next ('KB');
		$next ('MB');
		$next ('GB');
		$next ('TB');

		if ($neg)
			$bytes = -($bytes);

		return number_format($bytes, 3) . $t;
	}

	public static function hook_db () {
		static::$_queries = [];

		if (!empty(static::$_queries))
			return;

		\DB::listen(function ($sql) {
			static::$_queries[static::$_last_name][] = $sql;
		});
	}

	public static function run ($name = 'Unnamed Test') {
		if (!isset(static::$_times[$name]))
			static::$_times[$name] = [];

		if (!isset(static::$_memory[$name]))
			static::$_memory[$name] = [];

		if (!is_null(static::$_last_name)) {
			$now = microtime(true);
			$time = $now - static::$_last_time;

			$now = memory_get_usage();
			$mem = $now - static::$_last_memory;

			static::$_times[static::$_last_name][] = $time;
			static::$_memory[static::$_last_name][] = $mem;
		}

		if (is_null($name))
			return;

		static::$_last_name = $name;
		static::$_last_time = microtime(true);
		static::$_last_memory = memory_get_usage();
	}

	public static function finish () { static::run(null); }

	public static function results ($max_time = null) {
		if (is_null($max_time))
			$max_time = ini_get('max_execution_time');

		$column = function ($values, $sizes = null, $separator = '|', $spacer = ' ', $pad_type = STR_PAD_BOTH) {
			foreach ($values as $k => $value) {
				$size = 20;

				if(!is_null($sizes) && isset($sizes[$k]))
					$size = $sizes[$k];

				$values[$k] = str_pad (substr ($value, 0, $size), $size, $spacer, $pad_type);
			}
			return implode($separator, $values);
		};

		$columns = static::$columns;
		$new_line = "\r\n";

		echo $column (['', '| BENCHMARK |', '', '', '', ''], $columns, '+', '-') . $new_line;
		echo $column (['Test Name', 'Total Time (s)', 'Iterations', 'Avg. Time (s)', 'Queries', 'Memory'], $columns) . $new_line;

		$t_total = 0;
		$a_total = 0;
		$q_total = 0;
		$u_total = 0;

		foreach (static::$_times as $test => $marks) {
			$total = 0;
			$memory = 0;
			$usages = static::$_memory[$test];

			$queries = (isset(static::$_queries[$test]) ? count(static::$_queries[$test]) : 0);

			if (empty($marks))
				continue;

			foreach($marks as $i => $mark) {
				$total += $mark;

				if (isset($usages[$i]))
					$memory += $usages[$i];
			}
			$average = $total / count ($marks);

			$t_total += $total;
			$a_total += $average;
			$q_total += $queries;
			$u_total += $memory;

			echo $column ([$test, number_format($total, 5), count ($marks), number_format($average, 5), $queries, static::format_bytes($memory)], $columns) . $new_line;
		}

		$m_total = 100 * $t_total / $max_time;

		echo $column (['', '', '', '', '', ''], $columns, '+', '-') . $new_line;
		echo $column (['Total', number_format($t_total, 5), number_format($m_total, 3) . '%', number_format($a_total, 5), $q_total, static::format_bytes($u_total)], $columns) . $new_line;
		echo $column (['', '', '', '', '', ''], $columns, '+', '-') . $new_line;
	}

	public static function clean () {
		static::$_queries = [];
		static::$_times = [];
		static::$_last_time = null;
		static::$_last_name = null;
		static::memory_clear();
	}

	public static function memory_clear () {
		static::$_memory = [];
		static::$_last_memory = null;
	}
}