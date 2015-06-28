# Remember The Milk - Statistic

## What is it?

This is a console application written in PHP to analyze your RTM inbox.

## Installation & Requirements

You should have PHP 5.4+ on your machine.

Download composer.phar:

```
curl -sS https://getcomposer.org/installer | php
```

Install dependencies:

```
php composer.phar install
```

Get you API Key & Secret on a page https://www.rememberthemilk.com/services/api/keys.rtm.

Get you API Token (somehow... see TBD).

Update config:

```
cp app/config/example-default.yaml app/config/default.yaml
nano app/config/default.yaml
```

## Usage

```
php cli.php 2015-01-05 2015-03-30 "1 week"
````

Which means: "grab statistic from 1/5/2015 to 3/20/2015 with interval equals of 1 week".

Command will create a CSV file with content like this:

```
start: 2015-01-05
end: 2015-06-29
interval: 1 week

2015-01-05	11	4	3	35
2015-01-12	3	3	3	56
2015-01-19	30	35	4	78
2015-01-26	21	23	5	107
2015-02-02	22	23	6	142
2015-02-09	9	5	7	186
2015-02-16	5	9	11	243
2015-02-23	8	10	11	320
2015-03-02	16	14	11	397
2015-03-09	11	15	11	474
2015-03-16	11	5	11	551
```

Where
* first column is a first date of period
* second column is a amount of *completed* tasks in a period
* third column is a amount of *added* tasks in a period
* forth column is a amount of *total* tasks in a list in a period
* last column is a sum of *life-days* of all uncompleted task in a list in a period
(the number of days since the creation of each task)

## TBD

* Unit tests
* Command for getting API Token