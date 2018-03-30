# Events Arranger and Tracker Bot

[![Build Status](https://img.shields.io/travis/dmromanov/telegram-eant-bot/master.svg?style=flat-square)](https://travis-ci.org/dmromanov/telegram-eant-bot)
[![GitHub license](https://img.shields.io/github/license/dmromanov/telegram-eant-bot.svg)](https://github.com/dmromanov/telegram-eant-bot/blob/master/LICENSE)

## Installation

Clone this repository and run 
```bash
composer install
```

## Configuration

Read and edit `config/app.php` and setup the `'Datasources'` and any other
configuration relevant for your application.

Environment variables:

* `TELEGRAM_APIKEY`
* `DATABASE_URL`

## CLI Commands

Register the bot in Telegram:

```bash
$ TELEGRAM_APIKEY=<api-key> ./bin/cake setup webhook_register <domain> <ssl-certificate> <max-requests>
```
```bash
$ TELEGRAM_APIKEY=<api-key> ./bin/cake setup webhook_unregister
```
```bash
$ TELEGRAM_APIKEY=<api-key> DATABASE_URL=<database-url> ./bin/cake multicast_message "<message>"
```

## Authors

Dmitrii Romanov — the idea author and the main developer dmitrii.romanov@firstlinesoftware.com 
Anton Sipin — QA anton.sipin@firstlinesoftware.com
Yury Chernyatin — Developer
