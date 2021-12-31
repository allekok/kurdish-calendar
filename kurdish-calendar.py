'''
Copyright (C) 2020-2021 allekok.
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
'''

''' ~ '''
from math import floor

def div (x, y):
        return floor(x / y)

def mod (x, y):
        r = x % y
        return r + y if y * r < 0 else r

''' 
Translated from:
gnu-emacs/lisp/calendar/calendar.el:
You can download a copy of GNU Emacs source code from 'emacs.org'
Copyright (C) 1988-1995, 1997, 2000-2020 Free Software Foundation, Inc.
Author: Edward M. Reingold <reingold@cs.uiuc.edu>
'''
def calendarExtractMonth (date):
        return date[0]

def calendarExtractDay (date):
        return date[1]

def calendarExtractYear (date):
        return date[2]

def calendarUpdateYear (date, year):
        return [date[0], date[1], year]

def calendarLeapYearP (year):
        if year < 0: year = abs(year) - 1
        return (mod(year,4) == 0) and \
                ((not (mod(year,100) == 0)) or (mod(year,400) == 0))

def calendarDayNumber (date):
        month = calendarExtractMonth(date)
        day = calendarExtractDay(date)
        year = calendarExtractYear(date)
        dayOfYear = day + (31 * (month - 1))
        if month > 2:
                dayOfYear = dayOfYear - div((23 + (month * 4)), 10)
                if calendarLeapYearP(year):
                        dayOfYear += 1
        return dayOfYear

def calendarSum (index, condition, expression):
        sum = 0
        while condition(index):
                sum += expression(index)
                index += 1
        return sum

def calendarAbsoluteFromGregorian (date):
        year = calendarExtractYear(date)
        if year == 0:
                exit('There was no year zero')
        elif year > 0:
                offsetYears = year - 1
                return calendarDayNumber(date) + \
                        (365 * offsetYears) + \
                        div(offsetYears, 4) + \
                        (- div(offsetYears, 100)) + \
                        div(offsetYears, 400)
        offsetYears = abs(year + 1)
        return calendarDayNumber(date) - \
                (365 * offsetYears) - \
                div(offsetYears, 4) - \
                (- div(offsetYears, 100)) - \
                div(offsetYears, 400) - \
                calendarDayNumber([12, 31, -1])

def calendarGregorianFromAbsolute (date):
        d0 = date - 1
        n400 = div(d0, 146097)
        d1 = mod(d0, 146097)
        n100 = div(d1, 36524)
        d2 = mod(d1, 36524)
        n4 = div(d2, 1461)
        d3 = mod(d2, 1461)
        n1 = div(d3, 365)
        day = mod(d3, 365) + 1
        year = (400 * n400) + (100 * n100) + (n4 * 4) + n1
        month = 1
        if (n100 == 4) or (n1 == 4):
                return [12, 31, year]
        year += 1
        while calendarLastDayOfMonth(month, year) < day:
                day = day - calendarLastDayOfMonth(month, year)
                month += 1
        return [month, day, year]

def calendarLastDayOfMonth (month, year):
        if (month == 2) and calendarLeapYearP(year):
                return 29
        return [31, 28, 31, 30, 31, 30,
                31, 31, 30, 31, 30, 31][month - 1]

def calendarDayOfWeek (date, absFunc=calendarAbsoluteFromGregorian):
        '''
        With a slight modification to the original version:
        An additional argument for different calendars
        '''
        return mod(absFunc(date), 7)

'''
Translated from:
gnu-emacs/lisp/calendar/cal-julian.el:
You can download a copy of GNU Emacs source code from 'emacs.org'
Copyright (C) 1995, 1997, 2001-2020 Free Software Foundation, Inc.
Author: Edward M. Reingold <reingold@cs.uiuc.edu>
'''
def calendarJulianToAbsolute (date):
        month = calendarExtractMonth(date)
        year = calendarExtractYear(date)
        return calendarDayNumber(date) + \
                (1 if ((mod(year,100) == 0) and \
                       (not (mod(year,400) == 0)) and \
                       (month > 2)) else 0) + \
                       (365 * (year - 1)) + \
                       div((year - 1), 4) \
                       - 2

''' 
Translated from:
gnu-emacs/lisp/calendar/cal-persia.el:
You can download a copy of GNU Emacs source code from 'emacs.org'
Copyright (C) 1996-1997, 2001-2020 Free Software Foundation, Inc.
Author: Edward M. Reingold <reingold@cs.uiuc.edu>
'''
calendarPersianEpoch = calendarJulianToAbsolute([3, 19, 622])

def calendarPersianLeapYearP (year):
        return (mod((mod(mod(((year + 2346)
                              if (0 <= year)
                              else (year + 2347)), 2820)
                         , 768)
                     * 683)
                    , 2820)
                < 683)

def calendarPersianLastDayOfMonth (month, year):
        if month < 7: return 31
        elif (month < 12) or calendarPersianLeapYearP(year): return 30
        return 29

def calendarPersianToAbsolute (date):
        month = calendarExtractMonth(date)
        day = calendarExtractDay(date)
        year = calendarExtractYear(date)
        if year < 0:
                return calendarPersianToAbsolute(
                        [month, day, mod(year, 2820) + 1]) + (
                                1029983 * div(year, 2820))
        return (calendarPersianEpoch - 1) + \
                (365 * (year - 1)) + \
                (683 * div((year + 2345), 2820)) + \
                (186 * div(mod((year + 2345), 2820), 768)) + \
                div(683 * mod(mod((year + 2345), 2820), 768), 2820) + \
                -568 + \
                calendarSum(1, lambda idx: (idx < month), lambda idx:
                            calendarPersianLastDayOfMonth(idx,year)) + \
                            day

def calendarPersianYearFromAbsolute (date):
        d0 = date - calendarPersianToAbsolute([1, 1, -2345])
        n2820 = div(d0, 1029983)
        d1 = mod(d0, 1029983)
        n768 = div(d1, 280506)
        d2 = mod(d1, 280506)
        n1 = div((2820 * (d2 + 366)), 1029983)
        year = (2820 * n2820) + (768 * n768) + \
                ((n1 - 1) if (d1 == 1029617) else n1) + -2345
        return (year - 1) if (year < 1) else year

def calendarPersianFromAbsolute (date):
        year = calendarPersianYearFromAbsolute(date)
        month = 1 + calendarSum(1, lambda idx: (
                date > calendarPersianToAbsolute(
                        [idx,
                         calendarPersianLastDayOfMonth(idx, year),
                         year])), lambda _: 1)
        day = date - (calendarPersianToAbsolute([month, 1, year]) - 1)
        return [month, day, year]

def calendarPersianDayOfWeek (date):
        return calendarDayOfWeek(date, calendarPersianToAbsolute)

''' 
Translated from:
gnu-emacs/lisp/calendar/cal-islam.el:
You can download a copy of GNU Emacs source code from 'emacs.org'
Copyright (C) 1995, 1997, 2001-2020 Free Software Foundation, Inc.
Author: Edward M. Reingold <reingold@cs.uiuc.edu>
'''
calendarIslamicEpoch = calendarJulianToAbsolute([7, 16, 622])

def calendarIslamicLeapYearP (year):
        return mod(year, 30) in \
                [2, 5, 7, 10, 13, 16, 18, 21, 24, 26, 29]

def calendarIslamicLastDayOfMonth (month, year):
        if month in [1, 3, 5, 7, 9, 11]:
                return 30
        elif month in [2, 4, 6, 8, 10]:
                return 29
        return 30 if calendarIslamicLeapYearP(year) else 29

def calendarIslamicDayNumber (date):
        month = calendarExtractMonth(date)
        return (30 * div(month, 2)) + \
                (29 * div((month - 1), 2)) + \
                calendarExtractDay(date)

def calendarIslamicToAbsolute (date):
        month = calendarExtractMonth(date)
        day = calendarExtractDay(date)
        year = calendarExtractYear(date)
        y = mod(year, 30)
        if y < 3: leapYearsInCycle = 0
        elif y < 6: leapYearsInCycle = 1
        elif y < 8: leapYearsInCycle = 2
        elif y < 11: leapYearsInCycle = 3
        elif y < 14: leapYearsInCycle = 4
        elif y < 17: leapYearsInCycle = 5
        elif y < 19: leapYearsInCycle = 6
        elif y < 22: leapYearsInCycle = 7
        elif y < 25: leapYearsInCycle = 8
        elif y < 27: leapYearsInCycle = 9
        else: leapYearsInCycle = 10
        return calendarIslamicDayNumber(date) + \
                ((year - 1) * 354) + \
                (11 * div(year, 30)) + \
                leapYearsInCycle + \
                (calendarIslamicEpoch - 1)

def calendarIslamicFromAbsolute (date):
        if date < calendarIslamicEpoch:
                return [0,0,0]
        approx = div((date - calendarIslamicEpoch), 355)
        year = approx + calendarSum(approx, lambda idx: (
                date >= calendarIslamicToAbsolute([1,1,(idx + 1)])),
                                    lambda _: 1)
        month = 1 + calendarSum(1, lambda idx: (
                date > calendarIslamicToAbsolute(
                        [idx,
                         calendarIslamicLastDayOfMonth(idx, year),
                         year])),
                                lambda _: 1)
        day = date - ((calendarIslamicToAbsolute([month, 1, year])) - 1)
        return [month, day, year]

def calendarIslamicDayOfWeek (date):
	return calendarDayOfWeek(date, calendarIslamicToAbsolute)

''' Kurdish Calendar '''
calendarKurdishMonthNameArray = [
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
]

calendarKurdishDayNameArray = [
	"یەک‌شەممە",
	"دووشەممە",
	"سێ‌شەممە",
	"چوارشەممە",
	"پێنج‌شەممە",
	"هەینی",
	"شەممە",
]

def calendarKurdishFromAbsolutePersian (date):
        return calendarKurdishFromPersian(
                calendarPersianFromAbsolute(date))

def calendarKurdishToAbsolutePersian (date):
        return calendarPersianToAbsolute(
                calendarKurdishToPersian(date))

def calendarKurdishFromPersian (date):
        return calendarUpdateYear(
                date, calendarExtractYear(date) + 1321)

def calendarKurdishToPersian (date):
        return calendarUpdateYear(
                date, calendarExtractYear(date) - 1321)

def calendarKurdishFromGregorian (date):
        return calendarKurdishFromAbsolutePersian(
                calendarAbsoluteFromGregorian(date))

def calendarKurdishToGregorian (date):
        return calendarGregorianFromAbsolute(
                calendarKurdishToAbsolutePersian(date))

def calendarKurdishFromIslamic (date):
        return calendarKurdishFromAbsolutePersian(
                calendarIslamicToAbsolute(date))

def calendarKurdishToIslamic (date):
        return calendarIslamicFromAbsolute(
                calendarKurdishToAbsolutePersian(date))

def calendarKurdishMonth (month):
        return calendarKurdishMonthNameArray[month - 1]

def calendarKurdishDayOfWeek (date):
	return calendarDayOfWeek(date, calendarKurdishToAbsolutePersian)

def calendarKurdishDayOfWeekName (date):
	return calendarKurdishDayNameArray[calendarKurdishDayOfWeek(date)]
