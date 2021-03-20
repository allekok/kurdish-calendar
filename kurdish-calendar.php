<?php
/*
   Copyright (C) 2020 allekok.
   Author: Payam <payambapiri.97@gmail.com>

   This software is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   This software is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this software.  If not, see <https://www.gnu.org/licenses/>.
 */

/* ~ */
function div ($x, $y) {
	return floor($x / $y);
}

function mod ($x, $y) {
	$m = $x % $y;
	if($m < 0 xor $y < 0)
		return $y >= 0 ? $y + $m : $m + $y;
	return $m;
}

/* 
   Translated from:
   gnu-emacs/lisp/calendar/calendar.el:
   You can download a copy of GNU Emacs source code from 'emacs.org'
   Copyright (C) 1988-1995, 1997, 2000-2020 Free Software Foundation, Inc.
   Author: Edward M. Reingold <reingold@cs.uiuc.edu>
 */
function calendarExtractMonth ($date) {
	return $date[0];
}

function calendarExtractDay ($date) {
	return $date[1];
}

function calendarExtractYear ($date) {
	return $date[2];
}

function calendarUpdateYear ($date, $year) {
	$date[2] = $year;
	return $date;
}

function calendarLeapYearP ($year) {
	if($year < 0) $year = abs($year) - 1;
	return (mod($year,4) == 0) &&
	       ((!(mod($year,100) == 0)) || (mod($year,400) == 0));
}

function calendarDayNumber ($date) {
	$month = calendarExtractMonth($date);
	$day = calendarExtractDay($date);
	$year = calendarExtractYear($date);
	$dayOfYear = $day + (31 * ($month - 1));
	if($month > 2) {
		$dayOfYear = $dayOfYear - div((23 + ($month * 4)), 10);
		if(calendarLeapYearP($year))
			$dayOfYear += 1;
	}
	return $dayOfYear;
}

function calendarSum ($index, $condition, $expression, $month, $year, $date) {
	$sum = 0;
	while($condition($index, $month, $year, $date))
		$sum += $expression($index++, $month, $year);
	return $sum;
}

function calendarAbsoluteFromGregorian ($date) {
	$year = calendarExtractYear($date);
	if($year == 0)
		die("There was no year zero");
	else if($year > 0) {
		$offsetYears = $year - 1;
		return calendarDayNumber($date) +
		       (365 * $offsetYears) +
		       div($offsetYears, 4) +
		       (- div($offsetYears, 100)) +
		       div($offsetYears, 400);
	}
	$offsetYears = abs($year + 1);
	return calendarDayNumber($date) -
	       (365 * $offsetYears) -
	       div($offsetYears, 4) -
	       (- div($offsetYears, 100)) -
	       div($offsetYears, 400) -
	       calendarDayNumber([12, 31, -1]);
}

function calendarGregorianFromAbsolute ($date) {
	$d0 = $date - 1;
	$n400 = div($d0, 146097);
	$d1 = mod($d0, 146097);
	$n100 = div($d1, 36524);
	$d2 = mod($d1, 36524);
	$n4 = div($d2, 1461);
	$d3 = mod($d2, 1461);
	$n1 = div($d3, 365);
	$day = mod($d3, 365) + 1;
	$year = (400 * $n400) + (100 * $n100) + ($n4 * 4) + $n1;
	$month = 1;
	if($n100 == 4 || $n1 == 4)
		return [12, 31, $year];
	
	$year += 1;
	while(($mdays = calendarLastDayOfMonth($month, $year)) <
		$day) {
		$day = $day - $mdays;
		$month += 1;
	}
	return [$month, $day, $year];
}

function calendarLastDayOfMonth ($month, $year) {
	if($month == 2 && calendarLeapYearP($year))
		return 29;
	return [31, 28, 31, 30, 31, 30,
		31, 31, 30, 31, 30, 31][--$month];
}

/*
   Translated from:
   gnu-emacs/lisp/calendar/cal-julian.el:
   You can download a copy of GNU Emacs source code from 'emacs.org'
   Copyright (C) 1995, 1997, 2001-2020 Free Software Foundation, Inc.
   Author: Edward M. Reingold <reingold@cs.uiuc.edu>
 */
function calendarJulianToAbsolute ($date) {
	$month = calendarExtractMonth($date);
	$year = calendarExtractYear($date);
	return calendarDayNumber($date) +
	       ((mod($year,100) == 0 &&
		 !(mod($year,400) == 0) &&
		 $month > 2) ? 1 : 0) +
	       (365 * ($year - 1)) +
	       div(($year - 1), 4)
	     - 2;
}

/* 
   Translated from:
   gnu-emacs/lisp/calendar/cal-persia.el:
   You can download a copy of GNU Emacs source code from 'emacs.org'
   Copyright (C) 1996-1997, 2001-2020 Free Software Foundation, Inc.
   Author: Edward M. Reingold <reingold@cs.uiuc.edu>
 */
define('calendarPersianEpoch', calendarJulianToAbsolute([3, 19, 622]));

function calendarPersianLeapYearP ($year) {
	return (mod((mod(mod((0 <= $year) ?
			     ($year + 2346) :
			     ($year + 2347), 2820)
		       , 768)
		* 683)
		  , 2820)
		< 683);
}

function calendarPersianLastDayOfMonth ($month, $year) {
	if($month < 7) return 31;
	else if(($month < 12) || calendarPersianLeapYearP($year)) return 30;
	return 29;
}

function calendarPersianToAbsolute ($date) {
	$month = calendarExtractMonth($date);
	$day = calendarExtractDay($date);
	$year = calendarExtractYear($date);
	if ($year < 0)
		return calendarPersianToAbsolute(
			[$month, $day, mod($year, 2820) + 1]) + (
				1029983 * div($year, 2820));
	return (calendarPersianEpoch - 1) +
	       (365 * ($year - 1)) +
	       (683 * div(($year + 2345), 2820)) +
	       (186 * div(mod(($year + 2345), 2820), 768)) +
	       div(683 * mod(mod(($year + 2345), 2820), 768), 2820) +
	      -568 +
	       calendarSum(1, function ($idx,$month,$_,$__) {
		       return ($idx < $month);
	       }, function ($idx,$_,$year) {
		       return (calendarPersianLastDayOfMonth($idx,$year));
	       }, $month, $year, NULL) +
	       $day;
}

function calendarPersianYearFromAbsolute ($date) {
	$d0 = $date - calendarPersianToAbsolute([1, 1, -2345]);
	$n2820 = div($d0, 1029983);
	$d1 = mod($d0, 1029983);
	$n768 = div($d1, 280506);
	$d2 = mod($d1, 280506);
	$n1 = div((2820 * ($d2 + 366)), 1029983);
	$year = (2820 * $n2820) + (768 * $n768) +
		(($d1 == 1029617) ? --$n1 : $n1) + -2345;
	return $year < 1 ? --$year : $year;
}

function calendarPersianFromAbsolute ($date) {
	$year = calendarPersianYearFromAbsolute($date);
	$month = 1 + calendarSum(1, function ($idx,$_,$year,$date) {
		return ($date > calendarPersianToAbsolute(
			[$idx,
			 calendarPersianLastDayOfMonth($idx, $year),
			 $year]));
	}, function ($_,$__,$___) { return 1; }, NULL, $year, $date);
	$day = $date - (calendarPersianToAbsolute([$month, 1, $year]) - 1);
	return [$month, $day, $year];
}

/* 
   Translated from:
   gnu-emacs/lisp/calendar/cal-islam.el:
   You can download a copy of GNU Emacs source code from 'emacs.org'
   Copyright (C) 1995, 1997, 2001-2020 Free Software Foundation, Inc.
   Author: Edward M. Reingold <reingold@cs.uiuc.edu>
 */
define('calendarIslamicEpoch', calendarJulianToAbsolute([7,16,622]));

function calendarIslamicLeapYearP ($year) {
	return in_array(mod($year, 30),
			[2, 5, 7, 10, 13, 16, 18, 21, 24, 26, 29]);
}

function calendarIslamicLastDayOfMonth ($month, $year) {
	if(in_array($month, [1, 3, 5, 7, 9, 11]))
		return 30;
	else if(in_array($month, [2, 4, 6, 8, 10]))
		return 29;
	return calendarIslamicLeapYearP($year) ? 30 : 29;
}

function calendarIslamicDayNumber ($date) {
	$month = calendarExtractMonth($date);
	return (30 * div($month, 2)) +
	       (29 * div(($month - 1), 2)) +
	       calendarExtractDay($date);
}

function calendarIslamicToAbsolute ($date) {
	$month = calendarExtractMonth($date);
	$day = calendarExtractDay($date);
	$year = calendarExtractYear($date);
	$y = mod($year, 30);
	$leapYearsInCycle = ($y < 3) ? 0 :
			    (($y < 6) ? 1 :
			     (($y < 8) ? 2 :
			      (($y < 11) ? 3 :
			       (($y < 14) ? 4 :
				(($y < 17) ? 5 :
				 (($y < 19) ? 6 :
				  (($y < 22) ? 7 :
				   (($y < 25) ? 8 :
				    (($y < 27) ? 9 : 10)))))))));
	return calendarIslamicDayNumber($date) +
	       (($year - 1) * 354) +
	       (11 * div($year, 30)) +
	       $leapYearsInCycle +
	       (calendarIslamicEpoch - 1);
}

function calendarIslamicFromAbsolute ($date) {
	if ($date < calendarIslamicEpoch)
		return [0,0,0];
	$approx = div(($date - calendarIslamicEpoch), 355);
	$year = $approx + calendarSum($approx, function($idx,$_,$__,$date) {
		return $date >= calendarIslamicToAbsolute([1,1,($idx + 1)]);
	}, function ($_,$__,$___) { return 1; }, NULL, NULL, $date);
	$month = 1 + calendarSum(1, function ($idx,$_,$year,$date) {
		return ($date > calendarIslamicToAbsolute(
			[$idx,
			 calendarIslamicLastDayOfMonth($idx, $year),
			 $year]));
	}, function ($_,$__,$___) { return 1; }, NULL, $year, $date);
	$day = $date - ((calendarIslamicToAbsolute([$month, 1, $year])) - 1);
	return [$month, $day, $year];
}

/* Kurdish Calendar */
function calendarKurdishFromAbsolutePersian ($date) {
	return calendarKurdishFromPersian(
		calendarPersianFromAbsolute($date));
}

function calendarKurdishToAbsolutePersian ($date) {
	return calendarPersianToAbsolute(
		calendarKurdishToPersian($date));
}

function calendarKurdishFromPersian ($date) {
	return calendarUpdateYear(
		$date, calendarExtractYear($date) + 1321);
}

function calendarKurdishToPersian ($date) {
	return calendarUpdateYear(
		$date, calendarExtractYear($date) - 1321);
}

function calendarKurdishFromGregorian ($date) {
	return calendarKurdishFromAbsolutePersian(
		calendarAbsoluteFromGregorian($date));
}

function calendarKurdishToGregorian ($date) {
	return calendarGregorianFromAbsolute(
		calendarKurdishToAbsolutePersian($date));
}

function calendarKurdishFromIslamic ($date) {
	return calendarKurdishFromAbsolutePersian(
		calendarIslamicToAbsolute($date));
}

function calendarKurdishToIslamic ($date) {
	return calendarIslamicFromAbsolute(
		calendarKurdishToAbsolutePersian($date));
}

function calendarKurdishMonth ($month) {
	$calendarKurdishMonthNameArray = [
		"خاکەلێوە",
		"گوڵان",
		"جۆزەردان",
		"پووشپەڕ",
		"گەلاوێژ",
		"خەرمانان",
		"ڕەزبەر",
		"خەزەڵوەر",
		"سەرماوەز",
		"بەفرانبار",
		"ڕێبەندان",
		"ڕەشەمە"
	];
	return $calendarKurdishMonthNameArray[--$month];
}
?>
